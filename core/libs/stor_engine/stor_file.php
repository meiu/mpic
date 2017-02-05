<?php

/**
 * Storager:图片文件的存储方式
 * stor_file是默认的存放方法,即将图片文件存放在data文件夹下
 */
class stor_file{
    
    

    function upload($id,$src){
        $tofile = $this->getPath($id);
        $this->mkdirs(dirname($tofile));
        
        if(is_uploaded_file($src)){
            return @move_uploaded_file($src,$tofile);
        }

        if( @copy($src,$tofile) ){
            @unlink($src);
            return true;
        }
        return false;
    }
    
    
    function mkdirs($pathname, $mode = 0755) {
        is_dir(dirname($pathname)) || $this->mkdirs(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }

    function write($id,$content){
        $filename = $this->getPath($path);
        $this->mkdirs(dirname($filename));

        return file_put_contents($file,$content);
    }

    function getListByPath($path){
        $dir = $this->getPath($path);
        $list = array();
        if($directory = @dir($path)) {
            while ($file = $directory->read()) {
                if($file!="." && $file!="..") {
                  $list[] = $path."/".$file;
                }
            }
        }
        return $list;
    }

    function read($id){
        $file = $this->_getFile($id);
        if(!$file){
            return false;
        }
        return file_get_contents($file);
    }

    function fileExists($id){
        $file = $this->_getFile($id);
        if(!$file){
            return false;
        }
        return true;
    }
    
    function deleteFolder($dir){
        $dir = $this->getPath($dir);

        if($directory = @dir($dir)) {
            while ($file = $directory->read()) {
                if($file!="." && $file!="..") {
                  $fullpath=$dir."/".$file;
                  if(!is_dir($fullpath)) {
                      @unlink($fullpath);
                  } else {
                      $this->deleteFolder($fullpath);
                  }
                }
            }
            $directory->close();
        }else{
            return false;
        }
        if(@rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    function delete($id){
        $file = $this->_getFile($id);
        if(!$file){
            return false;
        }
        return @unlink($file);
    }
    
    function getUrl($id){
        $setting =& Loader::model('setting');
        $url = $setting->get_conf('site.url');
        return $url.$id;
    }

    function getPath($id){
        return ROOTDIR.'/'.$id;
    }

    function _getFile($id){
        if($id && file_exists(ROOTDIR.'/'.$id)){
            return ROOTDIR.'/'.$id;
         }else{
            return false;
        }
    }

}

