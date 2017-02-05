<?php

include_once('pclzip.lib.php');
class zip_cla extends PclZip{   
    
    function zip_cla(){
        parent::PclZip('');
    }

    function load_file($file){
        $this->zipname = $file;
    }

}