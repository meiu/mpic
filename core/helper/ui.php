<?php
//表单提交成功提示
function form_ajax_success($type = 'box|text', $content , $title = null, $close_time = 0 , $forward = ''){
    form_ajax($type,true,$content,$title,$close_time,$forward);
}
//表单提交失败提示
function form_ajax_failed($type = 'box|text', $content , $title = null, $close_time = 0 , $forward = ''){
    form_ajax($type,false,$content,$title,$close_time,$forward);
}

function form_ajax($type = 'box|text', $flag , $content , $title = null, $close_time = 0 , $forward = ''){
    no_cache_header();
    
    if($type == 'box'){
        $content = ajax_box($content,$title,$close_time,$forward,false);
    }
    $json =& loader::lib('json');
    echo $json->encode(
        array('ret'=>$flag,'html'=>$content)
    );
    exit;
}

//浮动图层内容
function ajax_box( $content , $title = '', $close_time = 0 , $forward = '' , $display = true )
{   
    if(!$title){
        $title = lang('system_notice');
    }
    $output=&loader::lib('output');
    $output->set('content',$content);
    $output->set('title',$title);
    $output->set('close_time',$close_time);
    $output->set('forward',$forward);
    if($display){
        loader::view('block/ajax_box');
        exit;
    }else{
        return loader::view('block/ajax_box',false);
    }
}

//排序下拉菜单
function get_sort_list($setting,$type,$default=''){
    $setting_mdl =& loader::model('setting');
    if($type=='album'){
        $default = $setting_mdl->get_conf('display.album_sort_default','ct_desc');
    }else{
        $default = $setting_mdl->get_conf('display.photo_sort_default','tu_desc');
    }
    $sort = isset($_COOKIE['Mpic_sortset_'.$type])?$_COOKIE['Mpic_sortset_'.$type]:$default;
    $menu_data = array();
    foreach ($setting as $k => $v) {
        $data = array();
        $data['name'] = $k;
        $data['field'] = $v;
        if($v.'_asc' == $sort){
            $data['c_sort'] = 'asc';
            $data['t_sort'] = 'desc';
            $data['is_current'] = true;
        }elseif($v.'_desc' == $sort){
            $data['c_sort'] = 'desc';
            $data['t_sort'] = 'asc';
            $data['is_current'] = true;
        }else{
            $data['c_sort'] = 'asc';
            $data['t_sort'] = 'desc';
            $data['is_current'] = false;
        }
        $menu_data[] = $data;
    }

    $output=&loader::lib('output');
    $output->set('sort_menu',$menu_data);
    $output->set('sort_menu_type',$type);
    $str = loader::view('block/sort_menu',false);
    return array($sort,$str);
}
//排序分页菜单
function get_page_setting($type){
    $arr = array(12,30,56);
    
    $setting_mdl =& loader::model('setting');
    if($type=='album'){
        $default = $setting_mdl->get_conf('display.album_pageset',12);
    }else{
        $default = $setting_mdl->get_conf('display.photo_pageset',12);
    }

    $current = isset($_COOKIE['Mpic_pageset_'.$type])?$_COOKIE['Mpic_pageset_'.$type]:$default;
    
    $output=&loader::lib('output');
    $output->set('pageset_menu',$arr);
    $output->set('pageset_menu_current',$current);
    $output->set('pageset_menu_type',$type);
    $str = loader::view('block/pageset_menu',false);

    return array($current,$str);
}