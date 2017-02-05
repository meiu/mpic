<?php
/**
 * $Id: photo_tag.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class photo_tag_mdl{
    function photo_tag_mdl(){
        $this->photo =& loader::model('photo');
    }
    
    function lists($data){
        $filters = array();
        if(isset($data['album_id'])){
            $filters['album_id'] = intval($data['album_id']);
        }
        if(isset($data['is_open'])){
            $filters['is_open'] = intval($data['is_open']);
        }
        if(isset($data['tag'])){
            $filters['tag'] = $data['tag'];
        }
        if(isset($data['name'])){
            $filters['name'] = $data['name'];
        }
        if(isset($data['cate_id'])){
            $filters['cate_id'] = intval($data['cate_id']);
        }
        
        $order = isset($data['order'])?$data['order']:null;
        $fields = isset($data['fields'])?$data['fields']:'*';

        if(array_key_exists('page',$data)){
            $page = intval($data['page']);
            $page = $page<1?1:$page;
            $pageset = intval($data['pagesize']);
            return $this->photo->get_all($page,$filters,$order,$pageset,$fields);
        }else{
            return $this->photo->get_top($data['limit'],$filters,$order,$fields);
        }
    }
    
    function load($data){
        $photo_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->photo->get_info($photo_id,$fields);
    }
}