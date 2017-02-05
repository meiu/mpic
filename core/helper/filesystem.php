<?php

if(!function_exists('file_put_contents')) {
    function file_put_contents($filename, $s) {
        $fp = @fopen($filename, 'w');
        @fwrite($fp, $s);
        @fclose($fp);
        return TRUE;
    }
}
//获取文件的真实路径
function get_realpath($path){
    //try to remove any relative paths
    $remove_relatives = '/\w+\/\.\.\//';
    while(preg_match($remove_relatives,$path)){
        $path = preg_replace($remove_relatives, '', $path);
    }
    //if any remain use PHP realpath to strip them out, otherwise return $path
    //if using realpath, any symlinks will also be resolved
    return preg_match('#^\.\./|/\.\./#', $path) ? realpath($path) : $path;
}
function file_base($file){
    $arr = explode('/',$file);
    $tmp = end($arr);
    $arr = explode('\\',$tmp);
    return end($arr);
}
//文件后缀
function file_ext($filename){
    $farr = explode('.',$filename);
    $tmp = end($farr);
    return strtolower($tmp);
}
//文件除去后缀的文件名
function file_pure_name($filename){
    $arr = explode('.',$filename);
    array_pop($arr);
    return implode('.',$arr);
}
//判断是否是纯英文的名字
function file_en_name($filepure){
    return preg_match('/^[0-9a-z_\-\.\(\)~\[\]]+$/i', $filepure);
}
//删除文件夹
function deldir($dir) {
    if($directory = @dir($dir)) {
        while ($file = $directory->read()) {
            if($file!="." && $file!="..") {
              $fullpath=$dir."/".$file;
              if(!is_dir($fullpath)) {
                  @unlink($fullpath);
              } else {
                  deldir($fullpath);
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

function filelist($dir,& $list){
    if($directory = @dir($dir)) {
        while ($file = $directory->read()) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    $list[] = $fullpath;
                }else{
                    filelist($fullpath,$list);
                }
            }
        }
        return true;
    }
    return false;
}

function getdirlist($dir){
    $dirs = array();
    if($directory = @dir($dir)) {
        while ($file = $directory->read()) {
            if($file!="." && $file!="..") {
              $fullpath=$dir."/".$file;
              if(!is_dir($fullpath)) {
                $dirs[$dir][] = $fullpath;
              } else {
                $list = array();
                if(filelist($fullpath,$list))
                    $dirs[$fullpath] = $list;
              }
            }
        }
        $directory->close();
        return $dirs;
    }else{
        return false;
    }
}
//文件夹占用空间
function dirsize($dir) {
    @$dh = opendir($dir);
    $size = 0;
    while ($file = @readdir($dh)) {
        if ($file != "." and $file != "..") {
            $path = $dir."/".$file;
            if (is_dir($path)) {
                $size += dirsize($path);
            } elseif (is_file($path)) {
                $size += filesize($path);
            }
        }
    }
    @closedir($dh);
    return $size;
}
//清除文件夹内文件
function dir_clear($dir) {
    if($directory = @dir($dir)) {
        while($entry = $directory->read()) {
            $filename = $dir.'/'.$entry;
            if(is_file($filename)) {
                @unlink($filename);
            }
        }
        $directory->close();
        @touch($dir.'/index.htm');
    }
}

function dir_writeable($dir) {
    $writeable = 0;
    if(!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    if(is_dir($dir)) {
        if($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }
    return $writeable;
}