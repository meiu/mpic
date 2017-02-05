<?php
if(!defined('IN_MEIU')) exit('Access Denied');

/*重新组织创建表的SQL*/
function _createtable($sql) {
    $db =& loader::database();
    
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
    ($db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=utf8" : " TYPE=$type");
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

@set_time_limit(0);
@ignore_user_abort(true);

$error = '';
if(getPost('_upgrade_act') == 'login'){
    $login_name = getPost('login_name');
    $login_pass = getPost('login_pass');
    if(!$login_name){
        $error .= '<div>'.lang('username_empty').'</div>';
    }elseif(!$login_pass){
        $error .=  '<div>'.lang('userpass_empty').'</div>';
    }elseif($login_name && $login_pass && !$user->set_login($login_name,md5($login_pass)) ){
        $error .= '<div>'.lang('username_pass_error').'</div>';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo lang('upgrade_title',MPIC_VERSION);?></title>
<style>
    body{
        font-size:12px;
        background:#fbfbfb;
    }
    #main{
        width:500px;
        margin:0 auto;
        border:1px solid #bbb;
        border-radius:5px;
        padding:10px;
        background:#fff;
        box-shadow: 0 4px 10px -1px rgba(200, 200, 200, 0.7);
    }
    #main h1{
        text-align:center;
        color:#003366;
    }
    #main form{
        width:300px;
        margin:30px auto 20px;
    }

    #main form .inputstyle {
        background: none repeat scroll 0 0 #FBFBFB;
        border: 1px solid #E5E5E5;
        box-shadow: 1px 1px 2px rgba(200, 200, 200, 0.2) inset;
        font-size: 24px;
        font-weight: 200;
        line-height: 1;
        margin-bottom: 16px;
        margin-right: 6px;
        margin-top: 2px;
        outline: medium none;
        padding: 3px;
        width: 100%;
        border-radius: 3px 3px 3px 3px;
    }
    #main form .label{
        font-size:14px;
        color:#777;
    }
    #main form .inputstyle:focus{
        border:1px solid #999;
    }
    #main form .ylbtn{
        background-color: #F5F5F5;
        border: 1px solid #E5E5E5;
        border-radius: 2px 2px 2px 2px;
        color: #666666;
        cursor: default;
        font-family: Arial,sans-serif;
        font-size: 14px;
        font-weight: bold;
        height: 29px;
        line-height: 27px;
        margin: 11px 6px;
        min-width: 54px;
        padding: 0 8px;
        text-align: center;
        cursor:pointer;
    }
    #main form .ylbtn:hover{
        border:1px solid #999
    }
    .info{
        line-height:2;
        margin-bottom:20px;
        color:green;
    }
    .error{
        color:red;
    }
    .msg{
        width:300px;
        margin:30px auto 20px;
        padding:10px;
        border:1px solid #ff9966;
        background:#ffffee;
    }
    .succ{
        color:green;
    }
    .fail{
        color:red;
    }
</style>
</head>

<body>
<div id="main">
<h1><?php echo lang('upgrade_title',MPIC_VERSION);?></h1>
<?php
//如果没有登录输出登录框
if(!$user->loggedin()){
    echo '<form id="login_form" action="" method="post">';
    if($error){
        echo '<div class="info error">'.$error.'</div>';
    }else{
        echo '<div class="info">'.lang('upgrade_need_login').'</div>';
    }
    echo '<input type="hidden" name="_upgrade_act" value="login" />
        <div class="field">
            <div class="label">'.lang('username').'</div>
            <div class="ipts"><input type="text" name="login_name" class="inputstyle iptw2" value="" /></div>
            <div class="clear"></div>
        </div>
        <div class="field">
            <div class="label">'.lang('password').'</div>
            <div class="ipts"><input type="password" name="login_pass" class="inputstyle iptw2" value="" /></div>
            <div class="clear"></div>
        </div>
        <div class="field">
            <div class="ipts"><input type="submit" value="'.lang('login').'" class="ylbtn f_left" name="submit"></div>
            <div class="clear"></div>
        </div>
    </form>';
}else{
    $prev_version = $setting_mdl->get_conf('system.version');
    $current_version = MPIC_VERSION;
    if($current_version == $prev_version){
        echo '<div class="msg fail">'.lang('have_been_updated').'</div>';
        exit;
    }
    
    //如果没有获取到当前version，根据数据库判断
    $db =& loader::database();
    if($prev_version == ''){
        //获取照片表的字段
        $photo_cols = $db->getColumns('#@photos');
        $rows = $db->show_tables();
        if(!in_array($db->pre.'setting',$rows)){
            $prev_version = '1.1';
        }elseif(!in_array($db->pre.'cate',$rows)){
            $prev_version = '2.0';
        }elseif(!in_array('cate_id',$photo_cols)){
            $prev_version = '2.1';
        }else{
            $prev_version = $current_version;
        }
    }else{
        if(version_compare($prev_version,'2.0','>=') && version_compare($prev_version,'2.1','<')){
            $prev_version = '2.0';
        }elseif(version_compare($prev_version,'2.1','>=') && version_compare($prev_version,'2.2','<')){
            $prev_version = '2.1';
        }
    }

    if(version_compare($current_version,$prev_version,'<')){
        echo '<div class="msg fail">'.lang('could_not_degrade').'</div>';
        exit;
    }
    if($prev_version == '' || version_compare($prev_version,'2.0','<') ){
        echo '<div class="msg fail">'.lang('too_old_to_update').'</div>';
        exit;
    }
    
    $script_file = ROOTDIR.'install/upgrade_'.$prev_version.'.php';
    if(file_exists($script_file)){
        require_once($script_file);
    }

    $setting_mdl->set_conf('system.version',MPIC_VERSION);
    $setting_mdl->set_conf('update.return','lastest');
    //清除缓存
    //Todo 需要统一清除缓存的功能，使其兼容memcache等
    dir_clear(ROOTDIR.'cache/data');
    dir_clear(ROOTDIR.'cache/templates');
    dir_clear(ROOTDIR.'cache/tmp');

    echo '<div class="msg succ">'.lang('upgrade_success').' <a href="'.site_link('default','index').'">'.lang('click_to_jump').'</a></div>';
}
?>
</div>
</body>
</html>