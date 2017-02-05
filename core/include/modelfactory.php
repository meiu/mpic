<?php
/**
 * $Id: modelfactory.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */
class modelfactory{
    var $id_col = 'id';
    var $text_column = 'name';
    var $default_cols = '*';
    var $default_order = 'id desc';
    var $table_name = null;
    var $pageset = 10;
    
    function modelfactory(){
        $this->db =& loader::database();
    }
    
    function _filters($filters){
        return null;
    }
    
    function _sort($sort){
        if($sort){
            return $sort;
        }
        return $this->default_order;
    }
    
    function set_pageset($s){
        $s = intval($s);
        $this->pageset = $s?$s:$this->pageset;
    }
    
    function last_insert_id(){
        return $this->db->insertId();
    }
    
    function get_all($page = NULL,$filters = array(),$sort = null,$pageset = null,$fields = null){
        $where = $this->_filters($filters);
        if($sort){
            $sort = $this->_sort($sort);
        }else{
            $sort = $this->default_order;
        }
        if(!$pageset){
            $pageset = $this->pageset;
        }
        if(!$fields){
            $fields = $this->default_cols;
        }

        $this->db->select($this->table_name,$fields,$where,$sort);
        if(is_numeric($page)){
            $data = $this->db->toPage($page,$pageset);
        }else{
            $data = $this->db->getAll();
        }
        return $data;
    }
    /**
     * 根据条件获取一定数量的数据
     * 
     * @param int $limit 数据条数
     * @param array $filters
     * @param array $sort
     */
    function get_top($limit,$filters = array(),$sort = null,$fields = null){
        $where = $this->_filters($filters);
        if($sort){
            $sort = $this->_sort($sort);
        }else{
            $sort = $this->default_order;
        }
        if(!$fields){
            $fields = $this->default_cols;
        }
        $this->db->select($this->table_name,$fields,$where,$sort);
        if(strpos($limit, ',') !== false){
            $lim = explode(',',$limit);
            $this->db->selectLimit(null,$lim[1],$lim[0]);
        }else{
            $this->db->selectLimit(null,$limit);
        }        
        return $this->db->getAll();
    }
    
    function get_row($fields,$filters = array()){
        $where = $this->_filters($filters);
        $this->db->select($this->table_name,$fields,$where);
        $this->db->selectLimit(null,1);
        return $this->db->getRow();
    }

    function save($arr){
        $this->db->insert($this->table_name,$arr);
        if( $this->db->query() ){
            return $this->db->insertId();
        }else{
            return false;
        }
    }
    
    function update($id,$arr){
        $this->db->update($this->table_name,$this->id_col.'='.intval($id),$arr);
        return $this->db->query();
    }
    
    function delete($id){        
        $this->db->delete($this->table_name,$this->id_col.'='.intval($id));
        return $this->db->query();
    }
    
    function get_info($id,$fileds='*'){
        if(is_array($id)){
            if(count($id) >0){
                $in_sql = '';
                foreach($id as $i){
                    $in_sql .= intval($i).',';
                }
                $in_sql = trim($in_sql,',');

                $this->db->select($this->table_name,$fileds,$this->id_col.' in ('.$in_sql.')');
                return $this->db->getAll();
            }else{
                return false;
            }
        }else{
            $this->db->select($this->table_name,$fileds,$this->id_col.'='.intval($id));
            return $this->db->getRow();
        }
    }
}