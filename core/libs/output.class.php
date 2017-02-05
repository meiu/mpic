<?php
/**
 * $Id: output.class.php 150 2011-05-13 05:11:43Z lingter $
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class output_cla{
    var $data = array();
    
    function set($key,$value){
        $this->data[$key] = $value;
    }
    
    function get($key = ''){
        if(!$key) return $this->data;
        else  return isset($this->data[$key]) ? $this->data[$key] : '';
    }
    
    function getAll(){
        return $this->data;
    }
}