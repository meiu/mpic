<?php
/**
 * $Id: nav.mdl.php 208 2011-11-15 10:37:19Z lingter $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */

class nav_mdl extends modelfactory{
    var $table_name = '#@nav';
    var $default_order = 'sort asc';

    function get_enabled_navs(){
        $cache =& loader::lib('cache');
        $value = $cache->get('enabled_navs');
        if($value){
            return $value;
        }
        $this->db->select($this->table_name,$this->default_cols,'enable=1',$this->default_order.',id asc');
        $value = $this->db->getAll();
        $cache->set('enabled_navs',$value);
        return $value;
    }

    function clear_nav_cache(){
        $cache =& loader::lib('cache');
        $cache->remove('enabled_navs');
    }
}