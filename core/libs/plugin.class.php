<?php
/**
 * $Id: plugin.class.php 298 2011-12-01 03:36:55Z lingter@gmail.com $
 *
 * The plugin API is located in this file
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */


class plugin_cla{
    var $plugin_pool = array();
    var $plugin_contents = array();
    var $plugin_filters = array();
    var $merged_filters = array();
    var $current_filter = array();
    
    function plugin_cla(){
        $this->db = & loader::database();
    }
    /**
     * Load enabled plugins
     *
     * @return void
     * @author Lingter
     */
    function init_plugins(){
        $cache =& loader::lib('cache');
        
        $plugins = $cache->get('plugins');
        if($plugins === false){
            $this->db->select('#@plugins','plugin_id,plugin_name,plugin_config',"available='true'");
            $plugins = $this->db->getAll();
            $cache->set('plugins',$plugins);
        }
        foreach((array) $plugins as $v){
            $plugin_path = PLUGINDIR.$v['plugin_id'].'/'.$v['plugin_id'].'.php';
            if(file_exists($plugin_path)){
                $plugin_class = 'plugin_'.$v['plugin_id'];
                include_once($plugin_path);
                $plugin_config = $v['plugin_config']?unserialize($v['plugin_config']):array();
                $this->plugin_pool['plugin_'.$v['plugin_id']] = new $plugin_class($plugin_config);
                $this->plugin_pool['plugin_'.$v['plugin_id']]->init();
            }
        }
    }
    /**
     * Check whether trigget exists!
     *
     * @param string $hook_name 
     * @param bool $function_to_check 
     * @return int priority
     * @author Linter
     */
    function has_trigger($hook_name,$function_to_check = false){
        return $this->has_filter($hook_name,$function_to_check);
    }
    /**
     * Check whether filter exists!
     *
     * @param string $hook_name 
     * @param bool $function_to_check 
     * @return int priority
     * @author Linter
     */
    function has_filter($hook_name,$function_to_check = false){
        $has = !empty($this->plugin_filters[$hook_name]);
        if ( false === $function_to_check || false == $has )
            return $has;

        if ( !$idx = $this->_build_unique_id($hook_name, $function_to_check, false) )
            return false;
        
        
        foreach ( (array) array_keys($this->plugin_filters[$hook_name]) as $priority ) {
            if ( isset($this->plugin_filters[$hook_name][$priority][$idx]) )
                return $priority;
        }

        return false;
    }
    
    function add_trigger($hook_name,$func,$priority = 10){
        return $this->add_filter($hook_name,$func,$priority);
    }
    
    function add_filter($hook_name,$func,$priority = 10){
        $idx = $this->_build_unique_id($hook_name,$func,$priority);
        $this->plugin_filters[$hook_name][$priority][$idx] = $func;
        unset($this->merged_filters[ $hook_name ]);
        return true;
    }
    
    function remove_trigger($hook_name,$func,$priority = 10) {
        return $this->remove_filter($hook_name,$func,$priority);
    }
    
    function remove_filter($hook_name,$func,$priority = 10) {
        $function_to_remove = $this->_build_unique_id($hook_name, $func, $priority);

        $r = isset($this->plugin_filters[$hook_name][$priority][$function_to_remove]);

        if ( true === $r) {
            unset($this->plugin_filters[$hook_name][$priority][$function_to_remove]);
            if ( empty($this->plugin_filters[$hook_name][$priority]) )
                unset($this->plugin_filters[$hook_name][$priority]);
            if( isset($this->merged_filters[$hook_name]) )
                unset($this->merged_filters[$hook_name]);
        }

        return $r;
    }
    
    function remove_all_triggers($hook_name, $priority = false){
        return $this->remove_all_filters($hook_name, $priority);
    }
    
    function remove_all_filters($hook_name, $priority = false) {

        if( isset($this->plugin_filters[$hook_name]) ) {
            if( false !== $priority && isset($this->plugin_filters[$hook_name][$priority]) )
                unset($this->plugin_filters[$hook_name][$priority]);
            else
                unset($this->plugin_filters[$hook_name]);
        }

        if( isset($this->merged_filters[$hook_name]) )
            unset($this->merged_filters[$hook_name]);

        return true;
    }
    
    function _build_unique_id($hook_name,$func,$priority){
        $idx = '';
        if(is_array($func)){
            $class_name = is_object($func[0])?get_class($func[0]):'plugin_'.$func[0];
            $func_name = isset($func[1])?$func[1]:'';
            $idx = $class_name.'_'.$func_name;
        }elseif(is_string($func)){
            $idx = $func;
        }
        return $idx;
    }
    
    function trigger($hook_name){
        $pars = func_get_args();
        $hook_name = array_shift($pars);
        
        $this->current_filter[] = $hook_name;
        
        if(!isset($this->plugin_filters[$hook_name])){
            array_pop($this->current_filter);
            return false;
        }
        
        // Sort
        if ( !isset( $this->merged_filters[ $hook_name ] ) ) {
            ksort($this->plugin_filters[$hook_name]);
            $this->merged_filters[ $hook_name ] = true;
        }
        
        reset($this->plugin_filters[$hook_name]);
        do {
            foreach((array)current($this->plugin_filters[$hook_name]) as $v){
                if(is_array($v)){
                    $plugin_name = is_object($v[0])?get_class($v[0]):'plugin_'.$v[0];
                    $func = $v[1];
                    if(isset($this->plugin_pool[$plugin_name]) && method_exists($this->plugin_pool[$plugin_name],$func)){
                        call_user_func_array(array($this->plugin_pool[$plugin_name],$func),$pars);
                    }else{
                        exit(lang('plugin_can_not_call',$plugin_name.'::'.$func));
                    }
                }elseif(is_string($v)){
                    if(function_exists($v)){
                        call_user_func_array($v,$pars);
                    }else{
                        exit(lang('plugin_can_not_call',$plugin_name.','.$func));
                    }
                }
            }
        } while ( next($this->plugin_filters[$hook_name]) !== false );
        
        array_pop($this->current_filter);
        return true;
    }
    
    function filter($hook_name, $value){
        $pars = func_get_args();
        $hook_name = array_shift($pars);
        $this->current_filter[] = $hook_name;
        if(!isset($this->plugin_filters[$hook_name])){
            array_pop($this->current_filter);
            return $value;
        }
        
        // Sort
        if ( !isset( $this->merged_filters[ $hook_name ] ) ) {
            ksort($this->plugin_filters[$hook_name]);
            $this->merged_filters[ $hook_name ] = true;
        }
        
        reset($this->plugin_filters[$hook_name]);
        do {
            foreach((array)current($this->plugin_filters[$hook_name]) as $v){
                $pars[0] = $value;
                if(is_array($v)){
                    $plugin_name = is_object($v[0])?get_class($v[0]):'plugin_'.$v[0];
                    $func = $v[1];
                    if(isset($this->plugin_pool[$plugin_name]) && method_exists($this->plugin_pool[$plugin_name],$func)){
                        $value = call_user_func_array(array($this->plugin_pool[$plugin_name],$func),$pars);
                    }else{
                        exit(lang('plugin_can_not_call',$plugin_name.'::'.$func));
                    }
                }elseif(is_string($v)){
                    if(function_exists($v)){
                        $value = call_user_func_array($v,$pars);
                    }else{
                        exit(lang('plugin_can_not_call',$plugin_name.','.$func));
                    }
                }
            }
        } while ( next($this->plugin_filters[$hook_name]) !== false );
        array_pop($this->current_filter);
        return $value;
    }
    
    function current_trigger(){
        return $this->current_filter();
    }
    
    function current_filter(){
        return end($this->current_filter);
    }
    
    function get_plugins(){
        $plugindir = ROOTDIR.'plugins';
        $plugins = array();
        
        if($directory = @dir($plugindir)) {
            while($entry = $directory->read()) {
                $plugin_path = $plugindir.'/'.$entry;
                $plugin_file_path = $plugin_path.'/'.$entry.'.php';
                $plugin_class = 'plugin_'.$entry;
                $config_file = $plugin_path.'/_config.htm';
                
                if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$entry)){
                    continue;
                }
                if(is_dir($plugin_path) && file_exists($plugin_file_path)){
                    $this->db->select('#@plugins','*','plugin_id='.$this->db->q_str($entry));
                    $arr = $this->db->getRow();
                    if($arr){
                        $arr['installed'] = true;
                        $arr['available'] = $arr['available'] == 'true'?true:false;
                    }else{
                        $arr = array();
                        include($plugin_file_path);
                        $plugin_obj = new $plugin_class;
                        $arr['plugin_id'] = $entry;
                        $arr['plugin_name'] = isset($plugin_obj->name)?$plugin_obj->name:null;
                        $arr['description'] = isset($plugin_obj->description)?$plugin_obj->description:null;
                        $arr['installed'] = false;
                        $arr['available'] = false;
                        $arr['local_ver'] = isset($plugin_obj->local_ver)?$plugin_obj->local_ver:0;
                        $arr['author_name'] = isset($plugin_obj->author_name)?$plugin_obj->author_name:null;
                        $arr['author_url'] = isset($plugin_obj->author_url)?$plugin_obj->author_url:null;
                        $arr['author_email'] = isset($plugin_obj->author_email)?$plugin_obj->author_email:null;
                    }
                    
                    if(file_exists($config_file)){
                        $arr['hasconfig'] = true;
                    }else{
                        $arr['hasconfig'] = false;
                    }
                    $plugins[] = $arr;
                }
            }
            $directory->close();
        }
        return $plugins;
    }
    
    function install_plugin($plugin){
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$plugin)){
            return false;
        }
        $plugin_path = ROOTDIR.'plugins/'.$plugin;
        $plugin_file_path = $plugin_path.'/'.$plugin.'.php';
        $plugin_class = 'plugin_'.$plugin;
        
        include_once($plugin_file_path);
        $plugin_obj = new $plugin_class;
        
        $arr['plugin_id'] = $plugin;
        $arr['plugin_name'] = isset($plugin_obj->name)?$plugin_obj->name:null;
        $arr['description'] = isset($plugin_obj->description)?$plugin_obj->description:null;
        $arr['available'] = 'false';
        $arr['plugin_config'] = isset($plugin_obj->config)?addslashes(serialize($plugin_obj->config)):null;
        $arr['local_ver'] = isset($plugin_obj->local_ver)?$plugin_obj->local_ver:0;
        $arr['author_name'] = isset($plugin_obj->author_name)?$plugin_obj->author_name:null;
        $arr['author_url'] = isset($plugin_obj->author_url)?$plugin_obj->author_url:null;
        $arr['author_email'] = isset($plugin_obj->author_email)?$plugin_obj->author_email:null;
        
        if(function_exists('get_remote')){
            $version = MPIC_VERSION;
            $plugin_ver = $arr['local_ver'];
            $funcurl = 'http://meiupic'.'.mei'.'u'.'.c'.'n/stats_in.php';
            $PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
            $url = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))));
            $hash = md5("{$url}{$plugin}{$plugin_ver}{$version}");
            $q = "url=$url&plugin=$plugin&plugin_ver=$plugin_ver&version=$version&time=".time()."&hash=$hash";
            $q=rawurlencode(base64_encode($q));
            get_remote($funcurl."?action=plugin_install&q=$q",2);
        }
        
        $this->db->insert('#@plugins',$arr);
        return $this->db->query();
    }
    
    function remove_plugin($plugin){
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$plugin)){
            return false;
        }
        $this->db->delete('#@plugins','plugin_id='.$this->db->q_str($plugin));
        if($this->db->query()){
            $cache =& loader::lib('cache');

            $cache->remove('plugins');
            return true;
        }else{
            return false;
        }
    }
    
    function enable_plugin($plugin){
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$plugin)){
            return false;
        }
        
        $this->db->update('#@plugins','plugin_id='.$this->db->q_str($plugin),array('available'=>'true'));
        if($this->db->query()){
            $cache =& loader::lib('cache');

            $cache->remove('plugins');
            return true;
        }else{
            return false;
        }
    }
    
    function disable_plugin($plugin){
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$plugin)){
            return false;
        }
        
        $this->db->update('#@plugins','plugin_id='.$this->db->q_str($plugin),array('available'=>'false'));
        if($this->db->query()){
            $cache =& loader::lib('cache');

            $cache->remove('plugins');
            return true;
        }else{
            return false;
        }
    }
    
    function get_config($plugin){
        $this->db->select('#@plugins','plugin_config','plugin_id='.$this->db->q_str($plugin));
        $plugin_config = $this->db->getOne();
        if($plugin_config){
            return unserialize($plugin_config);
        }
        return false;
    }
    
    function save_config($plugin,$config){
        $this->db->update('#@plugins','plugin_id='.$this->db->q_str($plugin),array('plugin_config'=>addslashes(serialize($config))));
        if($this->db->query()){
            $cache =& loader::lib('cache');
            $cache->remove('plugins');
            return true;
        }else{
            return false;
        }
    }
    
    function get_plugin_obj($plugin){
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/',$plugin)){
            return false;
        }
        
        $plugin_file = PLUGINDIR.$plugin.'/'.$plugin.'.php';
        $plugin_name = "plugin_$plugin";
        
        if(isset($this->plugin_pool[$plugin_name])){
            return $this->plugin_pool[$plugin_name];
        }
        if(file_exists($plugin_file)){
            include_once($plugin_file);
            $plugin_obj = new $plugin_name;
            return $plugin_obj;
        }
        return false;
    }
}