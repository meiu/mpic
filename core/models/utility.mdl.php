<?php
/**
 * $Id: utility.mdl.php 241 2011-10-19 05:30:40Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class utility_mdl extends modelfactory{
    function sys_info(){
        $env_items = array(
                    'meiupic_version' => array('c'=>'MPIC_VERSION'),
                    'operate_system' => array('c' => 'PHP_OS'),
                    'server_software' => array('s' => 'SERVER_SOFTWARE'),
                    'php_runmode'=>'php_runmode',
                    'php_version' => array('c'=>'PHP_VERSION'),
                    'memory_limit' => array('i'=>'memory_limit'),
                    'post_max_size' => array('i'=>'post_max_size'),
                    'upload_max_filesize' => array('i'=>'upload_max_filesize'),
                    'mysql_support' => array('f'=>'mysql_connect'),
                    'mysqli_support' => array('f'=>'mysqli_connect'),
                    'sqlite_support' => array('f' => 'sqlite_open'),
                    'database_version' => 'database_version',
                    'gd_info' => 'gd_info',
                    'imagick_support' => array('cla' => 'imagick'),
                    'exif_support' =>   array('f' => 'exif_read_data'),
                    'zlib_support' => array('f' => 'gzopen')
                    );
        $info = array();
        foreach($env_items as $k=>$v){
            if($k == 'php_runmode'){
               $info[] = array('title'=>lang($k),'value'=>php_sapi_name());
            }elseif($k == 'database_version'){
               $adapater = ($this->db->adapter=='mysql' || $this->db->adapter=='mysqli')?'Mysql':$this->db->adapter;
               $info[] = array('title'=>lang($k),'value'=>$adapater.' '.$this->db->version());
            }elseif($k == 'gd_info'){
                $tmp = function_exists('gd_info') ? gd_info() :false;
                $gd_ver = empty($tmp['GD Version']) ? lang('notsupport') : $tmp['GD Version'];
                $gd_rst = array();
                if(isset($tmp['FreeType Support']) && $tmp['FreeType Support']){
                    $gd_rst[] = 'freetype';
                }
                if(isset($tmp['GIF Read Support']) && $tmp['GIF Read Support']){
                    $gd_rst[] = 'gif';
                }
                if((isset($tmp['JPEG Support']) && $tmp['JPEG Support']) 
                    || 
                    (isset($tmp['JPG Support']) && $tmp['JPG Support'])){
                    $gd_rst[] = 'jpg';
                }
                if(isset($tmp['PNG Support']) && $tmp['PNG Support']){
                    $gd_rst[] = 'png';
                }
                $info[] = array('title'=>lang($k),'value'=>$gd_ver.' '.implode(',',$gd_rst));
            }elseif($k == 'sqlite_support'){
                if(function_exists('sqlite_open') || class_exists("SQLite3") || (function_exists('pdo_drivers') && in_array('sqlite',pdo_drivers()))){
                    $support = true;
                }else{
                    $support = false;
                }
                $info[] = array('title'=>lang($k),'value'=>$support?lang('support'):lang('notsupport'));
            }elseif(isset($v['f'])){
                $info[] = array('title'=>lang($k),'value'=>function_exists($v['f'])?lang('support'):lang('notsupport'));
            }elseif(isset($v['c'])){
                $info[] = array('title'=>lang($k),'value'=>constant($v['c']));
            }elseif(isset($v['s'])){
                $info[] = array('title'=>lang($k),'value'=>$_SERVER[$v['s']]);
            }elseif(isset($v['cla'])){
                $info[] = array('title'=>lang($k),'value'=>class_exists($v['cla'])?lang('support'):lang('notsupport'));
            }
        }
        return $info;
      
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
    
    function get_time_zones(){
        $zonelist =  array
            (
                'Kwajalein' => -12.00,
                'Pacific/Midway' => -11.00,
                'Pacific/Honolulu' => -10.00,
                'America/Anchorage' => -9.00,
                'America/Los_Angeles' => -8.00,
                'America/Denver' => -7.00,
                'America/Tegucigalpa' => -6.00,
                'America/New_York' => -5.00,
                'America/Caracas' => -4.30,
                'America/Halifax' => -4.00,
                'America/St_Johns' => -3.30,
                'America/Argentina/Buenos_Aires' => -3.00,
                'America/Sao_Paulo' => -3.00,
                'Atlantic/South_Georgia' => -2.00,
                'Atlantic/Azores' => -1.00,
                'Europe/Dublin' => 0,
                'Europe/Belgrade' => 1.00,
                'Europe/Minsk' => 2.00,
                'Asia/Kuwait' => 3.00,
                'Asia/Tehran' => 3.30,
                'Asia/Muscat' => 4.00,
                'Asia/Yekaterinburg' => 5.00,
                'Asia/Kolkata' => 5.30,
                'Asia/Katmandu' => 5.45,
                'Asia/Dhaka' => 6.00,
                'Asia/Rangoon' => 6.30,
                'Asia/Krasnoyarsk' => 7.00,
                'Asia/Shanghai/Hongkong' => 8.00,
                'Asia/Seoul' => 9.00,
                'Australia/Darwin' => 9.30,
                'Australia/Canberra' => 10.00,
                'Asia/Magadan' => 11.00,
                'Pacific/Fiji' => 12.00,
                'Pacific/Tongatapu' => 13.00
            );
        return $zonelist;
    }
}