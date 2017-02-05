<?php

function check_theme_config($posts){
    unset($posts['style']);
    foreach($posts as $k => $v){
        if(!check_color($v)){
            form_ajax_failed('text',lang('style_'.$k).' '.$v.': '.lang('not_aliable_color'));
        }
    }
}