<?php

class setting_ctl extends pagecore{
    
    function _init(){
        $setting_menu = $this->plugin->filter('setting_menu','');
        $this->output->set('setting_menu',$setting_menu);
    }
    
    function index(){
        need_login('page');
        
        $site = $this->setting->get_conf('site');
        $site['description'] = safe_invert($site['description']);
        $site['footer'] = safe_invert($site['footer'],true);
        $site['share_title'] = safe_invert($site['share_title']);
        $this->output->set('site',$site);
        $this->output->set('enable_comment',$this->setting->get_conf('system.enable_comment'));
        $this->output->set('enable_comment_captcha',$this->setting->get_conf('system.enable_comment_captcha'));
        $this->output->set('enable_login_captcha',$this->setting->get_conf('system.enable_login_captcha'));
        $this->output->set('comment_audit',$this->setting->get_conf('system.comment_audit'));

        $this->output->set('enable_auto_update',$this->setting->get_conf('system.enable_auto_update'));
        $this->output->set('show_process_info',$this->setting->get_conf('system.show_process_info'));
        $this->output->set('gravatar_url',$this->setting->get_conf('system.gravatar_url'));
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('basic_setting'));
        
        $this->page_crumb($crumb_nav);
        
        $page_title = lang('basic_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function save_basic(){
        need_login('ajax_box');
        
        $site = $this->getPost('site');
        $site['title'] = safe_convert($site['title']);
        $site['url'] = safe_convert($site['url']);
        $site['keywords'] = safe_convert($site['keywords']);
        $site['description'] = safe_convert($site['description']);
        $site['share_title'] = safe_convert($site['share_title']);
        
        $gravatar_url = safe_convert($this->getPost('gravatar_url'));
        
        if($site['title'] == ''){
            form_ajax_failed('box',lang('empty_site_name'));
        }
        if($site['url'] == ''){
            form_ajax_failed('box',lang('empty_site_url'));
        }
        $this->setting->set_conf('site.title',$site['title']);
        $this->setting->set_conf('site.url',$site['url']);
        $this->setting->set_conf('site.keywords',$site['keywords']);
        $this->setting->set_conf('site.description',$site['description']);
        $this->setting->set_conf('site.share_title',$site['share_title']);
        $this->setting->set_conf('system.gravatar_url',$gravatar_url);
        $this->setting->set_conf('system.comment_audit',intval($this->getPost('comment_audit')));

        if($this->getPost('enable_comment')){
            $this->setting->set_conf('system.enable_comment',true);
        }else{
            $this->setting->set_conf('system.enable_comment',false);
        }

        if($this->getPost('enable_comment_captcha')){
            $this->setting->set_conf('system.enable_comment_captcha',true);
        }else{
            $this->setting->set_conf('system.enable_comment_captcha',false);
        }

        if($this->getPost('enable_login_captcha')){
            $this->setting->set_conf('system.enable_login_captcha',true);
        }else{
            $this->setting->set_conf('system.enable_login_captcha',false);
        }
        
        if($this->getPost('enable_auto_update')){
            $this->setting->set_conf('system.enable_auto_update',true);
        }else{
            $this->setting->set_conf('system.enable_auto_update',false);
        }

        
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function display(){
        need_login('page');
        
        $site = $this->setting->get_conf('site');
        $site['footer'] = safe_invert($site['footer'],true);
        $this->output->set('site',$site);
        $this->output->set('show_process_info',$this->setting->get_conf('system.show_process_info'));

        $album_sort_list = array(lang('create_time') => 'ct',lang('upload_time') => 'ut',lang('photo_nums') => 'p');
        $photo_sort_list = array(lang('upload_time') => 'tu',lang('taken_time') => 'tt',lang('hits')=>'h',lang('comments_nums')=>'c',lang('photo_name')=>'n');
        
        $this->output->set('album_sort_list',$album_sort_list);
        $this->output->set('photo_sort_list',$photo_sort_list);
        
        $album_pageset = $this->setting->get_conf('display.album_pageset');
        $photo_pageset = $this->setting->get_conf('display.photo_pageset');
        $album_sort_default = $this->setting->get_conf('display.album_sort_default');
        $photo_sort_default = $this->setting->get_conf('display.photo_sort_default');

        $this->output->set('album_pageset',$album_pageset);
        $this->output->set('photo_pageset',$photo_pageset);
        $this->output->set('album_sort_default',$album_sort_default);
        $this->output->set('photo_sort_default',$photo_sort_default);
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('display_setting'));
        
        $this->page_crumb($crumb_nav);
        
        $page_title = lang('display_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }

    function save_display(){
        $site = $this->getPost('site');
        $display = $this->getPost('display');

        $this->setting->set_conf('site.footer',safe_convert($site['footer'],true,true));
        $this->setting->set_conf('site.logo',$site['logo']);
        $this->setting->set_conf('display.album_pageset',$display['album_pageset']);
        $this->setting->set_conf('display.photo_pageset',$display['photo_pageset']);
        $this->setting->set_conf('display.album_sort_default',$display['album_sort_default']);
        $this->setting->set_conf('display.photo_sort_default',$display['photo_sort_default']);

        if($this->getPost('show_process_info')){
            $this->setting->set_conf('system.show_process_info',true);
        }else{
            $this->setting->set_conf('system.show_process_info',false);
        }
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }

    function upload(){
        need_login('page');
        
        $upload = $this->setting->get_conf('upload');
        $this->output->set('upload',$upload);
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('upload_setting'));
        
        $this->page_crumb($crumb_nav);
        

        $page_title = lang('upload_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function save_upload(){
        need_login('ajax_box');
        
        $upload = $this->getPost('upload');
        $enable_pre_resize = $this->getPost('enable_pre_resize');

        if($upload['resize_quality'] < 1 || $upload['resize_quality'] > 100){
             form_ajax_failed('box',lang('resize_quality_error'));
        }
        
        //客户端预处理
        if($enable_pre_resize){
            if($upload['resize_width'] == '' || $upload['resize_width'] < 1){
                form_ajax_failed('box',lang('resize_width_error'));
            }
            if($upload['resize_height'] == '' || $upload['resize_height'] < 1){
                form_ajax_failed('box',lang('resize_height_error'));
            }
            $this->setting->set_conf('upload.resize_width',intval($upload['resize_width']));
            $this->setting->set_conf('upload.resize_height',intval($upload['resize_height']));
            $this->setting->set_conf('upload.resize_quality',intval($upload['resize_quality']));
            $this->setting->set_conf('upload.enable_pre_resize',true);
        }else{
            $this->setting->set_conf('upload.enable_pre_resize',false);
        }
        
        //大图按比例缩放
        $enable_cut_big_pic = $this->getPost('enable_cut_big_pic');
        if($enable_cut_big_pic){
            if($upload['max_width'] == '' || $upload['max_width']<1){
                form_ajax_failed('box',lang('max_width_error'));
            }
            if($upload['max_height'] == '' || $upload['max_height']<1){
                form_ajax_failed('box',lang('max_height_error'));
            }

            $this->setting->set_conf('upload.max_width',intval($upload['max_width']));
            $this->setting->set_conf('upload.max_height',intval($upload['max_height']));
            $this->setting->set_conf('upload.enable_cut_big_pic',true);
        }else{
            $this->setting->set_conf('upload.enable_cut_big_pic',false);
        }

        //缩略图设置
        $enable_thumb_cut = $this->getPost('enable_thumb_cut');
        if($upload['thumb_width'] == '' || $upload['thumb_width']<1){
            form_ajax_failed('box',lang('thumb_width_error'));
        }
        if($upload['thumb_height'] == '' || $upload['thumb_height']<1){
            form_ajax_failed('box',lang('thumb_height_error'));
        }
        if(!$enable_thumb_cut){
            $this->setting->set_conf('upload.enable_thumb_cut',false);
        }else{
            $this->setting->set_conf('upload.enable_thumb_cut',true);
        }
        $this->setting->set_conf('upload.thumb_width',intval($upload['thumb_width']));
        $this->setting->set_conf('upload.thumb_height',intval($upload['thumb_height']));
        
        if($this->getPost('use_old_imgname')){
            $this->setting->set_conf('upload.use_old_imgname',true);
        }else{
            $this->setting->set_conf('upload.use_old_imgname',false);
        }

        //封面缩略图设置
        $this->setting->set_conf('upload.allow_size',$upload['allow_size']);
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function watermark(){
        need_login('page');
        
        $fonts = get_fonts();
        $this->output->set('fonts',$fonts);
        
        $watermark = $this->setting->get_conf('watermark');
        $this->output->set('watermark',$watermark);
        

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('watermark_setting'));
        
        $this->page_crumb($crumb_nav);


        $page_title = lang('watermark_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function save_watermark(){
        need_login('ajax_box');
        
        $watermark_type = $this->getPost('watermark_type','0');
        $watermark = $this->getPost('watermark');
        
        if(!isset($watermark['water_mark_pos'])){
            $watermark['water_mark_pos'] = 5;
        }
        
        if($watermark_type == 1){
            if($watermark['water_mark_image'] == ''){
                form_ajax_failed('box',lang('water_mark_image_error'));
            }
            if($watermark['water_mark_opacity'] < 0 || $watermark['water_mark_opacity'] > 100){
                form_ajax_failed('box',lang('water_mark_opacity_error'));
            }
            $this->setting->set_conf('watermark.water_mark_image',$watermark['water_mark_image']);
            $this->setting->set_conf('watermark.water_mark_opacity',$watermark['water_mark_opacity']);
            $this->setting->set_conf('watermark.water_mark_pos',$watermark['water_mark_pos']);
        }elseif($watermark_type == 2){
            if($watermark['water_mark_string'] == ''){
                form_ajax_failed('box',lang('water_mark_string_error'));
            }
            if($watermark['water_mark_fontsize'] < 1){
                form_ajax_failed('box',lang('water_mark_fontsize_error'));
            }
            if(!check_color($watermark['water_mark_color'])){
                form_ajax_failed('box',lang('water_mark_color_error'));
            }
            if(!isset($watermark['water_mark_font']) || !$watermark['water_mark_font']){
                form_ajax_failed('box',lang('water_mark_font_error'));
            }
            if($watermark['water_mark_angle'] < 0 || $watermark['water_mark_angle'] > 360){
                form_ajax_failed('box',lang('water_mark_angle_error'));
            }
            if($watermark['water_mark_opacity'] < 0 || $watermark['water_mark_opacity'] > 100){
                form_ajax_failed('box',lang('water_mark_opacity_error'));
            }
            $this->setting->set_conf('watermark.water_mark_string',$watermark['water_mark_string']);
            $this->setting->set_conf('watermark.water_mark_fontsize',$watermark['water_mark_fontsize']);
            $this->setting->set_conf('watermark.water_mark_color',$watermark['water_mark_color']);
            $this->setting->set_conf('watermark.water_mark_font',$watermark['water_mark_font']);
            $this->setting->set_conf('watermark.water_mark_angle',$watermark['water_mark_angle']);
            $this->setting->set_conf('watermark.water_mark_opacity',$watermark['water_mark_opacity']);
            $this->setting->set_conf('watermark.water_mark_pos',$watermark['water_mark_pos']);
        }
        $this->setting->set_conf('watermark.type',$watermark_type);
        
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function fileupload(){
        $json =& loader::lib('json');

        $error = "";
        $msg = "";
        if(!$this->user->loggedin()){
            echo $json->encode(array('error'=>lang('not_authorized'),'msg'=>''));
            exit;
        }
        $upaction = $this->getPost('upaction');
        if($upaction == 'watermark'){
            $path_dir = 'data/watermark';
            $file_type = array('png','jpg','gif');
        }elseif($upaction == 'logo'){
            $path_dir = 'data/logo';
            $file_type = array('png','jpg','gif');
        }
        
        
        $fileElementName = 'fileToUpload';
        if(!empty($_FILES[$fileElementName]['error'])){
            $error = lang('upload_error');
        }elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none'){
            $error = lang('need_sel_upload_file');
        }else{
            $filename = $_FILES[$fileElementName]['name'];
            $fileext = file_ext($filename);
            $filesize = $_FILES[$fileElementName]['size'];
            $allowsize = allowsize($this->setting->get_conf('upload.allow_size'));
            
            if(!in_array($fileext,$file_type)){
                echo $json->encode(array('error'=>lang('failed_not_support',$filename),'msg'=>''));
                exit;
            }
            if($allowsize && $filesize>$allowsize){
                echo $json->encode(array('error'=>lang('failed_larger_than_usetting',$filename),'msg'=>''));
                exit;
            }
            if($filesize == 0){
                echo $json->encode(array('error'=>lang('failed_if_file',$filename),'msg'=>''));
                exit;
            }
            $storlib =& loader::lib('storage');
            $path = $path_dir.'/'.date('Ymd').'.'.$fileext;
            if( $storlib->upload($path,$_FILES[$fileElementName]['tmp_name']) ){
                $msg = $path;
            }else{
                $error = lang('upload_error');
            }
        }
        
        echo $json->encode(array('error'=>$error,'msg'=>$msg));
        exit;
    }
    
    function themes(){
        need_login('page');

        $mdl_theme =& loader::model('theme');
        $themes = $mdl_theme->all_themes();
        $this->output->set('themes',$themes);

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('theme_setting'));
        
        $this->page_crumb($crumb_nav);

        $page_title = lang('theme_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');

        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function theme_set(){
        need_login('ajax_page');
        
        setcookie('MPIC_THEME','',- 86400 * 365,'/');
        
        $theme = $this->getGet('theme');
        $this->setting->set_conf('system.current_theme',$theme);
        
        ajax_box(lang('enable_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function theme_edit(){
        need_login('ajax_page');
        
        $theme = $this->getGet('theme');
        $this->output->set('theme',$theme);
        $theme_lang_file = ROOTDIR.'themes/'.$theme.'/lang/'.LANGSET.'.lang.php';
        if(file_exists($theme_lang_file)){
            global $language;
            @include($theme_lang_file);
        }
        $tpl =& Loader::model('template');
        $setting_config = $tpl->fetch('_config',$theme,'themes/'.$theme);
        $this->output->set('setting_config',$setting_config);
        $this->render();
    }
    
    function theme_save_setting(){
        need_login('ajax');
        
        $theme = $this->getGet('theme');
        
        $config = $this->getPosts();
        
        $check_config_file = ROOTDIR.'themes/'.$theme.'/_config.php';
        if(file_exists($check_config_file)){
            $theme_lang_file = ROOTDIR.'themes/'.$theme.'/lang/'.LANGSET.'.lang.php';
            if(file_exists($theme_lang_file)){
                global $language;
                @include($theme_lang_file);
            }
            include_once($check_config_file);
            check_theme_config($config);
        }
        
        $this->setting->set_conf('theme_'.$theme,$config);
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function theme_confirm_remove(){
        need_login('ajax_page');
        $theme = $this->getGet('theme');
        $this->output->set('theme',$theme);
        
        $this->render();
    }
    
    function theme_remove(){
        need_login('ajax_page');
        
        $theme = $this->getGet('theme');
        if($theme == ''){
            ajax_box(lang('empty_theme'));
        }
        if($theme == 'default'){
            ajax_box(lang('can_not_delete_default'));
        }
        $current_theme = $this->setting->get_conf('system.current_theme');
        if($current_theme == $theme){
            ajax_box(lang('theme_is_using'));
        }
        
        $theme_mdl =& loader::model('theme');
        if($theme_mdl->remove($theme)){
            ajax_box(lang('delete_theme_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('delete_theme_failed'));
        }
        
    }
    
    function plugins(){
        need_login('page');

        $plugins = $this->plugin->get_plugins();
        $this->output->set('plugins',$plugins);
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('plugin_setting'));
        
        $this->page_crumb($crumb_nav);

        $page_title = lang('plugin_setting').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        $Config = loader::config();
        $this->output->set('safemode',$Config['safemode']);
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function plugin_install(){
        need_login('ajax_page');
        
        $plugin = $this->getGet('plugin');
        
        $plugin_obj = $this->plugin->get_plugin_obj($plugin);
        $plugin_obj && $plugin_obj->callback_install();
        
        if($this->plugin->install_plugin($plugin)){
            ajax_box(lang('install_plugin_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('install_plugin_failed'));
        }
    }
    
    function plugin_enable(){
        need_login('ajax_page');
        
        $plugin = $this->getGet('plugin');
        
        $plugin_obj = $this->plugin->get_plugin_obj($plugin);
        $plugin_obj && $plugin_obj->callback_enable();
        
        if($this->plugin->enable_plugin($plugin)){
            ajax_box(lang('enable_plugin_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('enable_plugin_failed'));
        }
    }
    
    function plugin_disable(){
        need_login('ajax_page');
        
        $plugin = $this->getGet('plugin');
        
        $plugin_obj = $this->plugin->get_plugin_obj($plugin);
        $plugin_obj && $plugin_obj->callback_disable();
        
        if($this->plugin->disable_plugin($plugin)){
            ajax_box(lang('stop_plugin_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('stop_plugin_failed'));
        }
    }
    
    function plugin_remove(){
        need_login('ajax_page');
        
        $plugin = $this->getGet('plugin');
        
        $plugin_obj = $this->plugin->get_plugin_obj($plugin);
        $plugin_obj && $plugin_obj->callback_remove();
        
        if($this->plugin->remove_plugin($plugin)){
            ajax_box(lang('remove_plugin_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            ajax_box(lang('remove_plugin_failed'));
        }
    }
    
    function plugin_config(){
        need_login('ajax_page');
        
        $plugin = $this->getGet('plugin');
        $this->output->set('plugin',$plugin);

        $_config = $this->plugin->get_config($plugin);
        $this->output->set('_config',$_config);
        $tpl =& Loader::model('template');
        $plugin_config_fields = $tpl->fetch('_config',$plugin,'plugins/'.$plugin);
        
        $this->output->set('plugin_config_fields',$plugin_config_fields);
        $this->render();
    }
    
    function plugin_save_config(){
        need_login('ajax');
        
        $plugin = $this->getGet('plugin');
        
        $plugin_obj = $this->plugin->get_plugin_obj($plugin);
        
        $config = $this->getPosts();
        $config = arr_stripslashes($config);
        $plugin_obj && $plugin_obj->save_config($config);
        
        if($this->plugin->save_config($plugin,$config)){
            form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_success('text',lang('save_setting_failed'),null,0.5,$_SERVER['HTTP_REFERER']);
        }
        
    }
    
    function info(){
        need_login('page');
        $utility_mdl =& loader::model('utility');
        $info = $utility_mdl->sys_info();
        $this->output->set('info',$info);
        //$size = dirsize(ROOTDIR.'cache');
        //$this->output->set('cache_size',bytes2u($size));


        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('system_info'));
        
        $this->page_crumb($crumb_nav);

        $page_title = lang('system_info').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');

        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function get_cache_size(){
        need_login('ajax');

        $size = dirsize(ROOTDIR.'cache');
        $cache_size = bytes2u($size);

        echo $cache_size;
    }

    function clear_cache(){
        need_login('ajax_page');

        dir_clear(ROOTDIR.'cache/data');
        dir_clear(ROOTDIR.'cache/templates');
        dir_clear(ROOTDIR.'cache/tmp');
        //清除缩略图片缓存
        $files = glob(ROOTDIR.'cache/dimgs/[a-zA-Z0-9][a-zA-Z0-9]/*.php');//用glob通过通配符搜索所有缓存文件
        if ($files) {
            foreach($files as $file){
                @unlink($file);
            }
        }
        
        ajax_box(lang('clear_cache_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    
    function phpinfo(){
        need_login('page');
        
        phpinfo();
    }
    
    
    function language(){
        need_login('page');
        $utility_mdl =& loader::model('utility');

        $langs = $utility_mdl->get_languages();
        $time_zones = $utility_mdl->get_time_zones();
        
        $this->output->set('lang_list',$langs);
        $this->output->set('time_zones',$time_zones);
        $this->output->set('current_lang',$this->setting->get_conf('system.language'));
        $this->output->set('current_timezone',$this->setting->get_conf('system.timezone'));
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('language_and_locale'));
        
        $this->page_crumb($crumb_nav);
        
        $page_title = lang('language_and_locale').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function save_language(){
        need_login('ajax_box');
        
        $system = $this->getPost('system');
        if($system['language'] == ''){
            form_ajax_failed('box',lang('empty_langset'));
        }
        if($system['timezone'] == ''){
            form_ajax_failed('box',lang('empty_timezone'));
        }
        
        $this->setting->set_conf('system.language',$system['language']);
        $this->setting->set_conf('system.timezone',$system['timezone']);
        
        form_ajax_success('box',lang('save_setting_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
    //重计数量
    function counter(){
        need_login('ajax_box');

        $counter = $this->getPost('counter');
        if(!$counter){
            form_ajax_failed('box',lang('nothing_to_do'),null,2);
        }
        @set_time_limit(0);
        
        if(isset($counter['comments'])){
            $comment_mdl =& loader::model('comment');
            $comment_mdl->recount_all();
        }
        
        if(isset($counter['photos'])){
            $album_mdl =& loader::model('album');
            $list = $album_mdl->get_all();
            if($list){
                foreach($list as $v){
                    $album_mdl->update_photos_num($v['id'],false);
                }
            }
        }
        
        if(isset($counter['tags'])){
            $tag_mdl =& loader::model('tag');
            $tag_mdl->recount_all();
        }
        
        form_ajax_success('box',lang('recounter_success'),null,2);
    }
    //检查更新
    function check_update(){
        $response = check_update();
        if(!$response){
            $data = lang('connect_to_server_failed');
        }else{
            if($response['return'] == 'lastest'){
                $data = lang('your_system_is_up_to_date');
            }elseif($response['return'] == 'new'){
                $newversion = $response['version'];
                $publish_date = $response['pubdate'];
                $data = lang('new_update_available',$newversion,$publish_date).'<a href="'.site_link('update','core',array('version'=>$newversion)).'">'.lang('update_immediately').'</a> '.lang('or').' <a href="'.$response['package'].'">'.lang('update_manually').'</a>';
            }else{
                $data = lang('connect_to_server_failed');
            }
        }
        ajax_box($data,lang('check_update'));
    }
    //自定义菜单
    function nav(){
        need_login('page');

        $mdl_nav =& Loader::model('nav');
        $nav_list = $mdl_nav->get_all();
        $this->output->set('nav_list',$nav_list);

        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('system_setting'),'link'=>site_link('setting'));
        $crumb_nav[] = array('name'=>lang('setting_nav'));
        
        $this->page_crumb($crumb_nav);

        $page_title = lang('setting_nav').' - '.lang('system_setting').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    //保存自定义菜单
    function save_nav(){
        need_login('ajax_box');
        
        $mdl_nav =& Loader::model('nav');

        $names = $this->getPost('name');
        $urls = $this->getPost('url');
        $sorts = $this->getPost('sort');
        $dels = $this->getPost('del');
        $enables = $this->getPost('enable');        

        $flag = true;
        //编辑及删除
        if($names){
            foreach($names as $key=>$name){
                $key = intval($key);
                $name = trim($name);
                $urls[$key] = isset($urls[$key])?trim($urls[$key]):'';
                if(isset($dels[$key])){
                    //delete 记录
                    $mdl_nav->delete($key);
                }else{
                    $data = array();
                    if($name){
                        $data['name'] = $name;
                    }
                    if($urls[$key]){
                        $data['url'] = $urls[$key];
                    }
                    if($sorts[$key]){
                        $data['sort'] = intval($sorts[$key]);
                    }
                    if(isset($enables[$key])){
                        $data['enable'] = 1;
                    }else{
                        $data['enable'] = 0;
                    }
                    if(!$mdl_nav->update($key,$data)){
                        $flag = false;
                    }
                }
            }
        }
        //新增
        $newnames = $this->getPost('namenew');
        $newurls = $this->getPost('urlnew');
        $newsorts = $this->getPost('sortnew');
        if($newnames){
            foreach($newnames as $key=>$newname){
                $newname = trim($newname);
                $newurls[$key] = trim($newurls[$key]);
                if($newname == ''){
                    continue;
                }
                if($newurls[$key] == ''){
                    $flag = false;
                    continue;
                }

                $data = array(
                            'name' => $newname,
                            'url' => $newurls[$key],
                            'sort' => $newsorts[$key]?intval($newsorts[$key]):100,
                            'enable' => 1
                        );
                if(!$mdl_nav->save($data)){
                    $flag = false;
                }
            }
        }
        //清除菜单缓存
        $mdl_nav->clear_nav_cache();

        if($flag){
            form_ajax_success('box',lang('nav_save_succ'),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_failed('box',lang('nav_save_fail'),null,2,$_SERVER['HTTP_REFERER']);
        }
    }
}