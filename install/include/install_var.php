<?php

define('SOFT_NAME', 'MeiuPic');
define('MPIC_VERSION','2.2.0');

$sqlfile = INSDIR.'data/install.sql';
$sqlite_sqlfile = INSDIR.'data/install_sqlite.sql';
$datasqlfile = INSDIR.'data/install_data.sql';
$lockfile = ROOTDIR.'conf/install.lock';
$confile =  ROOTDIR.'conf/config.php';
$dst_dbfile = 'data/meiupic.db';

define('ORIG_TABLEPRE', 'meu_');

define('ENV_CHECK_ERROR', 31);

$func_items = array('copy', 'file_get_contents','fopen');

$env_items = array
(
	'os' => array('c' => 'PHP_OS', 'r' => 'notset', 'b' => 'unix'),
	'php' => array('c' => 'PHP_VERSION', 'r' => '4.3', 'b' => '5.0'),
	'attachmentupload' => array('r' => 'notset', 'b' => '2M'),
	'gdversion' => array('r' => '1.0', 'b' => '2.0'),
	'diskspace' => array('r' => '10M', 'b' => 'notset'),
	'database' => array('r' => '1db','b' => '3db')
);

$dirfile_items = array
(

	'config' => array('type' => 'file', 'path' => './conf/config.php'),
	'config_dir' => array('type' => 'dir', 'path' => './conf'),
	'data' => array('type' => 'dir', 'path' => './data'),
	'cache' => array('type' => 'dir', 'path' => './cache'),
	'cache data' => array('type' => 'dir', 'path' => './cache/data'),
	'template cache' => array('type' => 'dir', 'path' => './cache/templates'),
	'temp dir' => array('type' => 'dir', 'path' => './cache/tmp'),
	'plugindata' => array('type' => 'dir', 'path' => './plugins'),
	'theme dir' => array('type' => 'dir', 'path' => './themes'),
);

$form_db_init_items = array
(
	'mysqldbinfo' => array
	(
		'dbhost' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbhost')),
		'dbname' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbname')),
		'dbport' => array('type'  => 'text', 'required' => 1, 'reg' => '/^[0-9]+$/', 'value' => array('type' => 'var', 'var' => 'dbport')),
		'dbuser' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbuser')),
		'dbpw' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbpw')),
		'tablepre' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*+/', 'value' => array('type' => 'var', 'var' => 'tablepre')),
	),
	'siteinfo' => array
	(
		'siteurl' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'siteurl')),
		'sitename' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/','value' => array('type' => 'var', 'var' => 'sitename')),
	),
	'admininfo' => array
	(
		'username' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'constant', 'var' => 'admin')),
		'password' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'password2' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'email' => array('type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => array('type' => 'var', 'var' => 'adminemail')),
	)
);