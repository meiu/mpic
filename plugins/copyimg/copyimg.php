<?php

class plugin_copyimg extends plugin{
    var $name = '拷贝图片地址';
    var $description = '一键拷贝图片地址，支持批量复制！';
    var $local_ver = '1.1';
    var $author_name = 'Meiu Studio';
    var $author_url = 'http://www.meiu.cn';
    var $author_email = 'lingter@gmail.com';
    var $config = array(
        'tpl' => '<div align="center"><img src="{imgpath}" /></div>',
        'split' => '<br />'
    );
    
    function init(){
        $this->plugin_mgr->add_filter('photo_control_icons',array('copyimg','photo_list_page_icon'));
        $this->plugin_mgr->add_filter('album_control_icons',array('copyimg','album_control_icons'));
        $this->plugin_mgr->add_filter('photo_multi_opt',array('copyimg','photo_multi_opt'));
         
        $this->plugin_mgr->add_filter('meu_head',array('copyimg','html_head'),10);
        $this->plugin_mgr->add_trigger('custom_page.utils.copyurl',array('copyimg','copyurl_act'),1);
        $this->plugin_mgr->add_trigger('custom_page.utils.copyallurl',array('copyimg','copyallurl_act'),1);
        $this->plugin_mgr->add_trigger('custom_page.utils.copyselectedurl',array('copyimg','copyselectedurl_act'),1);
        
        $usr_mdl =& loader::model('user');
        $this->loggedin = $usr_mdl->loggedin();
    }
    
    function photo_multi_opt($str,$album_id){
        return $str.'<span class="i_copyclip sprite"></span> <a onclick="Madmin.checked_action(\'.selitem\',\''.site_link('utils','copyselectedurl',array('id'=>$album_id)).'\');" href="javascript:void(0)">'.lang('copyimg:copy_sel_img_url').'</a>';
    }
    
    function photo_list_page_icon($str,$album_id,$id){
        if($this->loggedin){
            return $str.'<li><a href="javascript:void(0);" onclick="Mui.bubble.show(this,\''.site_link('utils','copyurl',array('id'=>$id)).'\',true);Mui.bubble.resize(330);" title="'.lang('copyimg:copy_to_clipboard').'"><span class="i_copyclip sprite"></span></a></li>';
        }else{
            return $str;
        }
    }
    
    function album_control_icons($str,$album_id){
        if($this->loggedin){
            return $str.'<li><a href="javascript:void(0);" onclick="Mui.box.show(\''.site_link('utils','copyallurl',array('aid'=>$album_id)).'\',true);" title="'.lang('copyimg:copy_all_to_clipboard').'"><span class="i_copyclip sprite"></span></a></li>';
        }else{
            return $str;
        }
    }
    
    function copyurl_act(){
        include_once('utils.cct.php');
        $ctl = new utils_cct();
        $ctl->_init();
        $ctl->copyurl($this->config);
        $ctl->_called();
    }
    function copyallurl_act(){
        include_once('utils.cct.php');
        $ctl = new utils_cct();
        $ctl->_init();
        $ctl->copyallurl($this->config);
        $ctl->_called();
    }
    function copyselectedurl_act(){
        include_once('utils.cct.php');
        $ctl = new utils_cct();
        $ctl->_init();
        $ctl->copyselectedurl($this->config);
        $ctl->_called();
    }
    
    function html_head($str){
        global $base_path;
        $head_str = <<<eot
<script type="text/javascript" src="{$base_path}plugins/copyimg/ZeroClipboard.js"></script>
<script>
    function show_copy_notice(o,notice){
        var pos = $(o).offset();
        var width = $(o).width();
        var left = pos.left+width-80;
        var top = pos.top;
        
        if($("#copy_notice").length == 0){
            $("body").prepend('<div id="copy_notice"></div>');
        }
        $("#copy_notice").css({"left":left,"top":top});
        $("#copy_notice").html(notice).show().animate({opacity: 1.0}, 1000).fadeOut();
    }
</script>
<style>
    #copy_notice{
        position:absolute;
        z-index:1103;
        height:15px;
        width:80px;
        padding:5px;
        border:1px solid #eee;
        background:#FFFFEE;
    }
</style>
eot;
        return $str.$head_str;
    }
}