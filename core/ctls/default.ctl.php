<?php

class default_ctl extends pagecore{
    
    function index(){

        $index_tpl = ROOTDIR.TPLDIR.DIRECTORY_SEPARATOR.'index.htm';
        if(file_exists($index_tpl)){
            $this->output->set('page_crumb','');

            $page_title =  $this->setting->get_conf('site.title');
            $page_keywords = $this->setting->get_conf('site.keywords');
            $page_description = $this->setting->get_conf('site.description');
            $this->page_init($page_title,$page_keywords,$page_description);

            loader::view('index');
        }else{
            $url = site_link('albums');
            redirect($url);
        }
    }

    function diy(){
        $id = $this->getGet('id');
        $path = ROOTDIR.TPLDIR.DIRECTORY_SEPARATOR.$id.'.htm';
        if($id && preg_match('/^[0-9a-z_\-\.]+$/i',$id) && file_exists($path) ){
            $this->output->set('page_crumb','');

            $page_title =  $this->setting->get_conf('site.title');
            $page_keywords = $this->setting->get_conf('site.keywords');
            $page_description = $this->setting->get_conf('site.description');
            $this->page_init($page_title,$page_keywords,$page_description);

            loader::view($id);
        }else{
            header("HTTP/1.1 404 Not Found");
            showError(lang('404_not_found'));
        }
    }
}