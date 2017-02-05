<?php

/**
 * $Id: file.php 202 2011-06-06 04:45:21Z lingter $
 * 
 * Cache engine: file
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
 
class cache_file
{
    var $_default_policy = array(
        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存总是失效，设置为 null 则表示不检查缓存有效期。
         */
        'life_time'         => 900,
        'cache_dir'         => 'cache/data',
        'test_validity'     => true
    );

    var $_static_head = '<?php exit(); ?>';
    var $_head_len = 64;
    var $_static_head_len = 16;

    function cache_file($policy = null){
        if(is_array($policy)){
            $this->_default_policy = array_merge($this->_default_policy, $policy);
        }
    }

    function set($id, $data, $policy = null){
        $policy = $this->_policy($policy);
        
        $data = serialize($data);

        $head = $this->_static_head;
        $head .= pack('IS', $policy['life_time'], $policy['test_validity']);
        $head .= str_repeat(' ', $this->_head_len - strlen($head));

        $content = $head;
        if ($policy['test_validity']){
            // 接下来的 32 个字节写入用于验证数据完整性的验证码
            $content .= sprintf('% 32d', strlen($data));
        }

        $content .= $data;

        unset($data);

        // 写入缓存
        file_put_contents(ROOTDIR.$policy['cache_dir'].'/'.$id.'.php', $content, LOCK_EX);
    }
    
    function get($id, $policy = null)
    {
        $policy = $this->_policy($policy);

        // 如果缓存策略 life_time 为 null，表示缓存数据永不过期
        if (is_null($policy['life_time'])){
            $refresh_time = null;
        }
        else{
            $refresh_time = time();
        }

        $path = ROOTDIR.$policy['cache_dir'].'/'.$id.'.php';
        clearstatcache();
        if (!file_exists($path)) { 
            return false; 
        }

        // 读取文件头部
        $fp = fopen($path, 'rb');
        if (!$fp) { return false; }
        flock($fp, LOCK_SH);

        $len = filesize($path);
        // 头部的 32 个字节存储了该缓存的策略
        $head = fread($fp, $this->_head_len);
        $head = substr($head, $this->_static_head_len);
        $len -= $this->_head_len;
        $tmp = unpack('Il/St', substr($head, 0, 8));
        $policy['life_time'] = $tmp['l'];
        $policy['test_validity'] = $tmp['t'];

        do
        {
            // 检查缓存是否已经过期
            if (!is_null($refresh_time))
            {
                if (filemtime($path) <= $refresh_time - $policy['life_time'])
                {
                    $hashtest = null;
                    $data = false;
                    break;
                }
            }
            // 检查缓存数据的完整性
            if ($policy['test_validity'])
            {
                $hashtest = fread($fp, 32);
                $len -= 32;
            }
            if ($len > 0)
            {
                $data = fread($fp, $len);
            }
            else
            {
                $data = false;
            }
        } while (false);

        flock($fp, LOCK_UN);
        fclose($fp);
        if ($data === false)
        {
            return false;
        }
        if ($policy['test_validity'])
        {
            $hash = sprintf('% 32d', strlen($data));
            if ($hash != $hashtest)
            {
                if (is_null($refresh_time))
                {
                    // 如果是永不过期的缓存文件没通过验证，则直接删除
                    unlink($path);
                }
                else
                {
                    // 否则设置文件时间为已经过期
                    touch($path, time() - 2 * abs($policy['life_time']));
                }
                return false;
            }
        }

        $data = @unserialize($data);

        return $data;
    }
    
    /**
     * 删除指定的缓存
     *
     * @param string $id
     * @param array $policy
     */
    function remove($id, $policy = null)
    {
        $policy = $this->_policy($policy);

        $path = ROOTDIR.$policy['cache_dir'].'/'.$id.'.php';
        if (is_file($path)) { unlink($path); }
    }

    function _policy( $policy = null){
        return !is_null($policy) ? array_merge($this->_default_policy, $policy) : $this->_default_policy;
    }
}
