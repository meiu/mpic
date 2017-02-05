<?php
/**
 * $Id: uri.class.php 290 2011-11-29 08:52:47Z lingter@gmail.com $
 *
 * Parse and make uri
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class uri_cla{
    /**
     * Get pathinfo
     *
     * @return string
     * @author lingter
     */
    function pathinfo(){
        if ( ! isset($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] == ''){
            $strlen = strlen($_SERVER['SCRIPT_NAME']);
            $totallen = strlen($_SERVER['PHP_SELF']);
            return substr($_SERVER['PHP_SELF'],$strlen,$totallen);
        }else{
            return $_SERVER['PATH_INFO'];
        }
    }
    /**
     * Make uri
     *
     * @param string $ctl 
     * @param string $act 
     * @param array $pars 
     * @return string
     * @author Lingter
     */
    function mk_uri($ctl='default',$act='index',$pars=array()){
        global $base_path;
        $arr = array();
        if($ctl!='default'){
            $arr['ctl'] = $ctl;
        }
        if($act!='index'){
            $arr['act'] = $act;
        }
        $url = '';
        $arr = array_merge($arr,$pars);
        foreach($arr as $k=>$v){
            $url .= $k.'='.rawurlencode($v).'&';
        }
        if($url){
            $url =  $base_path.'?'.rtrim($url,'&');//str_replace('&','&amp;',rtrim($url,'&'));
        }else{
            $url = $base_path;
        }
        $plugin =& loader::lib('plugin');
        $url = $plugin->filter('make_url',$url,$ctl,$act,$pars);
        return $url;
    }
    /**
     * Parse uri
     *
     * @return array
     * @author Lingter
     */
    function parse_uri(){
        $arg['ctl'] = isset($_GET['ctl'])?$_GET['ctl']:'default';
        $arg['act'] = isset($_GET['act'])?$_GET['act']:'index';
        //过滤私有的方法
        $arg['act'] = ltrim($arg['act'],'_');

        unset($_GET['ctl']);
        unset($_GET['act']);
        $arg['pars'] = $_GET;
        
        $plugin =& loader::lib('plugin');
        $plugin_uri = $plugin->filter('parse_url',$arg);
        return $plugin_uri;
    }
    
}