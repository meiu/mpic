<?php
/**
 * $Id: album.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class album_mdl extends modelfactory{
    var $table_name = '#@albums';
    
    function _filters($filters){
        $str = 'deleted=0';
        if(isset($filters['name']) && $filters['name']!='' && $filters['name']!='ANY'){
            $str .= " and name like '%".$this->db->q_str($filters['name'],false)."%'";
        }
        if(isset($filters['tag']) && $filters['tag'] != '' && $filters['tag'] != 'ANY'){
            $tag_mdl =& loader::model('tag');
            $tag_info = $tag_mdl->get_by_type_name($filters['tag'],1);
            if($tag_info){
                $str .= " and id in (select rel_id from ".$this->db->stripTpre('#@tag_rel')." where tag_id=".intval($tag_info['id']).")";
            }else{
                $str .= " and 1=0";
            }
        }
        if(isset($filters['cate_id']) && $filters['cate_id'] != '' && $filters['cate_id'] != 'ANY'){
            $cate_id = intval($filters['cate_id']);
            if($cate_id == 0){
                $str .= " and cate_id=".$cate_id;
            }else{
                $str .= " and cate_id in (select id from ".$this->db->stripTpre('#@cate')." where cate_path like '%,".intval($cate_id).",%')";
            }
        }
        if(isset($filters['priv_type']) && $filters['priv_type'] != ''){
            $str .= " and priv_type=".intval($filters['priv_type']);
        }else{
            $user_mdl = loader::model('user');
            if(! $user_mdl->loggedin()){
                $str .= " and priv_type<>3";
            }
        }
        return $str;
    }
    
    function _sort($sort){
        switch($sort){
            case 'ct_asc':
                $str = 'create_time asc';
                break;
            case 'ct_desc':
                $str = 'create_time desc';
                break;
            case 'ut_asc':
                $str = 'up_time asc';
                break;
            case 'ut_desc':
                $str = 'up_time desc';
                break;
            case 'p_asc':
                $str = 'photos_num asc';
                break;
            case 'p_desc':
                $str = 'photos_num desc';
                break;
            default:
                if(preg_match('/[0-9a-z\-_]\s+(asc|desc)/is',$sort)){
                    $str = $sort;
                }else{
                    $str = $this->default_order;
                }
        }
        return $str;
    }
    
    function get_kv($album_id = 0){
        $where = 'deleted=0';
        if($album_id>0){
            $where .= ' and id <> '.intval($album_id);
        }
        $this->db->select('#@albums','id,name',$where,'id desc');
        return $this->db->getAssoc();
    }
    
    function real_delete($id,$album_info=null){
        /*if(is_null($album_info)){
            $album_info = $this->get_info($id);
        }
        if($album_info > 0){
            $cover = get_album_cover($id,$album_info['cover_ext']);
            @unlink(ROOTDIR.$cover);
        }*/
        //remove comments
        $mdl_comment =& loader::model('comment');
        $mdl_comment->delete_by_ref(1,$id);
        
        $mdl_photo = & loader::model('photo');
        $photos = $mdl_photo->get_all_items($id);
        if($photos){
            foreach($photos as $v){
                $mdl_photo->real_delete($v['id'],$v);
            }
        }
        return $this->delete($id);
    }
    
    function restore($id){
        return $this->update($id,array('deleted'=>'0'));
    }
    
    function get_trash_count(){
        $this->db->select('#@albums','count(*)','deleted=1');
        return $this->db->getOne();
    }
    
    function get_trash($page=null){
        $this->db->select('#@albums','*','deleted=1');
        if($page){
            $data = $this->db->toPage($page,20);
        }else{
            $data = $this->db->getAll();
        }
        return $data;
    }
    
    function trash($id){
        trash_status(1);
        return $this->update($id,array('deleted'=>1));
    }
    
    function trash_batch($ids){
        if(!is_array($ids)){
            return false;
        }
        $in_sql = '';
        foreach($ids as $i){
            $in_sql .= intval($i).',';
        }
        $in_sql = trim($in_sql,',');

        $this->db->update('#@albums','id in ('.$in_sql.')',array('deleted'=>1));
        if(!$this->db->query()){
            return false;
        }
        trash_status(1);
        return true;
    }
    
    function update_photos_num($id,$up=true){
        $this->db->select('#@photos','count(id)','album_id='.intval($id).' and deleted=0');
        $arr['photos_num'] = $this->db->getOne();
        if($up){
            $arr['up_time'] = time();
        }
        return $this->update($id,$arr);
    }
    
    function update_comments_num($id){
        $this->db->select('#@comments','count(id)','ref_id='.intval($id).' and status=1 and type=1');
        $arr['comments_num'] = $this->db->getOne();
        return $this->update($id,$arr);
    }
    
    function check_repare_cover($id){
        $info = $this->get_info($id);
        $photo_mdl =& loader::model('photo');
        $photo = $photo_mdl->get_info($info['cover_id']);
        if($photo && $photo['deleted']==0 && $photo['album_id']==$id){
            return true;
        }
        $this->db->select('#@photos','id,path','album_id='.intval($id).' and deleted=0');
        $this->db->selectLimit(null,1);
        $photo_info = $this->db->getRow();
        if($photo_info){
            $cover_info['cover_id'] = $photo_info['id'];
        
            $this->db->update('#@photos','album_id='.intval($id),array('is_cover'=>0));
            $this->db->query();
            $this->db->update('#@photos','id='.intval($cover_info['cover_id']),array('is_cover'=>1));
            $this->db->query();
            
            //$this->make_cover_img($id,$photo_info['path'],$ext,$info['cover_ext']);
            //$cover_info['cover_ext'] = $ext;
        }else{
            $cover_info['cover_id'] = 0;
        }
        
        return $this->update($id,$cover_info);
    }
    
    function set_cover($pic_id){
        $photo_mdl =& loader::model('photo');
        $pic_info = $photo_mdl->get_info($pic_id);
        $arr['cover_id'] = $pic_id;
        
        $album_info = $this->get_info($pic_info['album_id']);
        
        $this->db->update('#@photos','album_id='.intval($pic_info['album_id']),array('is_cover'=>0));
        $this->db->query();
        $this->db->update('#@photos','id='.intval($pic_id),array('is_cover'=>1));
        $this->db->query();
        return $this->update($pic_info['album_id'],$arr);
    }
    
    function check_album_priv($id,$album_info = null){
        $user_mdl =& loader::model('user');
        $logined = $user_mdl->loggedin();
        if($logined){
            return true;
        }
        
        if(is_null($album_info)){
            $album_info = $this->get_info($id);
        }
        
        if($album_info['priv_type']==0){
            return true;
        }
        if($album_info['priv_type']==3){
            return false;
        }
        $key = 'Mpic_album_priv_'.$id;
        if($album_info['priv_type']==1){
            if(isset($_COOKIE[$key])){
               if($_COOKIE[$key] == md5($album_info['priv_pass'])){
                   return true;
               }
            }
        }
        if($album_info['priv_type']==2){
            if(isset($_COOKIE[$key])){
               if($_COOKIE[$key] == md5($album_info['priv_question'].$album_info['priv_answer'])){
                   return true;
               }
            }
        }
        return false;
    }

    function save_extra($id,$extra){
        if(is_array($extra)){
            foreach($extra as $k => $v){
                $this->db->select('#@albummeta','meta_value','album_id='.intval($id).' and meta_key='.$this->db->q_str($k));
                $row = $this->db->getRow();
                if($row){
                    $this->db->update('#@albummeta','album_id='.intval($id).' and meta_key='.$this->db->q_str($k),array('meta_value'=>$v));
                }else{
                    $this->db->insert('#@albummeta',array('album_id'=>intval($id),'meta_key'=>$k,'meta_value'=>$v));
                }
                $this->db->query();
            }
        }
    }
    
    function get_extra($id){
        if($value){
            return $value;
        }
        $this->db->select('#@albummeta','meta_key,meta_value','album_id='.intval($id));
        $value = $this->db->getAssoc();
        return $value;
    }

    function get_next($id,$fields='*'){
        $this->db->select($this->table_name,$fields,'id<'.intval($id).' and priv_type<>3','id desc limit 1');
        $row = $this->db->getRow();
        return $row;
    }

    function get_prev($id,$fields='*'){
        $this->db->select($this->table_name,$fields,'id>'.intval($id).' and priv_type<>3','id asc limit 1');
        $row = $this->db->getRow();
        return $row;
    }

    //将某分类相册的分类置为未分类相册
    function set_default_cate($catid){
        $this->db->update($this->table_name,'cate_id='.intval($catid),array('cate_id'=>0));
        return $this->db->query();
    }
}