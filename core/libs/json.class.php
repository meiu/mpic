<?php
/**
 * $Id: json.class.php 322 2011-12-13 04:48:33Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class json_cla{
   
    var $service_json = null;
    
    function encode($str){
        if(!function_exists('json_encode')){
            if(!$this->service_json){
                include_once('Services_JSON.php');
                $this->service_json = new Services_JSON();
            }
            return $this->service_json->encode($str);
        }
        return json_encode($str);
    }

    function decode($str){
        if(!function_exists('json_decode')){
            if(!$this->service_json){
                include_once('Services_JSON.php');
                $this->service_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            }
            return $this->service_json->decode($str);
        }
        return json_decode($str,true);
    }
}
