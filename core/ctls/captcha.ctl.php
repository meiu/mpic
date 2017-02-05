<?php

class captcha_ctl extends pagecore{
    
    function index(){
        $captcha =& loader::lib('captcha');
        $captcha->display();
    }
}