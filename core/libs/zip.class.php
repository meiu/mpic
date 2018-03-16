<?php

include_once('pclzip.lib.php');
class zip_cla extends PclZip{   
    
    function __construct(){
        parent::PclZip('');
    }

    function load_file($file){
        $this->zipname = $file;
    }

}