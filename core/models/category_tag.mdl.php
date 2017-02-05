<?php
/**
 * $Id: album_tag.mdl.php 333 2012-01-29 05:53:49Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class category_tag_mdl{
    function category_tag_mdl(){
        $this->category =& loader::model('category');
    }
    
    function lists($data){
        $filters = array();
        if(isset($data['par_id'])){
            $filters['par_id'] = $data['par_id'];
        }
        
        $order = isset($data['order'])?$data['order']:null;
        $fields = isset($data['fields'])?$data['fields']:'*';

        if(array_key_exists('page',$data)){
            $page = intval($data['page']);
            $page = $page<1?1:$page;
            $pageset = intval($data['pagesize']);
            return $this->category->get_all($page,$filters,$order,$pageset,$fields);
        }else{
            return $this->category->get_top($data['limit'],$filters,$order,$fields);
        }
    }

    function tree($data){
        return $this->category->get_categorys_width_cache();
    }
}