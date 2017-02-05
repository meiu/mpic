<?php
/**
 * $Id: category.mdl.php 231 2011-10-22 02:47:38Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class category_mdl extends modelfactory {
    var $table_name = '#@cate';
    
    function _filters($filters){
        $str = 'deleted=0';
        if(isset($filters['par_id']) && $filters['par_id']!=''){
            $str .= " and par_id=".intval($filters['par_id']);
        }
    }

    function get_categorys_width_cache(){
        $cache =& loader::lib('cache');
        $cateArr = $cache->get('flat_category');
        if($cateArr){
            return $cateArr;
        }
        $cateArr = $this->get_flat_category();
        $cache->set('flat_category',$cateArr);
       
        return $cateArr;
    }
    /**
     * 获取树状分类 tree
     * 带参数标示取出分类及子分类的树状结构
     * 不带参数表示取出所有的
     */
    function get_categorys($catid=0){
        if($catid>0){
            $where = "cate_path like '%,".intval($catid).",%'";
        }else{
            $where = null;
        }
        $this->db->select($this->table_name,'*',$where,'sort asc');
        $cateList = $this->db->getAll();
        $pArr = array();
        if(is_array($cateList)){
            $sArr = array();
            foreach($cateList as $cate){
                $sArr['s_'.$cate['par_id']][] = $cate;
            }
            $pArr = $this->_sub($sArr,0);
        }
        unset($cateList);
        unset($sArr);
        return $pArr;
    }
    //平面化分类
    function get_flat_category($catid=0){
        $oarr = $this->get_categorys($catid);
        $arr = array();
        if(is_array($oarr)){
            $this->_deep($arr,$oarr,0);
        }
        return $arr;
    }
    /**
     * 获取属于当前分类的所有分类的id，包含它自己
     * 返回数组
     *
     * @param int $id
     * @return array
     */
    function get_belong_ids($id){
        $this->db->select($this->table_name,'id','cate_path like \'%,'.intval($id).',%\'');
        return $this->db->getCol(0);
    }

    //递归显示
    function _deep(& $arr,& $oarr,$deep = 0){
        foreach($oarr as $v){
            $v['deep'] = $deep;
            $tmp = $v['sub'];
            unset($v['sub']);
            if(!$tmp){
                $v['last'] = true;
            }else{
                $v['last'] = false;
            }
            $arr[] = $v;
            if($tmp){
                $this->_deep($arr,$tmp,$deep+1);
            }
        }
        return $arr;
    }
    //递归分类私有函数
    function _sub(& $arr,$parent){
        if(isset($arr['s_'.$parent])){
            $tarr = $arr['s_'.$parent];
            foreach($tarr as $k=>$v){
                $tarr[$k]['sub'] = $this->_sub($arr,$v['id']);
            }
            return $tarr;
        }else{
            return false;
        }
    }

    /**
     * 删除分类
     *
     * @param Int $ids
     * @return Boolean
     */
    function delete($id){
        $this->db->select($this->table_name,'count(*)','par_id='.$id);
        if($this->db->getOne() > 0){
            return false;
        }

        $this->db->delete($this->table_name,'id = '.$id);

        if($this->db->query()){
            $cache =& loader::lib('cache');
            $cache->remove('flat_category');
            $cache->remove('cate_path_'.$id);
            return true;
        }else{
            return false;
        }
    }

    //编辑分类
    function update($id,$arr){
        if(isset($arr['par_id'])){
            $this->db->select($this->table_name,'par_id',"id = '$id'");
            $old_row = $this->db->getRow();
            if($old_row['par_id'] != $arr['par_id']){
                $this->db->select($this->table_name,'cate_path','id = '.$arr['par_id']);
                $row = $this->db->getRow();
                $path_ids = explode(',',trim($row['cate_path'],','));
                if(in_array($id,$path_ids)){
                    return false;
                }
            
                if($arr['par_id'] != 0){
                    $arr['cate_path'] = $row['cate_path'].$id.',';
                }else{
                    $arr['cate_path'] = ','.$id.',';
                }
            
                $this->db->select($this->table_name,'id,cate_path',"cate_path like '%,".$id.",%' and id<>".intval($id));
                $result = $this->db->getAll();
                if($result){
                    foreach ($result as $v){
                         $path = $arr['cate_path'].substr( $v['cate_path'], strpos( $v['cate_path'], ",".$id."," ), strlen( $v['cate_path'] ) );
                         $path = preg_replace('/^.*\,'.$id.'\,(.*)$/',$arr['cate_path']."\${1}",$v['cate_path']);
                         $this->db->update($this->table_name,'id='.$v['id'],array( 'cate_path'=>$path ));
                         $this->db->query();
                    }
                }
            }
        }

        $this->db->update($this->table_name,'id='.$id,$arr);
        $ret = $this->db->query();
        $cache =& loader::lib('cache');
        $cache->remove('flat_category');
        $cache->remove('cate_path_'.$id);
        return $ret;
    }
    //添加分类
    function save($arr){
        $this->db->select($this->table_name,'cate_path','id = '.$arr['par_id']);
        $row = $this->db->getRow();
        
        //$this->db->startTrans();
        $this->db->insert($this->table_name,$arr);
        if(!$this->db->query()){
            return false;
        }

        $id = $this->db->insertId();
        
        if($arr['par_id'] != 0){
            $uparr['cate_path'] = $row['cate_path'].$id.',';
        }else{
            $uparr['cate_path'] = ','.$id.',';
        }

        $this->db->update($this->table_name,'id='.$id,$uparr);
        $this->db->query();

        $cache =& loader::lib('cache');
        $cache->remove('flat_category');

        return $id;
    }
    //获取某个分类的路径及超链接
    function cate_path_link($cate_id){
        $data = array();
        if($cate_id == 0){
            $data[] = array('name'=>lang('no_cate_album'),'link'=>site_link('albums','index',array('cate'=>'0')));
        }else{
            $cache =& loader::lib('cache');
            $cate_id = intval($cate_id);
            $data = $cache->get('cate_path_'.$cate_id);
            if(!$data){
                $row = $this->get_info(intval($cate_id),'cate_path');
                if($row){
                    $cates = explode(',',trim($row['cate_path'],','));
                    $cate_infos = $this->get_info($cates,'id,name');
                    foreach($cates as $cate){
                        foreach($cate_infos as $info){
                            if($info['id'] == $cate){
                                $data[] = array('name'=>$info['name'],'cate_id'=>$cate);
                            }
                        }
                    }
                }
                $cache->set('cate_path_'.$cate_id,$data);
            }
            if($data){
                foreach($data as $k=>$v){
                    $data[$k]['link'] =site_link('albums','index',array('cate'=>$v['cate_id']));
                }
            }
        }
        return $data;
    }
}
