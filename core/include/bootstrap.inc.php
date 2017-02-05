<?php
/**
 * $Id: bootstrap.inc.php 404 2012-10-09 07:01:02Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://meiupic.meiu.cn
 * @copyright : (c)2011 meiu.cn lingter@gmail.com
 */
define('IN_MEIU',true);

define('MPIC_VERSION','2.2.0');

header("Content-type: text/html; charset=utf-8");

if (floor(PHP_VERSION) < 5){
    define('PHPVer',4);
}else{
    define('PHPVer',5);
}

/**
 * 开始计时器
 *
 * @param name
 *   计时器的名字
 */
function timer_start($name) {
  global $timers;

  list($usec, $sec) = explode(' ', microtime());
  $timers[$name]['start'] = (float)$usec + (float)$sec;
  $timers[$name]['count'] = isset($timers[$name]['count']) ? ++$timers[$name]['count'] : 1;
}

/**
 * 读取当前所用时间，但是并不停止计数器.
 *
 * @param name
 *   计时器的名字
 * @return
 *   当前消耗时间,单位微秒
 */
function timer_read($name) {
  global $timers;

  if (isset($timers[$name]['start'])) {
    list($usec, $sec) = explode(' ', microtime());
    $stop = (float)$usec + (float)$sec;
    $diff = round(($stop - $timers[$name]['start']) * 1000, 2);

    if (isset($timers[$name]['time'])) {
      $diff += $timers[$name]['time'];
    }
    return $diff;
  }
}

/**
 * 计时器停止.
 *
 * @param name
 *   计时器名字.
 * @return
 *   返回计时器数组.
 */
function timer_stop($name) {
  global $timers;

  $timers[$name]['time'] = timer_read($name);
  unset($timers[$name]['start']);

  return $timers[$name];
}

/**
 * 销毁所有不允许的全局变量
 */
function unset_globals() {
  if (ini_get('register_globals')) {
    $allowed = array('_ENV' => 1,'_SESSION'=>1, '_GET' => 1, '_POST' => 1, '_COOKIE' => 1, '_FILES' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1);
    foreach ($GLOBALS as $key => $value) {
      if (!isset($allowed[$key])) {
        unset($GLOBALS[$key]);
      }
    }
  }
}
/**
 * 载入语言
 */
function lang() {
    global $templatelangs,$base_path;
    $varr = func_get_args();
    $var = array_shift($varr);
    if(isset($GLOBALS['language'][$var])) {
        return vsprintf(str_replace('{base_path}', $base_path, $GLOBALS['language'][$var]),$varr);
    } else {
        $vars = explode(':', $var);
        if(count($vars) != 2) {
            return "!$var!";
        }
        if(!in_array($vars[0], $GLOBALS['templatelangs']) && empty($templatelangs[$vars[0]])) {
            @include ROOTDIR.'plugins'.DIRECTORY_SEPARATOR.$vars[0].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.LANGSET.'.lang.php';
            if(isset($language)){
                $GLOBALS['templatelangs'][$vars[0]] = $language;
            }
        }
        if(!isset($GLOBALS['templatelangs'][$vars[0]][$vars[1]])) {
            return "!$var!";
        } else {
            return vsprintf(str_replace('{base_path}', $base_path,$GLOBALS['templatelangs'][$vars[0]][$vars[1]]),$varr);
        }
    }
    return $var;
}

/**
 * 启动初始化
 *
 */
function boot_init(){
    global $base_url, $base_path, $base_root,$timestamp,$tplrefresh;
    $timestamp = time();
    $tplrefresh = 1;
    
    if (isset($base_url)) {
        $parts = parse_url($base_url);
        if (!isset($parts['path'])) {
          $parts['path'] = '';
        }
        
        if($dir = trim($parts['path'],'\,/')){
            $base_path = '/'.$dir.'/';
        }else{
            $base_path = '/';
        }
        
        $base_root = substr($base_url, 0, strlen($base_url) - strlen($parts['path']));
    }
    else {
        $base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        $base_url = $base_root .= '://'. $_SERVER['HTTP_HOST'];
        $PHP_SELF = htmlspecialchars(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
        $dir = trim( substr($PHP_SELF, 0, strrpos($PHP_SELF, 'index.php')) , '\,/');
        if ($dir) {
          $base_path = "/$dir";
          $base_path .= '/';
          $base_url .= $base_path;
        }else {
          $base_path = '/';
        }
    }
}

function timezone_set($timeoffset = 8) {
    if(function_exists('date_default_timezone_set')) {
        @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
    }
}


function init_defines(){
    $Config =& loader::config();
    if(isset($Config['img_engine']) && in_array($Config['img_engine'],array('imagick','gd'))){
        define('IMG_ENGINE',$Config['img_engine']);
    }else{
        define('IMG_ENGINE','gd');
    }

    if(isset($Config['storage_engine'])){
        define('STORAGE_ENGINE',$Config['storage_engine']);
    }else{
        define('STORAGE_ENGINE','file');
    }
    
    
    $setting =& loader::model('setting');
    define('GRAVATAR_URL',$setting->get_conf('system.gravatar_url'));
    if(!defined('LANGSET'))
        define('LANGSET',$setting->get_conf('system.language','zh_cn'));
    if(!defined('TIMEZONE'))
        define('TIMEZONE',$setting->get_conf('system.timezone',8.00));
    
    timezone_set(TIMEZONE);
}

function init_template($user_theme){
    global $language;
    
    if(isset($_GET['tem'])){
        $meu_theme = $_GET['tem'];
        setcookie('MPIC_THEME',$meu_theme,0,'/');
    }else{
        $meu_theme = isset($_COOKIE['MPIC_THEME'])?$_COOKIE['MPIC_THEME']:'';
    }

    if($user_theme && file_exists('themes/'.$meu_theme)){
        define('TEMPLATEID', $user_theme);
    }elseif($meu_theme && file_exists('themes/'.$meu_theme)){
        define('TEMPLATEID', $meu_theme);
    }else{
        $setting_mdl =& loader::model('setting');
        $current_theme = $setting_mdl->get_conf('system.current_theme','default');
        define('TEMPLATEID', $current_theme);
    }
    define('TPLDIR','themes/'.TEMPLATEID);
    if(file_exists(ROOTDIR.TPLDIR.'/lang/'.LANGSET.'.lang.php')){
        include_once(ROOTDIR.TPLDIR.'/lang/'.LANGSET.'.lang.php');
    }
}

function meiu_bootstrap(){
    unset_globals();

    global $base_url, $base_path, $base_root, $language,$templatelangs;
    timer_start('page');
    require_once(COREDIR.'loader.php');
    require_once(INCDIR.'functions.php');
    include_once(INCDIR.'plugin.php');

    init_defines();
    if(file_exists(COREDIR.'lang'.DIRECTORY_SEPARATOR.LANGSET.'.lang.php')){
        require_once(COREDIR.'lang'.DIRECTORY_SEPARATOR.LANGSET.'.lang.php');
    }
    boot_init();
    $plugin =& loader::lib('plugin');
    
    $Config =& loader::config();
    if(!$Config['safemode']){
        $plugin->init_plugins();
    }
    $plugin->trigger('boot_init');

    //输出对象
    $output =& loader::lib('output');
    //载入当前用户信息
    $user =& loader::model('user');
    $output->set('loggedin',$user->loggedin());
    if($user->loggedin()){
        $output->set('u_info',$user->get_all_field());
        $user_extrainfo = $user->get_extra($user->get_field('id'));
        $output->set('u_extrainfo',$user_extrainfo);
        $user_theme = isset($user_extrainfo['theme'])?$user_extrainfo['theme']:'';
    }else{
        $user_theme = null;
    }

    init_template($user_theme);
    $templatelangs=array();
    
    $uri =& loader::lib('uri');
    $uriinfo = $uri->parse_uri();
    
    $setting_mdl =& loader::model('setting');

    //如果数据库中的版本和程序版本不一致则跳转到执行自动升级脚本
    $version = $setting_mdl->get_conf('system.version');
    if($version != MPIC_VERSION){
        if(file_exists(ROOTDIR.'install/upgrade.php')){
            include(ROOTDIR.'install/upgrade.php');
            exit;
        }
    }

    $output->set('base_path',$base_path);
    $output->set('statics_path',$base_path.'statics/');
    $output->set('site_logo',$setting_mdl->get_conf('site.logo',''));
    $output->set('site_name',$setting_mdl->get_conf('site.title',lang('myalbum')));
    
    $uriinfo['ctl'] = preg_replace('/[^a-z0-9\-_]/is', '', $uriinfo['ctl']);
    $uriinfo['act'] = preg_replace('/[^a-z0-9\-_]/is', '', $uriinfo['act']);
    
    define('IN_CTL',$uriinfo['ctl']);
    define('IN_ACT',$uriinfo['act']);
    $_GET = array_merge($_GET,$uriinfo['pars']);
    $_REQUEST = array_merge($_REQUEST,$uriinfo['pars']);
    
    require_once(INCDIR.'pagecore.php');

    if($plugin->has_trigger('custom_page.'.IN_CTL.'.'.IN_ACT) || $plugin->has_trigger('custom_page.'.IN_CTL)){
        $plugin->trigger('custom_page.'.IN_CTL.'.'.IN_ACT) || $plugin->trigger('custom_page.'.IN_CTL,IN_ACT);
    }else{
        if(file_exists(CTLDIR.$uriinfo['ctl'].'.ctl.php')){
            require_once(CTLDIR.$uriinfo['ctl'].'.ctl.php');

            $controller_name = $uriinfo['ctl'].'_ctl';
            $controller = new $controller_name();
            $controller->_init();
            if(method_exists($controller,$uriinfo['act'])){
                call_user_func(array($controller,$uriinfo['act']));
            }else{
                header("HTTP/1.1 404 Not Found");
                showError(lang('404_not_found'));
            }
            $controller->_called();
        }else{
            header("HTTP/1.1 404 Not Found");
            showError(lang('404_not_found'));
        }
    }
}
?>