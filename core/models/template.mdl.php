<?php
/**
 * $Id: template.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */
class template_mdl{
    /**
     * 编译模板/刷新模版
     *
     * @param $tplfile    模板原文件路径
     * @param $compiledtplfile    编译完成后，写入文件名
     * @return $strlen 长度
     */
    function template_compile($tplfile, $compiledtplfile) {
        $str = @file_get_contents ($tplfile);
        $str = $this->template_parse ($str);
        $strlen = file_put_contents ($compiledtplfile, $str );
        @chmod ($compiledtplfile, 0777);
        return $strlen;
    }
    
    function fetch($file,$templateid=null,$tpldir=null){
        global $base_path;
        $output =& loader::lib('output');
        $params = $output->getAll();
        extract($params);
        
        if(!$templateid){
            $templateid = TEMPLATEID;
        }
        
        $style_path = $base_path.TPLDIR.'/';
        $setting =& loader::model('setting');
        $theme_config = $setting->get_conf('theme_'.$templateid,array());
        if(isset($_config)){
            if($theme_config){
                $_config = array_merge($_config,$theme_config);
            }
        }else{
            $_config = $theme_config;
        }
        
        $footer = '<script src="'.$statics_path.'js/common.js" type="text/javascript"></script>';
        if(isset($loggedin) && $loggedin){
            $footer .= '<script src="'.$statics_path.'js/admin.js" type="text/javascript"></script>';
        }
        $footer .= 'Pow'.'ered by <a href="http://mei'.'upic.m'.'eiu.cn/" target="_blank">Mei'.'uPic '.MPIC_VERSION.'</a> ';
        $footer .= safe_invert($setting->get_conf('site.footer'),true);
        
        $show_process_info = $setting->get_conf('system.show_process_info');

        
        ob_start();
        include $this->template($file,$templateid,$tpldir);
        $content = ob_get_clean();
        return $content;
    }

    function template($file,$templateid=null,$tpldir=null) {
        if(strpos($file,':')!==false ) {
            list($templateid, $file) = explode(':', $file);
            $tpldir = 'plugins/'.$templateid.'/templates';
        }
        $tpldir = $tpldir?$tpldir:TPLDIR;
        $templateid = $templateid ? $templateid : TEMPLATEID;
        
        $tplfile = ROOTDIR.$tpldir.'/'.$file.'.htm';
        
        if(TEMPLATEID != 1 && !file_exists($tplfile)) {
            $tplfile = ROOTDIR.'themes/default/'.$file.'.htm';
        }
        if (! file_exists ( $tplfile )) {
            exit(lang('file_not_exists',$tplfile));
        }
        
        $compiledtplfile = ROOTDIR.'cache/templates/'.$templateid.'_'.str_replace(array('/','\\'),'_',$file).'.tpl.php';
        if(!file_exists($compiledtplfile) || @filemtime($tplfile) > @filemtime($compiledtplfile)){
            $this->template_compile($tplfile,$compiledtplfile);
        }
        return $compiledtplfile;
    }
    /**
     * 解析模板
     *
     * @param $str    模板内容
     * @return ture
     */
    function template_parse($str) {
        $str = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $str);
        $str = str_replace("{LF}", "<?php echo \"\\n\"; ?>", $str);
        $str = preg_replace ( "/\{template\s+(.+)\}/", "<?php include \$this->template(\"\\1\"); ?>",$str);
        $str = preg_replace ( "/\{include\s+(.+)\}/", "<?php include \\1; ?>", $str );
        $str = preg_replace ( "/\{php\s+(.+)\}/", "<?php \\1?>", $str );
        $str = preg_replace ( "/\{echo\s+(.+?)\}/", "<?php echo \\1; ?>", $str);
        $str = preg_replace ( "/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str );
        $str = preg_replace ( "/\{else\}/", "<?php } else { ?>", $str );
        $str = preg_replace ( "/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str );
        $str = preg_replace ( "/\{\/if\}/", "<?php } ?>", $str );
        //for 循环
        $str = preg_replace("/\{for\s+(.+?)\}/","<?php for(\\1) { ?>",$str);
        $str = preg_replace("/\{\/for\}/","<?php } ?>",$str);
        //++ --
        $str = preg_replace("/\{\+\+(.+?)\}/","<?php ++\\1; ?>",$str);
        $str = preg_replace("/\{\-\-(.+?)\}/","<?php --\\1; ?>",$str);
        $str = preg_replace("/\{(.+?)\+\+\}/","<?php \\1++; ?>",$str);
        $str = preg_replace("/\{(.+?)\-\-\}/","<?php \\1--; ?>",$str);
        $str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str );
        $str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str );
        $str = preg_replace ( "/\{\/loop\}/", "<?php } ?>", $str );
        $str = preg_replace ( "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
        $str = preg_replace ( "/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
        $str = preg_replace ( "/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str );
        $str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "\$this->addquote('<?php echo \\1;?>')",$str);
        $str = preg_replace ( "/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str );
        $str = preg_replace("/\{link(\s+.+?)\}/ies", "\$this->striplink('\\1')", $str);
        $str = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->striplang('\\1')",$str);
        $str = preg_replace("/\{img\s+(.+?)\}/is", "<?php echo img_path(\\1);?>",$str);
        $str = preg_replace("/\{mp:(\w+)(\s+[^}]+)\}/ie", "\$this->mp_tag('\\1','\\2', '\\0')", $str);
        $str = preg_replace("/\{\/mp\}/ie", "\$this->end_mp_tag()", $str);
        $str = preg_replace("/\{filter:(\w+)(\s+.+?)\}/ie","\$this->do_filter('\\1','\\2')",$str);
        $str = preg_replace("/\{thumbimg(\s+.+?)\}/ies","\$this->makethumbpath('\\1')",$str);
        
        $str = "<?php if(!defined('IN_MEIU')) exit('Access Denied'); ?>" . $str;
        return $str;
    }
    
    function striplang($var) {
        $varr = explode('|',$var);
        $str = "<?php echo lang(\"".$varr[0]."\"";
        for($i=1;$i<count($varr);$i++){
            $str .=',"'.preg_replace("/`(.+)`/U","{\\1}",$varr[$i]).'"';
        }
        $str .= '); ?>';
        return $str;
    }
    
    function striplink($var){
        preg_match_all("/\s+([a-zA-Z0-9_\-]+)\=([^\"\s]+|\"[^\"]+\")/i", stripslashes($var), $matches, PREG_SET_ORDER);
        
        $args = array();
        $ctl = 'default';
        $act = 'index';
        foreach($matches as $v){
            if($v[1] == 'ctl'){
                $ctl = trim($v[2],'"');
            }elseif($v[1] == 'act'){
                $act = trim($v[2],'"');
            }else{
                $args[$v[1]] = trim($v[2],'"');
            }
        }
        return "<?php echo site_link(\"$ctl\",\"$act\",".$this->arr_to_code($args)."); ?>";
    }

    function makethumbpath($var){
        preg_match_all("/\s+([a-zA-Z0-9_\-]+)\=([^\"\s]+|\"[^\"]+\")/i", stripslashes($var), $matches, PREG_SET_ORDER);
        
        $args = array();
        foreach($matches as $v){
            $args[$v[1]] = trim($v[2],'"');
        }
        return '<?php echo encrypt_imgpath('.$this->arr_to_code($args).'); ?>';
    }

    function mp_tag($op,$data,$html){
        preg_match_all("/\s+([a-zA-Z0-9_\-]+)\=([^\"\s]+|\"[^\"]+\")/i", stripslashes($data), $matches, PREG_SET_ORDER);

        $arr = array('action','start','num','cache','page', 'urlrule', 'return');
		$tools = array('sql');
		$datas = array();
		$tag_id = md5(stripslashes($html));
		foreach ($matches as $v) {
			if(in_array($v[1], $arr)) {
				$$v[1] = trim($v[2],'"');
				continue;
			}
            
			$datas[$v[1]] = trim($v[2],'"');
		}

		$str = '<?php ';
		$num = isset($num) && intval($num) ? intval($num) : 20;
		$cache = isset($cache) && intval($cache) ? intval($cache) : 0;
		$return = isset($return) && trim($return) ? trim($return) : 'data';

		if (isset($urlrule)){
            $urlrule = str_replace('[#page#]',rawurlencode('[#page#]'),$urlrule);
        }else{
            $urlrule = '';
        }

		if (!empty($cache) && !isset($page)) {
			$str .= '$tag_cache_name = md5(implode(\'&\','.$this->arr_to_code($datas).').\''.$tag_id.'\');';
            $str .= '$cache_lib =& loader::lib("cache"); ';
            $str .= 'if(!$'.$return.' = $cache_lib->get($tag_cache_name)){';
		}

        if (in_array($op,$tools)) {
			switch ($op) {
                case 'sql':
                    if (isset($start) && intval($start)) {
                        $limit = intval($start).','.$num;
                    } else {
                        $limit = $num;
                    }
                    $str .= '$db =& loader::database();';
                    if (isset($page)) {
                        $str .= '$pagesize = '.$num.';';
                        $str .= '$page = '.(isset($page)?str_replace('`','', $page):1).';';
                        $str .= '$urlrule = "'.$urlrule.'";';
                        $str .= '$db->setSql("'.$datas['sql'].'");';
                        $str .= '$r = $db->toPage($page,$pagesize);';
                        $str .= '$page_lib =& loader::lib("page");';
                        $str .= '$pages = $page_lib->fetch($r["total"],$r["current"],$urlrule);';
                        $str .= '$'.$return.' = $r["ls"];';
                        $str .= '$total_page = $r["total"];';
                        $str .= 'unset($r);';
                    }else{
                        $str .= '$'.$return.' = $db->getAll("'.$datas['sql'].' LIMIT '.$limit.'");';
                    }
                break;
            }
        }else{
            if (!isset($action) || empty($action)) return false;
            if (!file_exists(MODELDIR.$op.'_tag.mdl.php')) return false; 
            $str .= '$'.$op.'_tag =& loader::model("'.$op.'_tag"); if (method_exists($'.$op.'_tag, \''.$action.'\')) {';
            if (isset($start) && intval($start)) {
                $datas['limit'] = intval($start).','.$num;
            } else {
                $datas['limit'] = $num;
            }
            if (isset($page)) {
                $datas['page'] = $page;
                $datas['pagesize'] = $num;
                $str .= '$urlrule = "'.$urlrule.'";';
                $str .= '$page_lib =& loader::lib("page");';
                $str .= '$r = $'.$op.'_tag->'.$action.'('.$this->arr_to_code($datas).');';
                $str .= '$pages = $page_lib->fetch($r["total"], $r["current"] ,$urlrule);';
                $str .= '$'.$return.' = $r["ls"];';
                $str .= '$total_page = $r["total"];';
                $str .= 'unset($r);';
            }else{
                //$datas['limit'] = $num;
                $str .= '$'.$return.' = $'.$op.'_tag->'.$action.'('.$this->arr_to_code($datas).');';
            }
            
            $str .= '}';
            
        }
        if (!empty($cache) && !isset($page)) {
			$str .= 'if(!empty($'.$return.')){ $cache_lib->set($tag_cache_name, $'.$return.',array("life_time" => '.$cache.'));}';
			$str .= '}';
		}

        return $str.' ?>';
    }

    function end_mp_tag(){
        return '';
    }
    

    function do_filter($filtername,$data){
        preg_match_all("/\s+([a-zA-Z0-9_\-]+)\=([^\"\s]+|\"[^\"]+\")/i", stripslashes($data), $matches, PREG_SET_ORDER);
        
    }

    function arr_to_code($data) {
        if (is_array($data)) {
            $str = 'array(';
            foreach ($data as $key=>$val) {
                if (is_array($val)) {
                    $str .= "'$key'=>".$this->arr_to_code($val).",";
                } else {
                    if (strpos($val, '$')===0) {
                        $str .= "'$key'=>$val,";
                    }elseif(preg_match('/^`(.+)`$/',$val)){
                        $str .= "'$key'=>".trim($val,'`').",";
                    } else {
                        $str .= "'$key'=>'".addslashes($val)."',";
                    }
                }
            }
            $str = rtrim($str,',');
            return $str.')';
        }
        return false;
    }
    
    /**
     * 转义 // 为 /
     *
     * @param $var    转义的字符
     * @return 转义后的字符
     */
    function addquote($var) {
        return str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
    }
    
}