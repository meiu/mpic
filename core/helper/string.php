<?php
//简单加密解密函数
function mycrypt($string,$seccode,$action="EN")
{ 
    $secret_string = $seccode;
    if($string=="") return ""; 
    if($action=="EN") $md5code=substr(md5($string),8,10);
    else
    { 
        $md5code=substr($string,-10);
        $string=substr($string,0,strlen($string)-10);
    } 
    
    $key = md5($md5code.$secret_string);
    $string = ($action=="EN"?$string:base64_decode($string));
    $len = strlen($key); 
    $code = ""; 
    for($i=0; $i<strlen($string); $i++)
    { 
        $k = $i%$len; 
        $code .= $string[$i]^$key[$k]; 
    } 
    $code = ($action == "DE" ? (substr(md5($code),8,10)==$md5code?$code:NULL) : base64_encode($code)."$md5code"); 
    return $code; 
}

//相册权限类型对应的名称
function enum_priv_type($v){
    switch($v){
        case 0:
        return lang('album_type_public');
        case 1:
        return lang('album_type_passwd');
        case 2:
        return lang('album_type_ques');
        case 3:
        return lang('album_type_private');
    }
}

//数组中的字符串加 "\"
function arr_addslashes($arr){
    if(is_array($arr)){
        return array_map('arr_addslashes',$arr);
    }else{
        return addslashes($arr);
    }
}
//数组中的字符串去除 "\"
function arr_stripslashes($arr){
    if(is_array($arr)){
        return array_map('arr_stripslashes',$arr);
    }else{
        return stripslashes($arr);
    }
}
//标签处理，用空格或,分割返回数组
function parse_tag($str){
    $tag_arr_tmp = explode(' ',$str);
    $tag_arr =array();
    foreach($tag_arr_tmp as $tv){
        $arr = explode(',',$tv);
        $tag_arr = array_merge($tag_arr,$arr);
    }
    return $tag_arr;
}

//Words Filter
function safe_convert($string, $html=false, $filterslash=false) {
    $string = stripslashes(trim($string));
    if (!$html) {
        $string=htmlspecialchars($string, ENT_QUOTES);
        $string=str_replace("<","&lt;",$string);
        $string=str_replace(">","&gt;",$string);
        if ($filterslash) $string=str_replace("\\", '&#92;', $string);
        
        $string=str_replace('|', '&#124;', $string);
        $string=str_replace("&amp;#96;","&#96;",$string);
        $string=str_replace("&amp;#92;","&#92;",$string);
        $string=str_replace("&amp;#91;","&#91;",$string);
        $string=str_replace("&amp;#93;","&#93;",$string);
    } else {
        //$string=addslashes($string);
        if ($filterslash) $string=str_replace("\\", '&#92;', $string);
    }
    $string=str_replace("\r","",$string);
    $string=str_replace("\n","<br />",$string);
    $string=str_replace("\t","&nbsp;&nbsp;",$string);
    $string=str_replace("  ","&nbsp;&nbsp;",$string);
    $string=preg_replace('/[a-zA-Z](&nbsp;)[a-zA-Z]/i',' ',$string);
    return $string;
}
//Transfer the converted words into editable characters
function safe_invert($string, $html=false) {
    $string = str_replace(array("<br />",'<br/>','<br>'),"\n",$string);
    if ($html) {        
        $string = str_replace("<br/>","\n",$string);
        $string = str_replace("&nbsp;"," ",$string);
        //$string = str_replace("&","&amp;",$string);
    }
    $string = str_replace("&nbsp;"," ",$string);
    return $string;
}


//covert bytes to be readable
function bytes2u($size){
    $result = '';
    if($size < 1024)
    {
      $result = round($size, 2).' B';
    }
    elseif($size < 1024*1024)
    {
      $result = round($size/1024, 2).' KB';
    }
    elseif($size < 1024*1024*1024)
    {
      $result = round($size/1024/1024, 2).' MB';
    }
    elseif($size < 1024*1024*1024*1024)
    {
      $result = round($size/1024/1024/1024, 2).' GB';
    }
    else
    {
      $result = round($size/1024/1024/1024/1024, 2).' TB';
    }
    return $result;
}
//允许上传的大小，转换成字节数
function allowsize($size){
    switch($size){
        case '512K':
            $byte = 512*1024;
            break;
        case '1M':
            $byte = 1024*1024;
            break;
        case '2M':
            $byte = 2*1024*1024;
            break;
        case '5M':
            $byte = 5*1024*1024;
            break;
        default:
            $byte = false;
    }
    return $byte;
}

//切割字符串
function mycutstr($string, $length, $dot = ' ...', $charset = 'utf-8') {
    if(strlen($string) <= $length) {
        return $string;
    }
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    if(strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while($n < strlen($string)) {
            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) {
                break;
            }
        }
        if($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    return $strcut.$dot;
}