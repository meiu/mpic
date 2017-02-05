<?php

class feed_ctl extends pagecore{
    
    function _init(){
        $this->mdl_photo= & loader::model('photo');
        $this->mdl_album=& loader::model('album');
    }
    
    function index(){
        global $base_path;
        $album_id = intval($this->getGet('aid'));

        $search = array();
        
        $siteurl = $this->setting->get_conf('site.url');
        $sitename = $this->setting->get_conf('site.title');
        $description = $this->setting->get_conf('site.description');
        $sitedomain = substr($siteurl,0,-1*strlen($base_path));
        $feed =& loader::lib('rss');
        
        if($album_id){
            $search['album_id'] = $album_id;
            $album_info = $this->mdl_album->get_info($album_id);
            $feed->title = $album_info['name'].' - '.$sitename;
        }else{
            $feed->title = $sitename;
        }
        $feed->link        = $siteurl;
        $feed->description = $description;

        $this->mdl_photo->set_pageset(50);
        $data = $this->mdl_photo->get_all(1,$search,'tu_desc');
        
        if($data['ls']){
            foreach($data['ls'] as $v){
                $item = new RSSItem();
                $item->title = "<![CDATA[ ".$v['name']." ]]>";;
                $item->link  = $sitedomain.site_link('photos','view',array('id'=>$v['id']));
                $item->set_pubdate($v['create_time']);
                if(!$this->mdl_album->check_album_priv($album_id,isset($album_info)?$album_info:null)){
                    $img = lang('photo_has_priv').'<br />';
                }else{
                    $img = '<img src="'.$siteurl.$v['path'].'" /><br />';
                }
                $item->description = "<![CDATA[ ".$img.$v['desc']." ]]>";
                $feed->add_item($item);
            }
        }
        
        $feed->serve();
    }
}