<?php

if(!function_exists('file_put_contents')) {
    function file_put_contents($filename, $s) {
        $fp = @fopen($filename, 'w');
        @fwrite($fp, $s);
        @fclose($fp);
        return TRUE;
    }
}
function random($length) {
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}
function g($a){
    return isset($_GET[$a])?$_GET[$a]:'';
}
function r($a){
    return isset($_REQUEST[$a])?$_REQUEST[$a]:'';
}
function p($a){
    return isset($_POST[$a])?$_POST[$a]:'';
}

function timezone_set($timeoffset = 8) {
    if(function_exists('date_default_timezone_set')) {
        @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
    }
}

function show_header() {
    define('SHOW_HEADER', TRUE);
    global $step;
    $version = MPIC_VERSION;
    $install_lang = lang('lang_name');
    $title = lang('title_install');
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>$title</title>
<link rel="stylesheet" href="img/style.css" type="text/css" media="all" />
<script type="text/javascript">
    function $(id) {
        return document.getElementById(id);
    }

    function showmessage(message) {
        document.getElementById('notice').innerHTML += message + '<br />';
    }
</script>
<meta content="Meiu Studio" name="Copyright" />
</head>
<div class="container">
    <div class="header">
        <h1>$title</h1>
        <span>MeiuPic $version $install_lang</span>
EOT;

    $step > 0 && show_step($step);
}

function show_footer($quit = true) {

    echo <<<EOT
        <div class="footer">&copy;2010 - 2012 <a href="http://www.meiu.cn/">Meiu Studio</a></div>
    </div>
</div>
</body>
</html>
EOT;
    $quit && exit();
}


function show_step($step) {

    global $method;

    $laststep = 4;
    $title = lang('step_'.$method.'_title');
    $comment = lang('step_'.$method.'_desc');

    $stepclass = array();
    for($i = 1; $i <= $laststep; $i++) {
        $stepclass[$i] = $i == $step ? 'current' : ($i < $step ? '' : 'unactivated');
    }
    $stepclass[$laststep] .= ' last';

    echo <<<EOT
    <div class="setup step{$step}">
        <h2>$title</h2>
        <p>$comment</p>
    </div>
    <div class="stepstat">
        <ul>
            <li class="$stepclass[1]">1</li>
            <li class="$stepclass[2]">2</li>
            <li class="$stepclass[3]">3</li>
            <li class="$stepclass[4]">4</li>
        </ul>
        <div class="stepstatbg stepstat1"></div>
    </div>
</div>
<div class="main">
EOT;

}

function show_msg($error_no, $error_msg = 'ok', $success = 1, $quit = TRUE) {

    show_header();
    global $step;

    $title = lang($error_no);
    $comment = lang($error_no.'_comment', false);
    $errormsg = '';

    if($error_msg) {
        if(!empty($error_msg)) {
            foreach ((array)$error_msg as $k => $v) {
                if(is_numeric($k)) {
                    $comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
                }
            }
        }
    }

    if($step > 0) {
        echo "<div class=\"desc\"><b>$title</b><ul>$comment</ul>";
    } else {
        echo "</div><div class=\"main\" style=\"margin-top: -123px;\"><b>$title</b><ul style=\"line-height: 200%; margin-left: 30px;\">$comment</ul>";
    }

    if($quit) {
        echo '<br /><span class="red">'.lang('error_quit_msg').'</span><br /><br /><br />';
    }

    echo '<input type="button" onclick="history.back()" value="'.lang('click_to_back').'" /><br /><br /><br />';

    echo '</div>';

    $quit && show_footer();
}


function lang($str,$force=false){
    global $lang;
    if(isset($lang[$str])){
        return $lang[$str];
    }else{
        //return $str;/*
        return $force?$str:'';
    }
}

function showjsmessage($message) {
    echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
    flush();
    ob_flush();
}

function dirfile_check(&$dirfile_items) {
    foreach($dirfile_items as $key => $item) {
        $item_path = $item['path'];
        if($item['type'] == 'dir') {
            if(!dir_writeable(ROOTDIR.$item_path)) {
                if(is_dir(ROOTDIR.$item_path)) {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nodir';
                }
            } else {
                $dirfile_items[$key]['status'] = 1;
                $dirfile_items[$key]['current'] = '+r+w';
            }
        } else {
            if(file_exists(ROOTDIR.$item_path)) {
                if(is_writable(ROOTDIR.$item_path)) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                } else {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                }
            } else {
                if(dir_writeable(dirname(ROOTDIR.$item_path))) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nofile';
                }
            }
        }
    }
}

function env_check(&$env_items) {
    foreach($env_items as $key => $item) {
        if($key == 'php') {
            $env_items[$key]['current'] = PHP_VERSION;
        } elseif($key == 'attachmentupload') {
            $env_items[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
        } elseif($key == 'gdversion') {
            $tmp = function_exists('gd_info') ? gd_info() : array();
            $env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
            unset($tmp);
        } elseif($key == 'diskspace') {
            if(function_exists('disk_free_space')) {
                $env_items[$key]['current'] = floor(disk_free_space(ROOTDIR) / (1024*1024)).'M';
            } else {
                $env_items[$key]['current'] = 'unknow';
            }
        } elseif($key == 'database'){
            $database_support = 0;
            if(function_exists('mysql_connect') || function_exists('mysqli_connect')){
                $database_support += 1;
            }
            if(function_exists('sqlite_open') || class_exists("SQLite3") || (function_exists('pdo_drivers') && in_array('sqlite',pdo_drivers()))){
                $database_support += 2;
            }
            
            $env_items[$key]['current'] = $database_support.'db';
            
        } elseif(isset($item['c'])) {
            $env_items[$key]['current'] = constant($item['c']);
        }

        $env_items[$key]['status'] = 1;
        if($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
            $env_items[$key]['status'] = 0;
        }
        //没GD库不允许继续安装
        if($env_items[$key]['current'] == 'noext'){
            $env_items[$key]['status'] = 0;
        }
    }
}

function createtable($sql) {
    global $db;
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
    ($db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=utf8" : " TYPE=$type");
}

function runquery($sql) {
    global $lang, $tablepre, $db;

    if(!isset($sql) || empty($sql)) return;

    $sql = str_replace("\r", "\n", str_replace(' '.ORIG_TABLEPRE, ' '.$tablepre, $sql));
    $sql = str_replace("\r", "\n", str_replace(' `'.ORIG_TABLEPRE, ' `'.$tablepre, $sql));
    $ret = array();
    $num = 0;
    foreach(explode(";\n", trim($sql)) as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        foreach($queries as $query) {
            $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
        }
        $num++;
    }
    unset($sql);

    foreach($ret as $query) {
        $query = trim($query);
        if($query) {
            if(substr($query, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/CREATE TABLE `?([a-z0-9_]+)`? .*/is", "\\1", $query);
                if($db->adapter=='sqlite'){
                    $db->query($query);
                }else{
                    $db->query(createtable($query));
                }
                showjsmessage(lang('create_table').' '.$name.' ... '.lang('succeed'));
            } else {
                $db->query($query);
            }

        }
    }
}


function getstatinfo($params=array()) {
    $version = MPIC_VERSION;
    $onlineip = get_real_ip();
    $funcurl = 'http://meiupic'.'.mei'.'u'.'.c'.'n/stats_in.php';
    $PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
    $url = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))));
    $url = substr($url, 0, -8);
    $hash = md5("{$url}{$version}{$onlineip}");
    $q = "url=$url&version=$version&ip=$onlineip&time=".time()."&hash=$hash";
    foreach ($params as $key => $value) {
        $q .= '&'.$key.'='.$value;
    }
    $q=rawurlencode(base64_encode($q));
    get_remote($funcurl."?action=newinstall&q=$q",5);
}

function cleardir($dir) {
    global $lang;
    showjsmessage(lang('clear_dir').' '.str_replace(ROOTDIR, '', $dir));
    dir_clear($dir);
}

function get_languages(){
    $lang_dir = COREDIR.'lang';
    $languages = array();
    if($directory = @dir($lang_dir)) {
        while($entry = $directory->read()) {
            $language = null;
            if(preg_match('/^([A-Za-z0-9\_\-]+)\.lang\.php$/is',$entry,$matches)){
                 $file = $lang_dir.'/'.$entry;
                 $name = $matches[1];
                 @include($file);
                 $languages[] = array('name'=>$name ,'lang_name' => isset($language['lang_name'])?$language['lang_name']:$name);
            }
        }
    }
    return $languages;
}

function show_license(){
    global $self, $step;
    $next = $step + 1;

    show_header();
    $license = str_replace('  ', '&nbsp; ', lang('license'));
    $lang_agreement_yes = lang('agreement_yes');
    $lang_agreement_no = lang('agreement_no');
    $langs = get_languages();
    $lang_select = '<select name="language" onchange="if(this.value) window.location.href=\'?lang=\'+this.value;"><option value=\'\'>Please Select</option>';
    foreach($langs as $v){
        $lang_select .='<option value="'.$v['name'].'" '.(INS_LANG==$v['name']?'selected="selected"':'').'>'.$v['lang_name'].'</option>';
    }
    $lang_select .= '</select>';
    
    echo <<<EOT
</div>
<div class="main" style="margin-top:-123px;">
    <div style="margin:10px 0;">Select the installation language: $lang_select</div>
    <div class="licenseblock">$license</div>
    <div class="btnbox marginbot">
        <form method="get" autocomplete="off" action="index.php">
        <input type="hidden" name="step" value="$next">
        <input type="submit" value="{$lang_agreement_yes}" style="padding: 2px">&nbsp;
        <input type="button" name="exit" value="{$lang_agreement_no}" style="padding: 2px" onclick="javascript: window.close(); return false;">
        </form>
    </div>
EOT;
    show_footer();
}

function show_env_result(&$env_items, &$dirfile_items, &$func_items) {

    $env_str = $file_str = $dir_str = $func_str = '';
    $error_code = 0;

    foreach($env_items as $key => $item) {
        if($key == 'php' && strcmp($item['current'], $item['r']) < 0) {
            show_msg('php_version_too_low', $item['current'], 0);
        }
        $status = 1;
        if($item['r'] != 'notset') {
            if($item['status'] != 1){
                $status = 0;
                $error_code = ENV_CHECK_ERROR;
            }
        }
        
        $env_str .= "<tr>\n";
        $env_str .= "<td>".lang($key)."</td>\n";
        $env_str .= "<td class=\"padleft\">".lang($item['r'],true)."</td>\n";
        $env_str .= "<td class=\"padleft\">".lang($item['b'],true)."</td>\n";
        $env_str .= ($status ? "<td class=\"w pdleft1\">" : "<td class=\"nw pdleft1\">").lang($item['current'],true)."</td>\n";
        $env_str .= "</tr>\n";
    }

    foreach($dirfile_items as $key => $item) {
        $tagname = $item['type'] == 'file' ? 'File' : 'Dir';
        $variable = $item['type'].'_str';

    
        $$variable .= "<tr>\n";
        $$variable .= "<td>$item[path]</td><td class=\"w pdleft1\">".lang('writeable')."</td>\n";
        if($item['status'] == 1) {
            $$variable .= "<td class=\"w pdleft1\">".lang('writeable')."</td>\n";
        } elseif($item['type'] == 'dir' && $item['status'] == -1) {
            $error_code = ENV_CHECK_ERROR;
            $$variable .= "<td class=\"nw pdleft1\">".lang('nodir')."</td>\n";
        } elseif($item['type'] == 'file' && $item['status'] == -1) {
            $error_code = ENV_CHECK_ERROR;
            $$variable .= "<td class=\"nw pdleft1\">".lang('unwriteable')."</td>\n";
        } else {
            $error_code = ENV_CHECK_ERROR;
            $$variable .= "<td class=\"nw pdleft1\">".lang('unwriteable')."</td>\n";
        }
        $$variable .= "</tr>\n";
    }

    show_header();

    echo "<h2 class=\"title\">".lang('env_check')."</h2>\n";
    echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;\">\n";
    echo "<tr>\n";
    echo "\t<th>".lang('project')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('center_required')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('center_best')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('curr_server')."</th>\n";
    echo "</tr>\n";
    echo $env_str;
    echo "</table>\n";

    echo "<h2 class=\"title\">".lang('priv_check')."</h2>\n";
    echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;width:90%;\">\n";
    echo "\t<tr>\n";
    echo "\t<th>".lang('step1_file')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('step1_need_status')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('step1_status')."</th>\n";
    echo "</tr>\n";
    echo $file_str;
    echo $dir_str;
    echo "</table>\n";

    foreach($func_items as $item) {
        $status = function_exists($item);
        $func_str .= "<tr>\n";
        $func_str .= "<td>$item()</td>\n";
        if($status) {
            $func_str .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
            $func_str .= "<td class=\"padleft\">".lang('none')."</td>\n";
        } else {
            $error_code = ENV_CHECK_ERROR;
            $func_str .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
            $func_str .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
        }
    }
    echo "<h2 class=\"title\">".lang('func_depend')."</h2>\n";
    echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;width:90%;\">\n";
    echo "<tr>\n";
    echo "\t<th>".lang('func_name')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('check_result')."</th>\n";
    echo "\t<th class=\"padleft\">".lang('suggestion')."</th>\n";
    echo "</tr>\n";
    echo $func_str;
    echo "</table>\n";

    show_next_step(2, $error_code);

    show_footer();


}

function show_setting($setname, $varname = '', $value = '', $type = 'text|password|checkbox', $error = '') {
    if($setname == 'start') {
        echo "<form method=\"post\" action=\"index.php\">\n";
        return;
    } elseif($setname == 'end') {
        echo "\n</table>\n</form>\n";
        return;
    } elseif($setname == 'hidden') {
        echo "<input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";
        return;
    }

    echo "\n".'<tr><th class="tbopt'.($error ? ' red' : '').'">&nbsp;'.(empty($setname) ? '' : lang($setname).':')."</th>\n<td>";
    if($type == 'text' || $type == 'password') {
        $value = htmlspecialchars($value);
        echo "<input type=\"$type\" name=\"$varname\" value=\"$value\" size=\"35\" class=\"txt\">";
    } elseif($type == 'submit') {
        $value = empty($value) ? 'next_step' : $value;
        echo "<input type=\"submit\" name=\"$varname\" value=\"".lang($value)."\" class=\"btn\">\n";
    } elseif($type == 'checkbox') {
        if(!is_array($varname) && !is_array($value)) {
            echo "<label><input type=\"checkbox\" name=\"$varname\" value=\"1\"".($value ? 'checked="checked"' : '')."style=\"border: 0\">".lang($setname.'_check_label')."</label>\n";
        }
    } else {
        echo $value;
    }

    echo "</td>\n<td>&nbsp;";
    if($error) {
        $comment = '<span class="red">'.(is_string($error) ? lang($error) : lang($setname.'_error')).'</span>';
    } else {
        $comment = lang($setname.'_comment', false);
    }
    echo "$comment</td>\n</tr>\n";
    return true;
}
function show_tips($tip, $title = '', $comment = '', $style = 1) {
    global $lang;
    $title = empty($title) ? lang($tip) : $title;
    $comment = empty($comment) ? lang($tip.'_comment', FALSE) : $comment;
    if($style) {
        echo "<div class=\"desc\"><b>$title</b>";
    } else {
        echo "</div><div class=\"main\" style=\"margin-top: -123px;\">$title<div class=\"desc1 marginbot\"><ul>";
    }
    $comment && print('<br>'.$comment);
    echo "</div>";
}
function show_form(&$form_items, $error_msg) {

    global $step ;

    if(empty($form_items) || !is_array($form_items)) {
        return;
    }

    show_header();
    show_setting('start');
    show_setting('hidden', 'step', $step);
    
    show_select_db();
    
    $is_first = 1;
    echo '<div id="form_items_'.$step.'" '.($step == 5 ? 'style="display:none"' : '').'><br />';
    foreach($form_items as $key => $items) {
        global ${'error_'.$key};
        if($is_first == 0) {
            echo '</table>';
            echo '</div>';
        }
        
        echo '<div id="'.$key.'_feilds">';
        if(!${'error_'.$key}) {
            show_tips('tips_'.$key);
        } else {
            show_error('tips_admin_config', ${'error_'.$key});
        }

        echo '<table class="tb2">';
        foreach($items as $k => $v) {
            $value = '';
            if(!empty($error_msg)) {
                $value = isset($_POST[$key][$k]) ? $_POST[$key][$k] : '';
            }
            if(empty($value)) {
                if(isset($v['value']) && is_array($v['value'])) {
                    if($v['value']['type'] == 'constant') {
                        $value = defined($v['value']['var']) ? constant($v['value']['var']) : $v['value']['var'];
                    } else {
                        $value = isset($GLOBALS[$v['value']['var']])?$GLOBALS[$v['value']['var']]:'';
                    }
                } else {
                    $value = '';
                }
            }

            show_setting($k, $key.'['.$k.']', $value, $v['type'], isset($error_msg[$key][$k]) ? $key.'_'.$k.'_invalid' : '');
        }
        
        if($is_first) {
            $is_first = 0;
        }
        
    }
    echo '</table>';
    echo '</div>';
    echo '<table class="tb2">';
    show_setting('', 'submitname', 'new_step', 'submit');
    show_setting('end');
    show_footer();
}

function show_select_db(){
    echo '<script>
        function selected_adapter(v){
            if(v=="sqlite"){
                $("mysqldbinfo_feilds").style.display = "none";
            }else{
                $("mysqldbinfo_feilds").style.display = "block";
            }
        }
        window.onload = function(){
            selected_adapter($("sel_dbadapter").value);
        }
    </script>';
    echo '<div class="desc"><b>'.lang('sel_db_type').'</b></div>';
    echo '<table class="tb2">
    <tbody><tr><th class="tbopt">&nbsp;'.lang('db_type').':</th>
    <td><select id="sel_dbadapter" name="dbadapter" onchange="selected_adapter(this.value)">';
    
    $adp = p('dbadapter');
    if(function_exists('mysql_connect') || function_exists('mysqli_connect')){
        echo '<option value="mysql" '.($adp=='mysql'?'selected="selected"':'').'>Mysql</option>';
    }
    if(function_exists('sqlite_open')){
        echo '<option value="sqlite" '.($adp=='sqlite'?'selected="selected"':'').'>Sqlite</option>';
    }

    echo '</select></td>
    <td>&nbsp;'.lang('db_type_comments').'</td>
    </tr>
    </tbody></table>';
}

function show_next_step($step, $error_code) {
    echo "<form action=\"index.php\" method=\"get\">\n";
    if(isset($GLOBALS['hidden'])) {
        echo $GLOBALS['hidden'];
    }
    echo '<input type="hidden" name="step" value="'.$step.'" />';
    if($error_code == 0) {
        $nextstep = "<input type=\"button\" onclick=\"history.back();\" value=\"".lang('old_step')."\"><input type=\"submit\" value=\"".lang('new_step')."\">\n";
    } else {
        $nextstep = "<input type=\"button\" disabled=\"disabled\" value=\"".lang('not_continue')."\">\n";
    }
    echo "<div class=\"btnbox marginbot\">".$nextstep."</div>\n";
    echo "</form>\n";
}

function check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre) {
    if(!function_exists('mysql_connect')) {
        show_msg('undefine_func', 'mysql_connect', 0);
    }
    if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
        $errno = mysql_errno();
        $error = mysql_error();
        if($errno == 1045) {
            show_msg('database_errno_1045', $error, 0);
        } elseif($errno == 2003) {
            show_msg('database_errno_2003', $error, 0);
        } else {
            show_msg('database_connect_error', $error, 0);
        }
    } else {
        if($query = @mysql_query("SHOW TABLES FROM $dbname")) {
            while($row = mysql_fetch_row($query)) {
                if(preg_match("/^$tablepre/", $row[0])) {
                    return false;
                }
            }
        }
    }
    return true;
}

function show_install() {
?>
<script type="text/javascript">
function showmessage(message) {
    document.getElementById('notice').innerHTML += message + '<br />';
    document.getElementById('notice').scrollTop = 100000000;
}
function initinput() {
    window.location='index.php?method=complete';
}
</script>
    <div class="main">
        <div class="btnbox"><div id="notice"></div></div>
        <div class="btnbox marginbot">
    <input type="button" name="submit" value="<?php echo lang('install_in_processed');?>" disabled style="height: 25" id="laststep" onclick="initinput()">
    </div>
<?php
}