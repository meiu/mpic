<?php
/**
 * $Id: setting.mdl.php 298 2011-12-01 03:36:55Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */
class setting_mdl extends modelfactory {
    
    var $setting_pool=array();
    var $_regSave = false;
    
    function get_conf($key,$default=null){
        $key = preg_replace("/([^a-zA-Z0-9_\-\.]+)/","", $key);
        $key_arr = explode('.',$key);
        $k = array_shift($key_arr);
        
        if(isset($this->setting_pool[$k])){
            $value = $this->setting_pool[$k];
        }else{
            $cache =& loader::lib('cache');
            $value = $cache->get('setting_'.$k);
            if($value === false){
                $this->db->select('#@setting','value',"name='".$k."'");
                $data = $this->db->getOne();
                
                if($data){
                    $value = unserialize($data);
                    if (!is_array($value) && count($key_arr)>0) {
                        return $default;
                    }
                }else{
                    return false;
                }
                $cache->set('setting_'.$k,$value);
            }
            $this->setting_pool[$k] = $value;
        }

        foreach($key_arr as $v){
            if(isset($value[$v])){
                $value = $value[$v];
            }else{
                return $default;
            }
        }
        
        return $value;
    }
    
    function set_conf($key,$value,$immediately=false){
        $key = preg_replace("/([^a-zA-Z0-9_\-\.]+)/","", $key);

        $key_arr = explode('.',$key);
        $k = array_shift($key_arr);

        if(count($key_arr)>0){
            $s_k = array_shift($key_arr);
            $this->setting_pool[$k][$s_k] = $value;
        }else{
            $this->setting_pool[$k] = $value;
        }
        
        if($immediately){
            $this->_save();
            return true;
        }else{
            if(!$this->_regSave){
                register_shutdown_function(array(&$this,'_save'));
                $this->_regSave = true;
            }
            return true;
        }
    }
    
    function remove_conf($k){
        if(!$k){
            return ;
        }
        $cache =& loader::lib('cache');
        $cache->remove('setting_'.$k);
        
        $this->db->delete('#@setting','name="'.$k.'"');
        return $this->db->query();
    }
    
    function _save(){
        $cache =& loader::lib('cache');
        foreach($this->setting_pool as $k=>$values){
            $this->db->select('#@setting','value','name="'.$k.'"');
            $data = $this->db->getOne();
            if($data){
                $keyvalue = unserialize($data);
                if(is_array($keyvalue)){
                    $keyvalue = array_merge($keyvalue,$values);
                }else{
                    $keyvalue = $values;
                }
                $this->db->update('#@setting','name="'.$k.'"',array('value'=>addslashes(serialize($keyvalue))));
                $this->db->query();
            }else{
                $this->db->insert('#@setting',array('name'=>$k,'value'=>addslashes(serialize($values))));
                $this->db->query();
            }
            $cache->remove('setting_'.$k);
        }
    }
}