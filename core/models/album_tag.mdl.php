<?php
/**
 * $Id: album_tag.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class album_tag_mdl{
    function album_tag_mdl(){
        $this->album =& loader::model('album');
    }
    
    function lists($data){
        $filters = array();
        if(isset($data['cate_id'])){
            $filters['cate_id'] = intval($data['cate_id']);
        }
        if(isset($data['tag'])){
            $filters['tag'] = $data['tag'];
        }
        if(isset($data['name'])){
            $filters['name'] = $data['name'];
        }
        if(isset($data['type'])){
            $filters['priv_type'] = intval($data['type']);
        }
        
        $order = isset($data['order'])?$data['order']:null;
        $fields = isset($data['fields'])?$data['fields']:'*';
        if(array_key_exists('page',$data)){
            $page = intval($data['page']);
            $page = $page<1?1:$page;
            $pageset = intval($data['pagesize']);
            return $this->album->get_all($page,$filters,$order,$pageset,$fields);
        }else{
            return $this->album->get_top($data['limit'],$filters,$order,$fields);
        }
    }

    function load($data){
        $album_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->album->get_info($album_id,$fields);
    }

    function get_next($data){
        $album_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->album->get_next($album_id,$fields);
    }

    function get_prev($data){
        $album_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->album->get_prev($album_id,$fields);
    }
}