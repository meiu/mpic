<?php
/**
 * $Id: pagecore.php 341 2012-02-02 09:37:19Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */
class pagecore{
    
    function pagecore(){
        $this->output =& loader::lib('output');
        $this->db =& loader::database();
        $this->user =& loader::model('user');
        $this->setting =& loader::model('setting');
        $this->plugin =& loader::lib('plugin');
        
        $this->plugin->trigger('controller_init');
    }
    
    function _init(){
        ;
    }
    function _called(){
        ;
    }
    
    /*
     run page init
     initialize page head and user status
    */
    function page_init($title = '',$keywords = '',$description='',$album_id=null,$photo_id=null){
        $plugin =& loader::lib('plugin');
        
        $head_str = "<title>{$title}</title>\n";
        $head_str .= "<meta name=\"keywords\" content=\"{$keywords}\" />\n";
        $head_str .= "<meta name=\"description\" content=\"{$description}\" />\n";
        $meu_head = $plugin->filter('meu_head',$head_str,$album_id,$photo_id);
        $meu_head .= "\n".'<meta name="generator" content="Mei'.'u'.'Pic '.MPIC_VERSION.'" />'."\n";
        
        $feed_url = $album_id?site_link('feed','index',array('aid'=>$album_id)):site_link('feed');
        $feed_title = $this->setting->get_conf('site.title');
        $meu_head .= "<link rel=\"alternate\" title=\"{$feed_title}\" href=\"".$feed_url."\" type=\"application/rss+xml\" />\n";
        $this->output->set('meu_head',$meu_head);

        if($this->user->loggedin()){
            //更新提示
            $this->output->set('update_info',$this->setting->get_conf('update'));
        }
        $user_status = loader::view('block/user_status',false);

        $this->output->set('user_status',$plugin->filter('user_status',$user_status));
        $page_head = $plugin->filter('page_head','',$album_id,$photo_id);
        $page_foot = $plugin->filter('page_foot','',$album_id,$photo_id);
        
        $this->output->set('page_head',$page_head);
        $this->output->set('page_foot',$page_foot);
        $this->output->set('trash_status',has_trash());
        
        $mdl_nav =& Loader::model('nav');
        $nav_menu = $mdl_nav->get_enabled_navs();
        $this->output->set('nav_menu',$nav_menu);

        
        //$plugin->filter('main_menu',$nav_menu,$album_id,$photo_id);
    }
    
    function page_crumb($nav){
        $crumb_nav[] = array('name'=>lang('album_index'),'link'=>site_link('default','index'));
        $crumb_nav = array_merge($crumb_nav,$nav);

        $this->output->set('crumb_nav',$crumb_nav);
        $crumb = loader::view('block/crumb',false);

        $this->output->set('page_crumb',$crumb);
    }

    function render($type = 'normal'){
        if($type == 'normal'){
            $tpl = IN_CTL.'/'.IN_ACT;
        }else{
            $tpl = IN_CTL.'/'.IN_ACT.'_'.$type;
        }
        loader::view($tpl);
    }
    
    function isPost(){
        return isPost();
    }

    function getGet($key,$default=''){
        return getGet($key,$default);
    }

    function getPost($key,$default=''){
        return  getPost($key,$default);
    }

    function getRequest($key,$default=''){
        return getRequest($key,$default);
    }
    
    function getGets(){
        return getGets();
    }
    
    function getPosts(){
        return getPosts();
    }

    function getRequests(){
        return getRequests();
    }

}