<?php
/**
 * $Id: storage.class.php 150 2011-05-13 05:11:43Z lingter $
 * 
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class storage_cla{
    
    function storage_cla(){
        $this->class_name = 'stor_'.(defined('STORAGE_ENGINE')?constant('STORAGE_ENGINE'):'file');
        if(file_exists(LIBDIR.'stor_engine/'.$this->class_name.'.php')){
            require_once(LIBDIR.'stor_engine/'.$this->class_name.'.php');
            $this->worker = new $this->class_name;
        }else{
            exit(lang('storage_engine_not_exists',IMG_ENGINE));
        }
    }

    function mkdirs($pathname, $mode = 0755){
        return $this->worker->mkdirs($pathname,$mode);
    }
    
    function upload($id,$src){
        return $this->worker->upload($id,$src);
    }
    
    function write($id,$src){
        return $this->worker->write($id,$src);
    }

    function getListByPath($path){
        return $this->worker->getListByPath($path);
    }

    function read($id){
        return $this->worker->read($id);
    }

    function fileExists($id){
        return $this->worker->fileExists($id);
    }

    function deleteFolder($dir){
        return $this->worker->deleteFolder($dir);
    }
    
    function delete($id){
        return $this->worker->delete($id);
    }
    
    function getUrl($id){
        return $this->worker->getUrl($id);
    }

    function getPath($id){
        return $this->worker->getPath($id);
    }
}
?>