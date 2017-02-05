<?php

/**
 * $Id: xcache.php 150 2011-05-13 05:11:43Z lingter $
 * 
 * Cache engine: Xcache
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
 
class cache_xcache
{
    /**
     * 默认的缓存策略
     *
     * @var array
     */
    var $_default_policy = array(
        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存总是失效，设置为 null 则表示不检查缓存有效期。
         */
        'life_time'         => 900,
    );

    /**
     * 构造函数
     *
     * @param 默认的缓存策略 $default_policy
     */
    function cache_xcache(array $default_policy = null){
        if (isset($default_policy['life_time'])){
            $this->_default_policy['life_time'] = (int)$default_policy['life_time'];
        }
    }

    /**
     * 写入缓存
     *
     * @param string $id
     * @param mixed $data
     * @param array $policy
     */
    function set($id, $data, array $policy = null){
        $life_time = !isset($policy['life_time']) ? (int)$policy['life_time'] : $this->_default_policy['life_time'];
        xcache_set($id, $data, $life_time);
    }

    /**
     * 读取缓存，失败或缓存失效时返回 false
     *
     * @param string $id
     *
     * @return mixed
     */
    function get($id){
        if (xcache_isset($id)){
            return xcache_get($id);
        }
        return false;
    }

    /**
     * 删除指定的缓存
     *
     * @param string $id
     */
    function remove($id){
        xcache_unset($id);
    }
}