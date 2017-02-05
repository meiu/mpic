<?php
/**
 * $Id: tag_tag.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2012 meiu.cn lingter@gmail.com
 */

class tag_tag_mdl{
    function tag_tag_mdl(){
        $this->tag =& loader::model('tag');
    }
    
    function lists($data){
        $filters = array();
        if(isset($data['type'])){
            $filters['type'] = $data['type'];
        }
        
        $order = isset($data['order'])?$data['order']:null;
        $fields = isset($data['fields'])?$data['fields']:'*';

        if(array_key_exists('page',$data)){
            $page = intval($data['page']);
            $page = $page<1?1:$page;
            $pageset = intval($data['pagesize']);
            return $this->tag->get_all($page,$filters,$order,$pageset,$fields);
        }else{
            return $this->tag->get_top($data['limit'],$filters,$order,$fields);
        }
    }

    function load($data){
        $tag_id = $data['id'];
        $fields = isset($data['fields'])?$data['fields']:'*';
        return $this->tag->get_info($tag_id,$fields);
    }
}