<?php

class albums_ctl extends pagecore{
    
    function _init(){
        $this->mdl_album = & loader::model('album');
        $this->mdl_cate = & loader::model('category');
    }
    
    function index(){
        $page = intval($this->getGet('page','1'));
        //search
        $search['name'] = safe_convert($this->getRequest('sname'));
        $search['tag'] = safe_convert($this->getRequest('tag'));
        $search['cate_id'] = $this->getGet('cate');//这里不能加上intval

        $par['page'] = '[#page#]';
        if($search['name']){
            $par['sname'] = $search['name'];
        }
        if($search['tag']){
            $par['tag'] = $search['tag'];
        }
        if($search['cate_id']!=''){
            $par['cate'] = $search['cate_id'];
        }
        $pageurl = site_link('albums','index',$par);
        
        //如果是搜索表单提交，则进行页面跳转
        if($this->isPost()){
            $redirect_url = site_link('albums','index',array('page'=>$page)+$par);
            redirect($redirect_url);
        }

        if($search['name'] || $search['tag']){
            $this->output->set('is_search',true);
        }else{
            $this->output->set('is_search',false);
        }
        
        //get page setting
        list($pageset,$page_setting_str) = get_page_setting('album');
        //get sort setting
        $sort_setting = array(lang('create_time') => 'ct',lang('upload_time') => 'ut',lang('photo_nums') => 'p');
        list($sort,$sort_list) =  get_sort_list($sort_setting,'album','ct_desc');
        
        $this->mdl_album->set_pageset($pageset);
        $albums = $this->mdl_album->get_all($page,$search,$sort);
        
        $g_enable_comment = $this->setting->get_conf('system.enable_comment');

        if(is_array($albums['ls'])){
            $mdl_photo = &loader::model('photo');

            foreach($albums['ls'] as $k=>$v){
                $albums['ls'][$k]['album_control_icons'] = $this->plugin->filter(
                                                        'album_control_icons','',$v['id']);
                if($v['cover_id']){
                    $cover_info = $mdl_photo->get_info($v['cover_id'],'thumb');
                    if($cover_info)
                        $albums['ls'][$k]['cover_path'] = $cover_info['thumb'];
                    else
                        $albums['ls'][$k]['cover_id'] = 0;
                }
                //是否允许评论
                if($g_enable_comment && $v['enable_comment']==1){
                    $albums['ls'][$k]['enable_comment'] = 1;
                }else{
                    $albums['ls'][$k]['enable_comment'] = 0;
                }
            }
        }
        
        $this->output->set('album_col_menu',$this->plugin->filter('album_col_menu',$page_setting_str.$sort_list));
        $this->output->set('album_multi_opt',$this->plugin->filter('album_multi_opt',''));
        $this->output->set('albums',$albums['ls']);
        $page_obj =& loader::lib('page');
        $this->output->set('pagestr',$page_obj->fetch($albums['total'],$albums['current'],$pageurl));
        $this->output->set('total_num',$albums['count']);
        $this->output->set('search',arr_stripslashes($search));
        $this->output->set('show_uptime',($sort=='ut_desc'||$sort=='ut_asc')?true:false);
        $this->output->set('album_sidebar',$this->plugin->filter('album_sidebar',''));

        $title = '';
        //面包屑
        $crumb_nav = array( 
                    array('name'=>lang('album_list'),'link'=>site_link('albums'))
                    );
        if($search['cate_id'] != ''){
            $cate_nav = $this->mdl_cate->cate_path_link($search['cate_id']);
            if($cate_nav){
                $crumb_nav = $cate_nav;
                foreach($cate_nav as $b){
                    $title = $b['name'].' &lt; '.$title;
                }
            }
        }
        if($search['name']){
            $crumb_nav[] = array('name'=>lang('search_s',$search['name']));
        }elseif($search['tag']){
            $crumb_nav[] = array('name'=>lang('search_tag',$search['tag']));
        }
        
        $this->page_crumb($crumb_nav);

        //显示分类
        $categorylist = $this->mdl_cate->get_categorys_width_cache();
        $this->output->set('categorylist',$categorylist);

        //page head
        $page_title = $title.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function ajaxlist(){
        $albums = $this->mdl_album->get_kv();
        if($albums){
            $ret = array('ret'=>true,'list'=>$albums);
        }else{
            $ret = array('ret'=>false,'msg'=>lang('no_records'));
        }
        $json =& loader::lib('json');
        echo $json->encode($ret);
    }
    
    function create(){
        need_login('ajax_page');
        $cate_id = intval($this->getGet('cate'));
        $this->output->set('cate_id',$cate_id);

        $this->output->set('system_enable_comment',$this->setting->get_conf('system.enable_comment'));
        $cate_mdl =& loader::model('category');
        $cate_list = $cate_mdl->get_categorys_width_cache();
        $this->output->set('cate_list',$cate_list);
        $this->output->set('fromurl',base64_encode(site_link('albums','create')));
        $this->render();
    }
    
    function save(){
        need_login('ajax');
        
        $album['name'] = safe_convert($this->getPost('album_name'));
        $album['desc'] = safe_convert($this->getPost('desc'));
        $album['priv_type'] = $this->getPost('priv_type','0');
        $album['tags'] = safe_convert($this->getPost('album_tags'));
        $album['priv_pass'] = $this->getPost('priv_pass');
        $album['priv_question'] = safe_convert($this->getPost('priv_question'));
        $album['priv_answer'] = safe_convert($this->getPost('priv_answer'));
        $album['cate_id'] = intval($this->getPost('cate_id'));
        $album['create_time'] = $album['up_time'] = time();
        $album['enable_comment'] = intval($this->getPost('enable_comment'));

        if($album['name'] == ''){
            form_ajax_failed('text',lang('album_name_empty'));
        }
        if($album['priv_type'] == '1'){
            if($album['priv_pass']==''){
                form_ajax_failed('text',lang('album_password_empty'));
            }
        }
        if($album['priv_type'] == '2'){
            if($album['priv_question'] == ''){
                form_ajax_failed('text',lang('album_question_empty'));
            }
            if($album['priv_answer'] == ''){
                form_ajax_failed('text',lang('album_answer_empty'));
            }
        }
        
        if($album_id = $this->mdl_album->save($album)){
            $tag_mdl =& loader::model('tag');
            $tag_mdl->save_tags($album_id,$album['tags'],1);
            $this->plugin->trigger('created_album',$album_id);
            
            form_ajax_success('box',lang('create_album_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_failed('text',lang('create_album_failed'));
        }
    }
    
    function modify(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $info = $this->mdl_album->get_info($id);
        $info['desc'] = safe_invert($info['desc']);
        $this->output->set('system_enable_comment',$this->setting->get_conf('system.enable_comment'));
        $this->output->set('info',$info);
        $cate_mdl =& loader::model('category');
        $cate_list = $cate_mdl->get_categorys_width_cache();
        $this->output->set('cate_list',$cate_list);
        $this->output->set('fromurl',base64_encode(site_link('albums','modify',array('id'=>$id))));
        $this->render();
    }
    
    function update(){
        need_login('ajax');
        
        $album_id = intval($this->getGet('id'));
        $album['name'] = safe_convert($this->getPost('album_name'));
        $album['desc'] = safe_convert($this->getPost('desc'));
        $album['priv_type'] = $this->getPost('priv_type','0');
        $album['tags'] = safe_convert($this->getPost('album_tags'));
        $album['priv_pass'] = $this->getPost('priv_pass');
        $album['priv_question'] = safe_convert($this->getPost('priv_question'));
        $album['priv_answer'] = safe_convert($this->getPost('priv_answer'));
        $album['cate_id'] = intval($this->getPost('cate_id'));
        $album['enable_comment'] = intval($this->getPost('enable_comment'));
        
        if($album['name'] == ''){
            form_ajax_failed('text',lang('album_name_empty'));
        }
        if($album['priv_type'] == '1'){
            if($album['priv_pass']==''){
                form_ajax_failed('text',lang('album_password_empty'));
            }
        }
        if($album['priv_type'] == '2'){
            if($album['priv_question'] == ''){
                form_ajax_failed('text',lang('album_question_empty'));
            }
            if($album['priv_answer'] == ''){
                form_ajax_failed('text',lang('album_answer_empty'));
            }
        }
        
        if($this->mdl_album->update($album_id,$album)){
            $tag_mdl =& loader::model('tag');
            $tag_mdl->save_tags($album_id,$album['tags'],1);
            
            $photo_mdl =& loader::model('photo');
            $photo_mdl->update_by_aid($album_id,array('cate_id'=>$album['cate_id']));

            $this->plugin->trigger('modified_album',$album_id);
            
            form_ajax_success('box',lang('modify_album_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_failed('text',lang('modify_album_failed'));
        }
    }
    //set cover
    function update_cover(){
        need_login('ajax_page');
        
        $pic_id = intval($this->getGet('pic_id'));
        
        if($this->mdl_album->set_cover($pic_id)){
            ajax_box(lang('set_cover_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('set_cover_failed'));
        }
    }
    
    function confirm_delete(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $this->output->set('id',$id);
        $album_info = $this->mdl_album->get_info($id);
        $this->output->set('album_name',$album_info['name']);
        $this->render();
    }
    
    function delete(){
        need_login('ajax_page');
        $album_id = intval($this->getGet('id'));
        
        if($this->mdl_album->trash($album_id)){
            $this->plugin->trigger('trashed_album',$album_id);
            
            ajax_box(lang('delete_album_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('delete_album_failed'));
        }
    }
    
    function confirm_delete_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_album_to_delete'));
        }
        $this->render();
    }
    
    function delete_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_album_to_delete'));
        }else{
            if($this->mdl_album->trash_batch(array_keys($ids))){
                $this->plugin->trigger('trashed_many_albums',array_keys($ids));
                
                ajax_box(lang('batch_delete_album_success'),null,1,$_SERVER['HTTP_REFERER']);
            }else{
                ajax_box(lang('batch_delete_album_failed'));
            }
        }
    }
    
    function modify_name_inline(){
        need_login('ajax_inline');
        
        $id = intval($this->getGet('id'));
        $album_info = $this->mdl_album->get_info($id);
        $this->output->set('info',$album_info);
        $this->render();
    }
    
    function rename(){
        need_login('ajax');
        
        $id = intval($this->getGet('id'));
        $arr['name'] = safe_convert($this->getPost('name'));
        if($arr['name'] == ''){
            form_ajax_failed('text',lang('album_name_empty'));
        }
        if($this->mdl_album->update($id,$arr)){
            $this->plugin->trigger('renamed_album',$id);
            
            form_ajax_success('text',$arr['name']);
        }else{
            form_ajax_failed('text',lang('failed_to_rename_album'));
        }
        return;
    }
    
    function modify_tags_inline(){
        need_login('ajax_inline');
        
        $id = intval($this->getGet('id'));
        $album_info = $this->mdl_album->get_info($id);
        $this->output->set('info',$album_info);
        $this->render();
    }
    function save_tags(){
        need_login('ajax');
        
        $id = intval($this->getGet('id'));
        $tags = safe_convert($this->getPost('tags'));
        
        if( $this->mdl_album->update($id,array('tags'=>$tags)) ){
            $tag_mdl =& loader::model('tag');
            $tag_mdl->save_tags($id,$tags,1);
            
            $this->plugin->trigger('modified_album_tags',$id);
            form_ajax_success('text',lang('tags').': '.$tags);
        }else{
            form_ajax_failed('text',lang('modify_tags_failed'));
        }
        return;
    }
    function modify_desc_inline(){
        need_login('ajax_inline');
        
        $id = intval($this->getGet('id'));
        $album_info = $this->mdl_album->get_info($id);
        $album_info['desc'] = safe_invert($album_info['desc']);
        $this->output->set('info',$album_info);
        $this->render();
    }
    
    function save_desc(){
        need_login('ajax');
        
        $id = intval($this->getGet('id'));
        $desc = safe_convert($this->getPost('desc'));
        if($desc == ''){
            form_ajax_failed('text',lang('empty_album_desc'));
        }
        if( $this->mdl_album->update($id,array('desc'=>$desc)) ){
            $this->plugin->trigger('modified_album_desc',$id);
            form_ajax_success('text',$desc);
        }else{
            form_ajax_failed('text',lang('modify_album_desc_failed'));
        }
        return;
        
    }
    
    function modify_priv(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $album_info = $this->mdl_album->get_info($id);
        $this->output->set('info',$album_info);
        $this->render();
    }
    
    function save_priv(){
        need_login('ajax');
        
        $album['priv_type'] = $this->getPost('priv_type','0');
        $album['priv_pass'] = $this->getPost('priv_pass');
        $album['priv_question'] = safe_convert($this->getPost('priv_question'));
        $album['priv_answer'] = safe_convert($this->getPost('priv_answer'));
        $id = intval($this->getGet('id'));
        
        if($album['priv_type'] == '1'){
            if($album['priv_pass']==''){
                form_ajax_failed('text',lang('album_password_empty'));
            }
        }
        if($album['priv_type'] == '2'){
            if($album['priv_question'] == ''){
                form_ajax_failed('text',lang('album_question_empty'));
            }
            if($album['priv_answer'] == ''){
                form_ajax_failed('text',lang('album_answer_empty'));
            }
        }
        
        if($this->mdl_album->update($id,$album)){
            $this->plugin->trigger('modified_album_priv',$id);
            form_ajax_success('box',lang('modify_album_priv_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_failed('text',lang('modify_album_priv_failed'));
        }
    }
}