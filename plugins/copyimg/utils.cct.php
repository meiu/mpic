<?php

class utils_cct extends pagecore{
    
    function copyurl($config = array()){
        need_login('ajax_bubble');
        global $base_root;
        
        $id = $this->getGet('id');
        $photo_mdl =& loader::model('photo');
        $photo_info = $photo_mdl->get_info($id);
        $photo_info['path'] = img_path($photo_info['path']);
        
        $this->output->set('photo_info',$photo_info);
        
        $html_code = htmlspecialchars($config['tpl']);
        $html_code = str_replace('{thumbpath}',img_path($photo_info['thumb']),$html_code);
        $html_code = str_replace('{imgpath}',$photo_info['path'],$html_code);
        $html_code = str_replace('{imgname}',$photo_info['name'],$html_code);
        $html_code = str_replace('{detailurl}',$base_root.site_link('photos','view',array('id'=>$photo_info['id'])),$html_code);
        
        $this->output->set('html_code',$html_code);        
        loader::view('copyimg:copyurl');
    }
    
    function copyallurl($config = array()){
        need_login('ajax_page');
        global $base_root;
        
        $site_url = $this->setting->get_conf('site.url');
        
        $album_id = $this->getGet('aid');
        
        list($sort,$sort_list) =  get_sort_list(array(),'photo','tu_desc');
        $photo_mdl =& loader::model('photo');
        $pictures = $photo_mdl->get_all(NULL,array('album_id'=>$album_id),$sort);
        $html = '';
        $urls = '';
        $ubb = '';
        if($pictures){
            foreach($pictures as $pic){
                $img_path = img_path($pic['path']);
                $html_code = $config['tpl'];
                $html_code = str_replace('{thumbpath}',img_path($pic['thumb']),$html_code);
                $html_code = str_replace('{imgpath}',$img_path,$html_code);
                $html_code = str_replace('{imgname}',$pic['name'],$html_code);
                $html_code = str_replace('{detailurl}',$base_root.site_link('photos','view',array('id'=>$pic['id'])),$html_code);
                $html .= $html_code.$config['split'];
                $urls .= $img_path."\r\n";
                $ubb .= '[IMG]'.$img_path."[/IMG]\r\n";
            }
        }
        
        $this->output->set('img_url',$urls);
        $this->output->set('img_html',$html);
        $this->output->set('img_ubb',$ubb);
        loader::view('copyimg:copyurlall');
    }
    
    function copyselectedurl($config = array()){
        need_login('ajax_page');
        global $base_root;
        
        $ids = $this->getPost('sel_id');
        if(!$ids || count($ids) == 0){
            ajax_box(lang('copyimg:pls_sel_photo_want_to_copy'));
        }else{
            $site_url = $this->setting->get_conf('site.url');
            $mdl_photo =& loader::model('photo');
            $ids = array_keys($ids);
            $html = '';
            $urls = '';
            $ubb = '';
            foreach($ids as $id){
                $pic = $mdl_photo->get_info($id);
                $img_path = img_path($pic['path']);
                $html_code = $config['tpl'];
                $html_code = str_replace('{thumbpath}',img_path($pic['thumb']),$html_code);
                $html_code = str_replace('{imgpath}',$img_path,$html_code);
                $html_code = str_replace('{imgname}',$pic['name'],$html_code);
                $html_code = str_replace('{detailurl}',$base_root.site_link('photos','view',array('id'=>$pic['id'])),$html_code);
                $html .= $html_code.$config['split'];
                $urls .= $img_path."\r\n";
                $ubb .= '[IMG]'.$img_path."[/IMG]\r\n";
            }
            $this->output->set('img_url',$urls);
            $this->output->set('img_html',$html);
            $this->output->set('img_ubb',$ubb);
            loader::view('copyimg:copyurlall');
        }
    }
}