<?php

class comments_ctl extends pagecore{
    
    function _init(){
        $this->mdl_comment = & loader::model('comment');
        $this->mdl_photo =& loader::model('photo');
        $this->mdl_album =& loader::model('album');
    }
    function post(){
        if(!$this->setting->get_conf('system.enable_comment')){
            form_ajax_failed('text',lang('album_comment_closed'));
        }
        
        $comment['email'] = safe_convert($this->getPost('email'));
        $comment['author'] = safe_convert($this->getPost('author'));
        $comment['content'] = safe_convert($this->getPost('content'));
        $comment['ref_id'] = intval($this->getPost('ref_id'));
        $comment['type'] = intval($this->getPost('type'));
        
        $this->plugin->trigger('before_post_comment');
        
        if($this->setting->get_conf('system.enable_comment_captcha') && !$this->user->loggedin()){
            $captcha =& loader::lib('captcha');
            if(!$captcha->check($this->getPost('captcha')))
                form_ajax_failed('text',lang('invalid_captcha_code'));
        }

        if($comment['email'] && !check_email($comment['email'])){
            form_ajax_failed('text',lang('error_email'));
        }
        if(!$comment['author']){
            form_ajax_failed('text',lang('error_comment_author'));
        }
        if(!$comment['content']){
            form_ajax_failed('text',lang('empty_content'));
        }
        if(!$comment['ref_id'] || !$comment['type']){
            form_ajax_failed('text',lang('miss_argument'));
        }
        $comment['post_time'] = time();
        $comment['author_ip'] = get_real_ip();
        if($this->setting->get_conf('system.comment_audit') == 1 && !$this->user->loggedin()){
            $comment['status'] = 0;
        }else{
            $comment['status'] = 1;
        }

        if($comment_id = $this->mdl_comment->save($comment)){
            $this->plugin->trigger('post_comment',$comment_id);
            form_ajax_success('box',lang('post_comment_success'),null,10.5);
        }else{
            form_ajax_failed('text',lang('post_comment_failed'));
        }
    }
    
    function reply(){
        $id = intval($this->getGet('id'));
        $comment_info = $this->mdl_comment->get_info($id);
        $comment_info['author'] = safe_invert($comment_info['author']);
        $comment_info['pid'] = $comment_info['pid']?  $comment_info['pid']:$comment_info['id'];
        $this->output->set('info',$comment_info);
        $this->output->set('enable_comment_captcha',$this->setting->get_conf('system.enable_comment_captcha'));
        $this->render();
    }
    
    function save_reply(){
        if(!$this->setting->get_conf('system.enable_comment')){
            form_ajax_failed('text',lang('album_comment_closed'));
        }
        
        $comment['email'] = safe_convert($this->getPost('email'));
        $comment['author'] = safe_convert($this->getPost('author'));
        $comment['content'] = safe_convert($this->getPost('content'));
        $comment['ref_id'] = intval($this->getPost('ref_id'));
        $comment['type'] = intval($this->getPost('type'));
        $comment['reply_author'] = safe_convert($this->getPost('reply_author'));
        $comment['pid'] = intval($this->getPost('pid'));
        
        $this->plugin->trigger('before_post_comment');

        if($this->setting->get_conf('system.enable_comment_captcha') && !$this->user->loggedin()){
            $captcha =& loader::lib('captcha');
            if(!$captcha->check($this->getPost('captcha')))
                form_ajax_failed('text',lang('invalid_captcha_code'));
        }

        if($comment['email'] && !check_email($comment['email'])){
            form_ajax_failed('text',lang('error_email'));
        }
        if(!$comment['author']){
            form_ajax_failed('text',lang('error_comment_author'));
        }
        if(!$comment['content']){
            form_ajax_failed('text',lang('empty_content'));
        }
        if(!$comment['ref_id'] || !$comment['type'] || !$comment['pid'] || !$comment['reply_author']){
            form_ajax_failed('text',lang('miss_argument'));
        }
        $comment['post_time'] = time();
        $comment['author_ip'] = get_real_ip();
        if($this->setting->get_conf('system.comment_audit') == 1 && !$this->user->loggedin()){
            $comment['status'] = 0;
        }else{
            $comment['status'] = 1;
        }
        
        if($reply_id = $this->mdl_comment->save($comment)){
            $comment['id'] = $reply_id;
            $this->output->set('info',$comment);
            $this->plugin->trigger('reply_comment',$reply_id);
            form_ajax_success('text',loader::view('comments/view',false));
        }else{
            form_ajax_failed('text',lang('reply_failed'));
        }
    }
    
    function confirm_delete(){
        need_login('ajax_page');

        $id = intval($this->getGet('id'));
        $this->output->set('id',$id);
        
        $this->render();
    }
    
    function delete(){
        need_login('ajax_page');

        $id = intval($this->getGet('id'));
        if($this->mdl_comment->delete($id)){                        
            $this->plugin->trigger('deleted_comment',$id);
            ajax_box(lang('delete_comment_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('delete_comment_failed'));
        }
    }
    
    function more(){
        $ref_id = intval($this->getGet('ref_id'));
        $type = intval($this->getGet('type'));
        $page = intval($this->getGet('page',1));
        $comments = $this->mdl_comment->get_all($page,array('status'=>1,'pid'=>0,'ref_id'=>$ref_id,'type'=>$type));
        if($comments['ls']){
            foreach($comments['ls'] as $k=>$v){
                $sub_comments = $this->mdl_comment->get_sub($v['id']);
                if($sub_comments){
                    foreach($sub_comments as $kk=>$vv){
                        $sub_comments[$kk]['content'] = $this->plugin->filter('comment_content',$vv['content'],$vv['id']);
                    }
                }
                $comments['ls'][$k]['content'] = $this->plugin->filter('comment_content',$v['content'],$v['id']);
                $comments['ls'][$k]['sub_comments'] = $sub_comments;
            }
        }
        $this->output->set('comments_list',$comments['ls']);
        $this->output->set('comments_total_page',$comments['total']);
        $this->output->set('comments_current_page',$comments['current']);
        $this->output->set('ref_id',$ref_id);
        $this->output->set('comments_type',$type);
        
        $this->render();
    }
    
    function block(){
        need_login('ajax_page');

        $id = intval($this->getGet('id'));
        if($this->mdl_comment->block($id)){
            ajax_box(lang('block_comment_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('block_comment_failed'));
        }
    }
    
    function approve(){
        need_login('ajax_page');

        $id = intval($this->getGet('id'));
        if($this->mdl_comment->approve($id)){
            ajax_box(lang('approve_comment_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('approve_comment_failed'));
        }
    }
    
    function manage(){
        need_login('page');
        //系统设置菜单
        $setting_menu = $this->plugin->filter('setting_menu','');
        $this->output->set('setting_menu',$setting_menu);

        $page = intval($this->getGet('page',1));
        $status = $this->getGet('status','all');
        
        $status_nums = $this->mdl_comment->count_group_status();
        $pageurl = site_link('comments','manage',array('page'=>'[#page#]','status'=>$status));
        $this->mdl_comment->set_pageset(20);
        $data = $this->mdl_comment->get_all($page,array('status'=>$status));
        if($data['ls']){
            foreach($data['ls'] as $k => $v){
                if($v['type'] == 1){
                    $data['ls'][$k]['subject'] = $this->mdl_album->get_info($v['ref_id']);
                }else{
                    $data['ls'][$k]['subject'] = $this->mdl_photo->get_info($v['ref_id']);
                }
            }
        }
        $page_obj =& loader::lib('page');
        $this->output->set('pagestr',$page_obj->fetch($data['total'],$data['current'],$pageurl));
        $this->output->set('comments',$data['ls']);
        $this->output->set('status',$status);
        $this->output->set('status_nums',$status_nums);

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('comments_manage'));
        
        $this->page_crumb($crumb_nav);
        
        $page_title = lang('comments_manage').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        $this->page_init($page_title,$page_keywords,$page_description);
        
        $this->render();
    }
    
    function confirm_delete_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_delete'));
        }
        $this->render();
    }
    
    function delete_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_delete'));
        }else{
            if($this->mdl_comment->delete_batch(array_keys($ids))){
                $this->plugin->trigger('deleted_many_comments',array_keys($ids));
                
                ajax_box(lang('batch_delete_comments_success'),null,1,$_SERVER['HTTP_REFERER']);
            }else{
                ajax_box(lang('batch_delete_comments_failed'));
            }
        }
    }
    
    function confirm_block_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_block'));
        }
        $this->render();
    }
    
    function block_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_block'));
        }else{
            if($this->mdl_comment->block_batch(array_keys($ids))){
                ajax_box(lang('batch_block_comments_success'),null,1,$_SERVER['HTTP_REFERER']);
            }else{
                ajax_box(lang('batch_block_comments_failed'));
            }
        }
    }
    
    function confirm_approve_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_approve'));
        }
        $this->render();
    }
    
    function approve_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_comments_want_to_approve'));
        }else{
            if($this->mdl_comment->approve_batch(array_keys($ids))){
                ajax_box(lang('batch_approve_comments_success'),null,1,$_SERVER['HTTP_REFERER']);
            }else{
                ajax_box(lang('batch_approve_comments_failed'));
            }
        }
    }
}