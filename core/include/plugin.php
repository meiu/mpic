<?php
/**
 * $Id: plugin.php 186 2011-05-25 02:36:27Z lingter $
 * plugin base class
 * @author : Lingter
 * @support : http://meiupic.meiu.cn
 * @copyright : (c)2011 meiu.cn lingter@gmail.com
 */
class plugin{
    var $config = array();
    
    function plugin($config = null){
        if(!is_null($config)){
            $this->config = array_merge($this->config, $config);
        }
        $this->db =& loader::database();
        $this->plugin_mgr =& loader::lib('plugin');
    }
    
    function init(){
        ;
    }
    function callback_install(){
        ;
    }
    function callback_enable(){
        ;
    }
    function callback_disable(){
        ;
    }
    function callback_remove(){
        ;
    }
    function save_config($posts){
        ;
    }
}