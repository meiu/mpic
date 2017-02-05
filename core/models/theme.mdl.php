<?php
/**
 * $Id: theme.mdl.php 202 2011-06-06 04:45:21Z lingter $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */
class theme_mdl{

    function all_themes(){
        $themedir = ROOTDIR.'themes';
        $themes = array();
        $mdl_setting =& loader::model('setting');
        $current_theme = $mdl_setting->get_conf('system.current_theme','default');
        
        if($directory = @dir($themedir)) {
            while($entry = $directory->read()) {
                $theme_config = false;
                $theme_name = '';
                $theme_copyright = '';
                
                $theme_path = $themedir.'/'.$entry;
                $info_path = $theme_path.'/info.php';
                $config_path = $theme_path.'/_config.htm';
                $preview_path = $theme_path.'/preview.jpg';
                if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$entry)){
                    continue;
                }
                if(is_dir($theme_path) && file_exists($info_path)){
                    include($info_path);
                    if($entry == $current_theme){
                        $iscurrent = true;
                    }else{
                        $iscurrent = false;
                    }
                    $db_theme_config = $mdl_setting->get_conf('theme_'.$entry);
                    if($theme_config && !$db_theme_config){
                        $mdl_setting->set_conf('theme_'.$entry,$theme_config);
                    }
                    $themes[] = array(
                        'name' => $theme_name,
                        'dir' => $entry,
                        'copyright' => $theme_copyright,
                        'withconfig' => file_exists($config_path),
                        'preview' => file_exists($preview_path)?$GLOBALS['base_path'].'themes/'.$entry.'/preview.jpg':'',
                        'iscurrent' =>$iscurrent
                    );
                }
            }
            $directory->close();
        }
        return $themes;
    }
    
    function remove($theme){
        $theme_dir = ROOTDIR.'themes/'.$theme;
        if(is_dir($theme_dir) && deldir($theme_dir)){
            $setting_mdl =& loader::model('setting');
            $setting_mdl->remove_conf('theme_'.$theme,false);
            return true;
        }else{
            return false;
        }
    }
}