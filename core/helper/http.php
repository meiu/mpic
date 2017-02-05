<?php

function isPost(){
    if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
        return true;
    }
    return false;
}

function getGet($key,$default=''){
    if(isset($_GET[$key])){
        if(!MAGIC_GPC)
        {
            return arr_addslashes($_GET[$key]);
        }
        return $_GET[$key];
    }
    return $default;
}

function getPost($key,$default=''){
    if(isset($_POST[$key])){
        if(!MAGIC_GPC)
        {
            return arr_addslashes($_POST[$key]);
        }
        return $_POST[$key];
    }
    return $default;
}

function getRequest($key,$default=''){
    if(isset($_REQUEST[$key])){
        if(!MAGIC_GPC)
        {
            return arr_addslashes($_REQUEST[$key]);
        }
        return $_REQUEST[$key];
    }
    return $default;
}

function getPosts(){
    if(!MAGIC_GPC)
    {
        return arr_addslashes($_POST);
    }
    return $_POST;
}

function getRequests(){
    if(!MAGIC_GPC)
    {
        return arr_addslashes($_REQUEST);
    }
    return $_REQUEST;
}

function getGets(){
    if(!MAGIC_GPC)
    {
        return arr_addslashes($_GET);
    }
    return $_GET;
}

function get_remote($url,$timeout = 15, $limit = 0, $post = '', $cookie = '', $ip = '',$refer='',  $block = TRUE){
    if(function_exists('fsockopen') || function_exists('pfsockopen')){
        return socket_get_content($url, $timeout , $limit , $post , $cookie , $ip,$refer,  $block);
    }else{
        $ctx = null;
        if($timeout>0){
            if(function_exists('stream_context_create')){
                if($post){
                    $data = http_build_query($post, '', '&');
                    $par = array(
                        'http' => array(
                            'method'=>'POST',
                            'timeout'=>$timeout,
                            'header'=>"Content-Type: application/x-www-form-urlencoded\r\n".
                            "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n".
                            "Content-Length: " . strlen($data) . "\r\n".
                            "Cookie: $cookie\r\n",
                            "Referer: $refer\r\n",
                            'content' => $data,
                        )
                    );
                }else{
                    $par = array(
                        'http' => array(
                            'method'=>'GET',
                            'timeout'=>$timeout,
                            'header'=>"Content-Type: application/x-www-form-urlencoded\r\n".
                            "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n".
                            "Cookie: $cookie\r\n",
                            "Referer: $refer\r\n",
                        )
                    );
                }
                $ctx = stream_context_create($par);
            }
        }
        $result = @file_get_contents($url,false,$ctx);
        return $result;
    }
}

function socket_get_content($url, $timeout = 15, $limit = 0, $post = '', $cookie = '', $ip = '',$refer = '',  $block = TRUE) {
    $return = '';
    $matches = parse_url($url);
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].(isset($matches['query']) && $matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if($post) {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Referer: $refer\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    } else {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Referer: $refer\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
//exit($out);
    if(function_exists('fsockopen')) {
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    } elseif (function_exists('pfsockopen')) {
        $fp = @pfsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    } else {
        $fp = false;
    }

    if(!$fp) {
        return '';
    } else {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if(!$status['timed_out']) {
            while (!feof($fp)) {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                    break;
                }
            }

            $stop = false;
            while(!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}