<?php

class users_ctl extends pagecore{
    
    function _init(){
        
    }
    
    function login(){
        $ajax = $this->getGet('ajax');
        if($ajax == 'true'){
            $this->output->set('ajax',true);
        }else{
            $this->output->set('ajax',false);

            //面包屑
            $crumb_nav = array();
            $crumb_nav[] = array('name'=>lang('user_login'));

            $this->page_crumb($crumb_nav);

            $page_title = lang('user_login').' '.$this->setting->get_conf('site.title');
            $page_keywords = $this->setting->get_conf('site.keywords');
            $page_description = $this->setting->get_conf('site.description');
            $this->page_init($page_title,$page_keywords,$page_description);
        }
        $this->output->set('enable_login_captcha',$this->setting->get_conf('system.enable_login_captcha'));
        $this->render();
    }
    
    function check_login(){
        $login_name = safe_convert($this->getPost('login_name'));
        $login_pass = $this->getPost('login_pass');
        $remember_pass = $this->getPost('remember_pass');
        $normal = $this->getPost('normal');

        if($this->setting->get_conf('system.enable_login_captcha')){
            $captcha =& loader::lib('captcha');
            if(!$captcha->check($this->getPost('captcha')))
                form_ajax_failed('text',lang('invalid_captcha_code'));
        }
        if(!$login_name){
            form_ajax_failed('text',lang('username_empty'));
        }
        if(!$login_pass){
            form_ajax_failed('text',lang('userpass_empty'));
        }
        if($remember_pass){
            $expire_time = time()+86400*30; //记住密码30天
        }else{
            $expire_time = 0;
        }
        $go_url = $normal?site_link('default'):$_SERVER['HTTP_REFERER'];
        if($this->user->set_login($login_name,md5($login_pass),$expire_time)){
            $this->plugin->trigger('user_loged_in',$login_name);
            
            //登录时检查更新
            if($this->setting->get_conf('system.enable_auto_update')){
                check_update();
            }

            form_ajax_success('box',lang('login_success'),null,0.5,$go_url);            
        }else{
            form_ajax_failed('text',lang('username_pass_error'));
        }
    }
    
    function profile(){
        if(!$this->user->loggedin()){
            redirect(site_link('users','login'));
        }
        
        $mdl_theme =& loader::model('theme');
        $themes = $mdl_theme->all_themes();
        $this->output->set('themes',$themes);
        
        //面包屑
        $crumb_nav = array();
        $crumb_nav[] = array('name'=>lang('modify_profile'));

        $this->page_crumb($crumb_nav);
        
        $page_title = lang('modify_profile').' - '.$this->setting->get_conf('site.title');
        $page_keywords = $this->setting->get_conf('site.keywords');
        $page_description = $this->setting->get_conf('site.description');
        $this->page_init($page_title,$page_keywords,$page_description);
        $this->render();
    }
    
    function save_profile(){
        need_login('ajax');
        
        $current_id = $this->user->get_field('id');
        
        $arr['user_nicename'] = safe_convert($this->getPost('user_nicename'));
        $new_pass = $this->getPost('new_pass');
        $old_pass = $this->getPost('old_pass');
        $new_pass_again = $this->getPost('new_pass_again');
        $extra_arr = $this->getPost('extra');
        if($extra_arr['email'] && !check_email($extra_arr['email'])){
            form_ajax_failed('text',lang('error_email'));
        }
        
        if($new_pass){
            if(!$this->user->check_pass($current_id,md5($old_pass))){
                form_ajax_failed('text',lang('old_pass_error'));
            }
            if($new_pass != $new_pass_again){
                form_ajax_failed('text',lang('pass_twice_error'));
            }
            $arr['user_pass'] = md5($new_pass);
        }
        if($this->user->update($current_id,$arr)){
            $this->user->save_extra($current_id,$extra_arr);
            
            form_ajax_success('box',lang('modify_success').($new_pass?lang('pass_edit_ok'):''),null,0.5,$_SERVER['HTTP_REFERER']);
        }else{
            form_ajax_failed('text',lang('modify_failed'));
        }
    }
    
    function logout(){
        $current_username = $this->user->get_field('user_name');
        
        $this->user->clear_login();
        $this->plugin->trigger('user_loged_out',$current_username);
        ajax_box(lang('logout_success'),null,0.5,$_SERVER['HTTP_REFERER']);
    }
}