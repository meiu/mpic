<?php

class trash_ctl extends pagecore{
    
    function _init(){
        $this->mdl_album = & loader::model('album');
        $this->mdl_photo = & loader::model('photo');
    }
    
    function index(){
        need_login('page');
        
        $type = intval($this->getGet('type','1'));
        $page = intval($this->getGet('page',1));
        
        $deleted_albums = $this->mdl_album->get_trash_count();
        $deleted_photos = $this->mdl_photo->get_trash_count();
        if($deleted_albums <= 0 && $deleted_photos<= 0){
            trash_status(2);
            $this->output->set('isempty',true);
        }else{
            if($type == 1){
                $data = $this->mdl_album->get_trash($page);
                if(is_array($data['ls'])){
                    foreach($data['ls'] as $k=>$v){
                        if($v['cover_id']){
                            $cover_info = $this->mdl_photo->get_info($v['cover_id'],'thumb');
                            if($cover_info)
                                $data['ls'][$k]['cover_path'] = $cover_info['thumb'];
                            else
                                $data['ls'][$k]['cover_id'] = 0;
                        }
                    }
                }
            }elseif($type == 2){
                $data = $this->mdl_photo->get_trash($page);
            }
            $pageurl = site_link('trash','index',array('type'=>$type,'page'=>'[#page#]'));
            $page_obj =& loader::lib('page');
            $pagestr = $page_obj->fetch($data['total'],$data['current'],$pageurl);
            $this->output->set('isempty',false);
            $this->output->set('pagestr',$pagestr);
            $this->output->set('data',$data['ls']);
            $this->output->set('deleted_albums',$deleted_albums);
            $this->output->set('deleted_photos',$deleted_photos);
            $this->output->set('type',$type);
        }

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=> lang('recycle'));

        $this->page_crumb($crumb_nav);

        $page_title = lang('recycle').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        $this->page_init($page_title,$page_keywords,$page_description);
        
        $this->render();
    }
    
    function confirm_delete(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $type = intval($this->getGet('type'));
        $this->output->set('id',$id);
        $this->output->set('type',$type);
        if($type == 1){
            $info = $this->mdl_album->get_info($id);
        }elseif($type == 2){
            $info = $this->mdl_photo->get_info($id);
        }
        $this->output->set('name',$info['name']);
        $this->render();
    }
    
    function delete(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $type = intval($this->getGet('type'));
        if($type == 1){
            $ret = $this->mdl_album->real_delete($id);
        }elseif($type == 2){
            $ret = $this->mdl_photo->real_delete($id);
        }
        if($ret){
            ajax_box(lang('real_delete_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('real_delete_failed'));
        }
    }
    
    function confirm_delete_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        $type = intval($this->getGet('type'));
        $this->output->set('type',$type);
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_photo_album_del'));
        }
        $this->render();
    }
    
    
    function delete_batch(){
        need_login('ajax_page');
        
        $type = intval($this->getGet('type'));
        $ids = $this->getPost('sel_id');
        if(is_array($ids)){
            foreach($ids as $id => $v){
                $id = intval($id);
                if($type == 1){
                    $ret = $this->mdl_album->real_delete($id);
                }elseif($type == 2){
                    $ret = $this->mdl_photo->real_delete($id);
                }
            }
        }
        ajax_box(lang('real_delete_batch_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function restore(){
        need_login('ajax_page');
        
        $id = intval($this->getGet('id'));
        $type = $this->getGet('type');
        if($type == 1){
            $ret = $this->mdl_album->restore($id);
        }elseif($type == 2){
            $ret = $this->mdl_photo->restore($id);
        }
        if($ret){
            ajax_box(lang('restore_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('restore_failed'));
        }
    }
    
    function confirm_restore_batch(){
        need_login('ajax_page');
        
        $ids = $this->getPost('sel_id');
        $type = $this->getGet('type');
        $this->output->set('type',$type);
        if(!$ids || count($ids) == 0){
            ajax_box(lang('pls_sel_photo_album_restore'));
        }
        $this->render();
    }
    
    function restore_batch(){
        need_login('ajax_page');
        
        $type = $this->getGet('type');
        $ids = $this->getPost('sel_id');
        if(is_array($ids)){
            foreach($ids as $id => $v){
                $id = intval($id);
                if($type == 1){
                    $ret = $this->mdl_album->restore($id);
                }elseif($type == 2){
                    $ret = $this->mdl_photo->restore($id);
                }
            }
        }
        ajax_box(lang('restore_batch_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function confirm_emptying(){
        need_login('ajax_page');
        
        $this->render();
    }
    
    function emptying(){
        need_login('ajax_page');
        
        $albums = $this->mdl_album->get_trash();
        if($albums){
            foreach($albums as $v){
                $ret = $this->mdl_album->real_delete($v['id'],$v);
            }
        }
        $photos = $this->mdl_photo->get_trash();
        if($photos){
            foreach($photos as $v){
                $ret = $this->mdl_photo->real_delete($v['id'],$v);
            }
        }
        ajax_box(lang('empty_trash_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
}