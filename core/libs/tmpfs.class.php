<?php
/**
 * $Id: tmpfs.class.php 275 2011-11-24 14:25:47Z lingter@gmail.com $
 * 
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class tmpfs_cla{

    function tmpfs_cla(){
        $this->targetDir = ROOTDIR.'cache'.DIRECTORY_SEPARATOR.'tmp';
    }

    function get_path($fileName){
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);
        return $this->targetDir . DIRECTORY_SEPARATOR . $fileName;
    }

    function write($fileName,$content,$append=false,$fullPath=false){
        if (!file_exists($this->targetDir))
            @mkdir($this->targetDir);
        
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->targetDir . DIRECTORY_SEPARATOR . $fileName;
        }
        if($append){
            return file_put_contents($filePath,$content,FILE_APPEND);
        }
        return file_put_contents($filePath,$content);
    }

    function read($fileName,$fullPath=false){
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->targetDir . DIRECTORY_SEPARATOR . $fileName;
        }
        return file_get_contents($filePath);
    }

    function delete($fileName,$fullPath=false){
        if($fullPath){
             $filePath = $fileName;
        }else{
            $filePath = $this->targetDir . DIRECTORY_SEPARATOR . $fileName;
        }
        return @unlink($filePath);
    }

    function upload($fileName,$append=false,$fullPath=false){ 
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        if (!file_exists($this->targetDir))
            @mkdir($this->targetDir);
            
        if($fullPath){
            $filePath = $fileName;
        }else{
            $filePath = $this->targetDir . DIRECTORY_SEPARATOR . $fileName;
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];
        
        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($filePath, !$append ? "wb" : "ab");
                if ($out) {
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        return 101;
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    return 102;
            } else
                return 0;
        }else{
            $out = @fopen($filePath, !$append ? "wb" : "ab");
            if ($out) {
                $in = @fopen("php://input", "rb");
                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    return 101;
                fclose($in);
                fclose($out);
            } else{
                return 102;
            }
            return 0;
        }
    }
}