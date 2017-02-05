<?php
/**
 * $Id: user.mdl.php 360 2012-03-05 10:18:32Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */
 
class user_mdl extends modelfactory{
    
    var $cookie_name = 'MPIC_AUTH';
    var $cookie_auth_key = 'key1234';
    var $cookie_domain = '';
    var $uinfo = array();
    var $LOGIN_FLAG = false;
    var $table_name = '#@users';
    
    function user_mdl(){
        parent::modelfactory();
        $config =& loader::config();
        
        $this->cookie_name = $config['cookie_name'];
        $this->cookie_auth_key = $config['cookie_auth_key'];
        $this->cookie_domain = $config['cookie_domain'];
        
        if(isset($_COOKIE[$this->cookie_name])){

            $auth = authcode(
                $_COOKIE[$this->cookie_name],
                'DECODE', 
                md5($this->cookie_auth_key)
            );

            $auth = explode("\t",$auth);
            $uid = isset($auth[1])?$auth[1]:0;
            $upass = isset($auth[0])?$auth[0]:'';
            $this->db->select('#@users','*',"id=".intval($uid));
            $uinfo =  $this->db->getRow();
            if(!$uinfo){
                $this->LOGIN_FLAG = false;
            }else{
                if($uinfo['user_pass'] == $upass){    
                    $this->LOGIN_FLAG = true;
                    $this->uinfo = $uinfo;
                }else{
                    $this->LOGIN_FLAG = false;
                }
            }
        }else{
            $this->LOGIN_FLAG = false;
        }
    }
    
    function get_info_by_name($name){
        $this->db->select('#@users','*',"user_name=".$this->db->q_str($name));
        return $this->db->getRow();
    }
    
    function check_pass($uid,$pass){
        $info = $this->get_info($uid);
        if(!$info){
            return false;
        }
        if($info['user_pass'] != $pass){
            return false;
        }
        return true;
    }
    
    /**
     * 判断用户是否登陆
     *
     * @return Bool
     */
    function loggedin(){
        return $this->LOGIN_FLAG;
    }
    
    /**
     * 获取用户信息
     *
     * @param String $key
     * @param String $default
     * @return String
     */
    function get_field($key,$default = '') {
        return isset ($this->uinfo[$key]) ? $this->uinfo[$key] : $default;
    }
    
    function get_all_field(){
        return $this->uinfo;
    }
    
    /**
     * 设置用户登陆
     *
     * @param String $loginname
     * @param String $password
     * @param String $expire_time
     * @return Bool
     */
    function set_login($login_name,$password,$expire_time = 0){
        
        $uinfo = $this->get_info_by_name($login_name);

        if($uinfo && $uinfo['user_pass'] == $password){

            $this->LOGIN_FLAG = true;

            $this->uinfo = $uinfo;
            
            $my_auth = authcode(
                $password."\t".$uinfo[$this->id_col],
                'ENCODE',
                md5($this->cookie_auth_key)
            );
            @ob_clean();
            setcookie($this->cookie_name,$my_auth,$expire_time,'/',$this->cookie_domain);
            return true;
        }else{
            return false;
        }
    }

    function clear_login(){
        @ob_clean();
        setcookie($this->cookie_name,'',- 86400 * 365,'/',$this->cookie_domain);
    }
    
    function save_extra($id,$extra){
        if(is_array($extra)){
            foreach($extra as $k => $v){
                $this->db->select('#@usermeta','meta_value','userid='.intval($id).' and meta_key='.$this->db->q_str($k));
                $row = $this->db->getRow();
                if($row){
                    $this->db->update('#@usermeta','userid='.intval($id).' and meta_key='.$this->db->q_str($k),array('meta_value'=>$v));
                }else{
                    $this->db->insert('#@usermeta',array('userid'=>intval($id),'meta_key'=>$k,'meta_value'=>$v));
                }
                $this->db->query();
            }
            $cache =& loader::lib('cache');
            $cache->remove('user_extra_'.$id);
        }
    }
    
    function get_extra($id){
        $cache =& loader::lib('cache');
        $value = $cache->get('user_extra_'.$id);
        if($value){
            return $value;
        }
        $this->db->select('#@usermeta','meta_key,meta_value','userid='.intval($id));
        $value = $this->db->getAssoc();
        $cache->set('user_extra_'.$id,$value);
        return $value;
    }
    
}