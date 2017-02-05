<?php
/**
 * $Id: image.class.php 414 2012-10-25 05:12:48Z lingter@gmail.com $
 * 
 * Image class: resize, cut, rotate, add water mark 
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class image_cla{
    function image_cla(){
        $this->class_name = 'image_'.(defined('IMG_ENGINE')?constant('IMG_ENGINE'):'gd');
        if(file_exists(LIBDIR.'img_engine/'.$this->class_name.'.php')){
            require_once(LIBDIR.'img_engine/'.$this->class_name.'.php');
            $this->worker = new $this->class_name;
        }else{
            exit(lang('img_engine_not_exists',IMG_ENGINE));
        }
    }
    
    function load($filename){
        return $this->worker->load($filename);
    }
    
    function supportType(){
        return $this->worker->supportType();
    }
    
    function getWidth(){
        return $this->worker->getWidth();
    }
    
    function getHeight(){
        return $this->worker->getHeight();
    }
    
    function getExtension(){
        return $this->worker->getExtension();
    }
    
    function save($path){
        $this->worker->save($path);
    }
    
    function output(){
        $this->worker->output();
    }
    
    function resizeTo($w=0,$h=0){
        $this->worker->resizeTo($w,$h);
    }
    function resizeScale($w=0,$h=0){
        $this->worker->resizeScale($w,$h);
    }
    
    function square($v){
        $this->worker->square($v);
    }
    
    function resizeCut($w,$h){
        $this->worker->resizeCut($w,$h);
    }
    
    function rotate($a){
        $this->worker->rotate($a);
    }
    
    function waterMarkSetting($param){
        $this->worker->waterMarkSetting($param);
    }
    
    function waterMark(){
        $this->worker->waterMark();
    }

    function close(){
        $this->worker->close();
    }
}
?>