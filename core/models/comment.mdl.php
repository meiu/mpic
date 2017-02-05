<?php
/**
 * $Id: comment.mdl.php 208 2011-06-15 10:37:19Z lingter $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */

class comment_mdl extends modelfactory{
    var $table_name = '#@comments';
    var $pageset = 10;
    
    function _filters($filters){
        $str = '1';//'status=1 and pid=0';
        if(isset($filters['status']) && $filters['status'] != 'all'){
            $str .= ' and status='.intval($filters['status']);
        }    
        if(isset($filters['pid'])){
            $str .= ' and pid='.intval($filters['pid']);
        }
        if(isset($filters['ref_id'])){
            $str .= ' and ref_id='.intval($filters['ref_id']);
        }
        if(isset($filters['type']) && $filters['type']!=''){
            $str .= " and type=".intval($filters['type']);
        }
        return $str;
    }
    
    function save($comment){
        $ret = parent::save($comment);
        if($ret){
            $this->update_num($comment['type'],$comment['ref_id']);
        }
        return $ret;
    }
    
    function update_num($type,$id){
        if($type == 1){
            $mdl_album =& loader::model('album');
            $mdl_album->update_comments_num($id);
        }elseif($type == 2){
            $mdl_photo =& loader::model('photo');
            $mdl_photo->update_comments_num($id);
        }
    }
    
    function get_sub($pid){
        $this->db->select($this->table_name,'*','status=1 and pid='.intval($pid),'id asc');
        return $this->db->getAll();
    }
    
    function delete_by_ref($type,$ref_id){
        $this->db->delete($this->table_name,'type='.intval($type).' and ref_id='.intval($ref_id));
        return $this->db->query();
    }

    function delete($id){
        $info = $this->get_info($id);
        $this->db->delete($this->table_name,$this->id_col.'='.intval($id));
        $ret = $this->db->query();
        if($ret){
            $this->db->delete($this->table_name,'pid='.intval($id));
            $this->db->query();
            
            $this->update_num($info['type'],$info['ref_id']);
        }
        return $ret;
    }
    
    function delete_batch($ids){
        if(!is_array($ids)){
            return false;
        }
        foreach($ids as $id){
            $this->delete($id);
        }
        return true;
    }
    
    function block($id){
        $info = $this->get_info($id);
        
        $this->db->update($this->table_name,$this->id_col.'='.intval($id),array('status'=>2));
        $ret = $this->db->query();
        if($ret){
            $this->db->update($this->table_name,'pid='.intval($id),array('status'=>2));
            $this->db->query();
            
            $this->update_num($info['type'],$info['ref_id']);
        }
        return $ret;
    }
    
    function block_batch($ids){
        if(!is_array($ids)){
            return false;
        }
        foreach($ids as $id){
            $this->block($id);
        }
        return true;
    }
    
    function approve($id){
        $info = $this->get_info($id);
        
        $this->db->update($this->table_name,$this->id_col.'='.intval($id),array('status'=>1));
        $ret = $this->db->query();
        if($ret){
            $this->db->update($this->table_name,'pid='.intval($id),array('status'=>1));
            $this->db->query();
            
            $this->update_num($info['type'],$info['ref_id']);
        }
        return $ret;
    }
    
    function approve_batch($ids){
        if(!is_array($ids)){
            return false;
        }
        foreach($ids as $id){
            $this->approve($id);
        }
        return true;
    }
    
    function count_group_status(){
        $this->db->select($this->table_name,'status,count(*) as num','1 group by status');
        return $this->db->getAssoc();
    }
    
    function recount_all(){
        $this->db->select($this->table_name,'type,ref_id,count(id) as num','status=1 group by type,ref_id');
        $data = $this->db->getAll();
        if($data){
            foreach($data as $v){
                if($v['type'] == 1){
                    $this->db->update('#@albums','id='.intval($v['ref_id']),array('comments_num'=>$v['num']));
                }elseif($v['type'] == 2){
                    $this->db->update('#@photos','id='.intval($v['ref_id']),array('comments_num'=>$v['num']));
                }
                $this->db->query();
            }
        }
        
        return true;
    }
}