<?php

//create link
function site_link($ctl='default',$act='index',$pars=array()){
    $uri =& loader::lib('uri');
    return $uri->mk_uri($ctl,$act,$pars);
}
//跳转，默认header跳转，
function redirect($url,$time=0,$msg=''){
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg  =  "This page will redirect to {$url} in {$time} seconds！";
    
    if (!headers_sent()) {
        if(0===$time) {
            header("Location: ".$url);
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}

/*
type:
    page: 页面
    ajax_page: ajax 页面
    ajax: 返回ajax
    ajax_box
    ajax_bubble
*/
function need_login($type = 'page'){
    $user_mdl =& loader::model('user');
    if(!$user_mdl->loggedin()){
        switch($type){
            case 'page':
                showError(lang('not_authorized'));
                break;
            case 'ajax_page':
                ajax_box(lang('not_authorized'));
                break;
            case 'ajax_inline':
                echo '<form><div class="form_notice_div">'.lang('not_authorized').'</div><input type="button" name="cancel" class="graysmlbtn" value="'.lang('cancel').'" /></form>';
                break;
            case 'ajax':
                form_ajax_failed('text',lang('not_authorized'));
                break;
            case 'ajax_box':
                form_ajax_failed('box',lang('not_authorized'));
                break;
            case 'ajax_bubble':
                echo '<div class="close png" onclick="Mui.bubble.close()">X</div>'.lang('not_authorized');
                break;
        }
        exit;
    }
}
//发送禁止缓存的header
function no_cache_header(){
    //header("Content-Type:text/html;charset=utf-8");
    header("Expires: Thu, 01 Jan 1970 00:00:01 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
}
//获取相册的封面的地址
/*function get_album_cover($aid,$ext){
    return 'data/cover/'.$aid.'.'.$ext;
}*/
//获取用户头像url
function get_avatar($comment){
    $gravatar_url = GRAVATAR_URL;
    $id = md5(strtolower($comment['email']));
    $url = str_replace('{idstring}',$id,$gravatar_url);
    
    return $url;
}
//获取图片地址
function img_path($path){
    $storlib =& loader::lib('storage');
    $fullpath = $storlib->getUrl($path);
    $plugin =& loader::lib('plugin');
    return $plugin->filter('photo_path',$fullpath,$path);
}
//获取加密后的图片地址
function encrypt_imgpath($params){
    global $base_path;
    $string = serialize($params);
    $config =& loader::config();
    return $base_path.'img.php?'.mycrypt($string,$config['img_path_key'],'EN');
}

//用户真实的IP地址
function get_real_ip(){
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

/*
flag: 1, new files added
      2, clear trash
*/
function trash_status($flag){
    $cache =& loader::lib('cache');
    if($flag == 1){
        $cache->set('trash.tmp',1);
    }else{
        $cache->set('trash.tmp',0);
    }
}
//回收站是否为空
function has_trash(){
    $cache =& loader::lib('cache');
    if($cache->get('trash.tmp') == 1){
        return true;
    }
    return false;
}

function showError($error_msg){
    $output =& loader::lib('output');
    $output->set('error_msg',$error_msg);
    loader::view('block/showerror');
    exit;
}

function get_fonts(){
    $fontdir = ROOTDIR.'statics/font';
    $fonts = array();
    if($directory = @dir($fontdir)) {
        while($entry = $directory->read()) {
            $fileext = file_ext($entry);
            if(strtolower($fileext) == 'ttf' || strtolower($fileext) == 'ttc'){
                $fonts[] = $entry;
            }
        }
        $directory->close();
    }
    return $fonts;
}

function save_config_file($filename, $config, $default) {
    $config = setdefault($config, $default);
    $date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
    $content = <<<EOT
<?php


\$CONFIG = array();

EOT;
    $content .= getvars(array('CONFIG' => $config));
    $content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
    
    file_put_contents($filename, $content);
}

function setdefault($var, $default) {
    foreach ($default as $k => $v) {
        if(!isset($var[$k])) {
            $var[$k] = $default[$k];
        } elseif(is_array($v)) {
            $var[$k] = setdefault($var[$k], $default[$k]);
        }
    }
    return $var;
}

function getvars($data, $type = 'VAR') {
    $evaluate = '';
    foreach($data as $key => $val) {
        if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
            continue;
        }
        if(is_array($val)) {
            $evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
        } else {
            $val = addcslashes($val, '\'\\');
            $evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
        }
    }
    return $evaluate;
}

function get_config($str){
    $setting =& loader::model('setting');
    return $setting->get_conf($str);
}

function buildarray($array, $level = 0, $pre = '$CONFIG') {
    static $ks;
    $return = '';
    if($level == 0) {
        $ks = array();
    }

    foreach ($array as $key => $val) {
        if($level == 0) {
            $newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
            $return .= "\r\n// $newline //\r\n";
        }
        
        $ks_par = isset($ks[$level - 1])?$ks[$level - 1]:'';
        $ks[$level] = $ks_par."['$key']";
        if(is_array($val)) {
            $return .= buildarray($val, $level + 1, $pre);
        } else {
            $val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
            $return .= $pre.$ks_par."['$key']"." = $val;\r\n";
        }
    }
    return $return;
}


function detect_thumb($w,$h,$square){
    if($w>$h){
        $height = $square;
        $width = $w * $square/$h;
        $left = ($width-$height)/2;
        return 'height:'.intval($height).'px;left:-'.intval($left).'px';
    }else{
        $width = $square;
        $height = $h * $square/$w;
        $top = ($height-$width)/2;
        return 'width:'.intval($width).'px;top:-'.intval($top).'px';
    }
}


function check_update(){
    $software = 'meiupic';
    $version = MPIC_VERSION;
    $langset = LANGSET;
    $time = time();
    $hash = md5("{$software}{$version}{$langset}{$time}");
    $q = base64_encode("software=$software&version=$version&langset=$langset&time=$time&hash=$hash");
    
    if(!defined('CHECK_UPDATE_URL') || !CHECK_UPDATE_URL){
        return false;
    }

    $url = CHECK_UPDATE_URL.'?act=check&q='.$q;
    $response = get_remote($url,2);
    if(!$response){
        return false;
    }else{
        $json =& loader::lib('json');
        $result = $json->decode($response);
        $setting_mdl =& loader::model('setting');
        if(isset($result['return'])){
            $setting_mdl->set_conf('update.return',$result['return']);
        }
        if(isset($result['package'])){
            $setting_mdl->set_conf('update.package',$result['package']);
        }
        if(isset($result['version'])){
            $setting_mdl->set_conf('update.version',$result['version']);
        }
        if(isset($result['pubdate'])){
            $setting_mdl->set_conf('update.pubdate',$result['pubdate']);
        }
        return $result;
    }
}

/**
 * 认证加密
 *
 * @param String $string
 * @param String $operation
 * @param String $key
 * @param Int $expiry
 * @return String
 */
 function authcode($string, $operation = 'DECODE', $key, $expiry = 0) {
     $ckey_length = 4;
     $key = md5($key);
     $keya = md5(substr($key, 0, 16));
     $keyb = md5(substr($key, 16, 16));
     $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

     $cryptkey = $keya.md5($keya.$keyc);
     $key_length = strlen($cryptkey);

     $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
     $string_length = strlen($string);

     $result = '';
     $box = range(0, 255);

     $rndkey = array();
     for($i = 0; $i <= 255; $i++) {
         $rndkey[$i] = ord($cryptkey[$i % $key_length]);
     }

     for($j = $i = 0; $i < 256; $i++) {
         $j = ($j + $box[$i] + $rndkey[$i]) % 256;
         $tmp = $box[$i];
         $box[$i] = $box[$j];
         $box[$j] = $tmp;
     }

     for($a = $j = $i = 0; $i < $string_length; $i++) {
         $a = ($a + 1) % 256;
         $j = ($j + $box[$a]) % 256;
         $tmp = $box[$a];
         $box[$a] = $box[$j];
         $box[$j] = $tmp;
         $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
     }

     if($operation == 'DECODE') {
         if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
             return substr($result, 26);
         } else {
             return '';
         }
     } else {
         return $keyc.str_replace('=', '', base64_encode($result));
     }

 }