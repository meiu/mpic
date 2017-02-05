<?php
/**
 * $Id: cache.class.php 232 2011-09-22 18:09:16Z lingter@gmail.com $
 * 
 * Cache Class:  Including apc,file,memcache and xcache. This is a factory class.
 *
 * Using Example : 
 *
 *      $cache_obj = loadder::lib('cache');
 *      $cache_obj->set('key','This is data!'); // Set the data
 *      $data = $cache_obj->get('key'); // Get the data
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class cache_cla{
    var $cache = array();
    /**
    * construct function
    */
    function cache_cla(){
        $this->config =& Loader::config();
        $engine = $this->config['cache_engine'];
        include_once(LIBDIR.'/cache/'.$engine.'.php');
        $enginename = 'cache_'.$engine;
        $cache_policy = isset($this->config['cache_policy'])?$this->config['cache_policy']:array();
        $this->_cache = new $enginename($cache_policy);
    }
    /**
     * Set data
     *
     * @param string $id 
     * @param string $data
     * @param array $policy 
     * @return void
     * @author Lingter
     */
    function set($id,$data,$policy = null){
        $this->cache[$id] = $data;
        $this->_cache->set($id,$data,$policy);
    }
    /**
     * Get data
     *
     * @param string $id 
     * @return mixed
     * @author Lingter
     */
    function get($id){
        if(isset($cache[$id])){
            return $this->cache[$id];
        }
        $this->cache[$id] = $this->_cache->get($id);
        return $this->cache[$id];
    }
    /**
     * Remove data
     *
     * @param string $id 
     * @return void
     * @author Lingter
     */
    function remove($id){
        unset($this->cache[$id]);
        $this->_cache->remove($id);
    }

    function clean(){
        $this->cache = array();
        $this->_cache->clean();
    }
}