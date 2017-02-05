<?php
/**
 * $Id:index.php 93 2010-09-07 15:35:32Z lingter $
 * 
 * @author : Lingter
 * @support : http://meiupic.meiu.cn
 * @copyright : (c)2011 meiu.cn lingter@gmail.com
 */
error_reporting(E_ALL & ~E_NOTICE);
define('CHECK_UPDATE_URL','http://meiupic.meiu.cn/check_update.php');

@ini_set('memory_limit', '128M');

define('FCPATH',__FILE__);
define('ROOTDIR',dirname(FCPATH).DIRECTORY_SEPARATOR);
define('COREDIR',ROOTDIR.'core'.DIRECTORY_SEPARATOR);
define('LIBDIR',COREDIR.'libs'.DIRECTORY_SEPARATOR);
define('INCDIR',COREDIR.'include'.DIRECTORY_SEPARATOR);
define('CTLDIR',COREDIR.'ctls'.DIRECTORY_SEPARATOR);
define('MODELDIR',COREDIR.'models'.DIRECTORY_SEPARATOR);
define('DATADIR',ROOTDIR.'data'.DIRECTORY_SEPARATOR);
define('PLUGINDIR',ROOTDIR.'plugins'.DIRECTORY_SEPARATOR);
define('MAGIC_GPC',get_magic_quotes_gpc());

if(!file_exists(ROOTDIR.'conf'.DIRECTORY_SEPARATOR.'config.php')){
    header('Location: ./install/');
    exit;
}

require_once(INCDIR.'bootstrap.inc.php');
meiu_bootstrap();