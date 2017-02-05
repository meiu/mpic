<?php
/**
 * $Id: comment_tag.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class comment_tag_mdl{
    function comment_tag_mdl(){
        $this->comment =& loader::model('comment');
    }
    
    function lists($data){
        $filters = array();

        if(isset($data['album_id'])){
            $filters['type'] = 1;
            $filters['ref_id'] = $data['album_id'];
        }elseif(isset($data['photo_id'])){
            $filters['type'] = 2;
            $filters['ref_id'] = $data['photo_id'];
        }
        if(isset($data['type']) && !isset($filters['type'])){
            $filters['type'] = intval($data['type']);
        }
        $filters['status'] = 1;
        
        $order = isset($data['order'])?$data['order']:null;
        $fields = isset($data['fields'])?$data['fields']:'*';

        if(array_key_exists('page',$data)){
            $page = intval($data['page']);
            $page = $page<1?1:$page;
            $pageset = intval($data['pagesize']);
            return $this->comment->get_all($page,$filters,$order,$pageset,$fields);
        }else{
            return $this->comment->get_top($data['limit'],$filters,$order,$fields);
        }
    }

    function load($data){
        $photo_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->album->get_info($photo_id,$fields);
    }
}