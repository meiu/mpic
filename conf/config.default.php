<?php

$CONFIG['database']['default'] = array(
    'adapter'  => 'mysql',
    'host'     => 'localhost',
    'port'     => '3306',
    'dbuser'   => 'root',
    'dbpass'   => '',
    'dbname'   => 'meiupic',
    'pconnect' => false,
    'charset'  => 'utf8',
    'pre'      => 'meu_'
);
/*
$CONFIG['database']['default'] = array(
    'adapter'  => 'sqlite',
    'dbname'   => 'data/meiupic.sqlite',
    'pre'      => 'meu_'
);*/

//缓存
$CONFIG['cache_engine'] = 'file';
$CONFIG['cache_policy'] = array('life_time' => 900);

$CONFIG['storage_engine'] = 'file';//file 
$CONFIG['img_engine'] = 'imagick';//imagick or gd

$CONFIG['cookie_name'] = 'MPIC_AU';
$CONFIG['cookie_auth_key'] = 'eijs21aa0332$jsdf23';
//默认请留空
$CONFIG['cookie_domain'] = '';
$CONFIG['img_path_key'] = 'sdfasdf23424aa';

//安全模式
$CONFIG['safemode'] = false;
?>