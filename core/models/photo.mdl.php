<?php
/**
 * $Id: photo.mdl.php 422 2012-11-06 09:13:56Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010-2011 meiu.cn lingter@gmail.com
 */

class photo_mdl extends modelfactory{
    var $table_name = '#@photos';
    
    function _filters($filters){
        $str = 'deleted=0';
        if(isset($filters['album_id'])){
            $str .= ' and album_id='.intval($filters['album_id']);
        }elseif(isset($filters['is_open']) && $filters['is_open']){
            $str .= ' and album_id in (select id from '.$this->db->stripTpre('#@albums').' where priv_type=0)';
        }

        if(isset($filters['name']) && $filters['name']!='' && $filters['name']!='ANY'){
            $str .= " and name like '%".$this->db->q_str($filters['name'],false)."%'";
        }
        if(isset($filters['tag']) && $filters['tag'] != '' && $filters['tag'] != 'ANY'){
            $tag_mdl =& loader::model('tag');
            $tag_info = $tag_mdl->get_by_type_name($filters['tag'],2);
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
        return $str;
    }
    
    function _sort($sort){
        switch($sort){
            case 'tu_asc':
                $str = 'create_time asc,id desc';
                break;
            case 'tu_desc':
                $str = 'create_time desc,id desc';
                break;
            case 'tt_asc':
                $str = 'taken_time asc,id desc';
                break;
            case 'tt_desc':
                $str = 'taken_time desc,id desc';
                break;
            case 'h_asc':
                $str = 'hits asc,id desc';
                break;
            case 'h_desc':
                $str = 'hits desc,id desc';
                break;
            case 'c_asc':
                $str = 'comments_num asc,id desc';
                break;
            case 'c_desc':
                $str = 'comments_num desc,id desc';
                break;
            case 'n_asc':
                $str = 'name asc,id desc';
                break;
            case 'n_desc':
                $str = 'name desc,id desc';
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
    
    function restore($id){
        if(!$this->update($id,array('deleted'=>0))){
            return false;
        }
        $info = $this->get_info($id);
        $album_mdl =& loader::model('album');
        $album_mdl->update_photos_num($info['album_id'],false);
        $album_mdl->check_repare_cover($info['album_id']);
        return true;
    }
    
    function real_delete($id,$info=null){
        if(is_null($info)){
            $info = $this->get_info($id);
        }
        $mdl_comment =& loader::model('comment');
        $mdl_comment->delete_by_ref(2,$id);
        
        $storlib =& loader::lib('storage');
        $storlib->delete($info['thumb']);
        $storlib->delete($info['path']);
        return $this->delete($id);
    }
    
    function get_items($filters = array(),$sort = null){
        $where = $this->_filters($filters);
        if($sort){
            $sort = $this->_sort($sort);
        }else{
            $sort = $this->default_order;
        }
        $this->db->select($this->table_name,'id',$where,$sort);
        $data = $this->db->getCol(0);
        return $data;
    }
    
    function get_all_items($aid){
        $this->db->select('#@photos','*','album_id='.intval($aid));
        return $this->db->getAll();
    }
    
    function get_trash_count(){
        $this->db->select('#@photos','count(*)','deleted=1');
        return $this->db->getOne();
    }
    
    function get_trash($page=null){
        $this->db->select('#@photos','*','deleted=1');
        if($page){
            $data = $this->db->toPage($page,10);
        }else{
            $data = $this->db->getAll();
        }
        return $data;
    }
    
    function trash($id){
        $info = $this->get_info($id);
        if(!$this->update($id,array('deleted'=>1))){
            return false;
        }
        trash_status(1);
        $album_mdl =& loader::model('album');
        $album_mdl->update_photos_num($info['album_id'],false);
        $album_mdl->check_repare_cover($info['album_id']);
        return true;
    }
    
    function trash_batch($ids){
        if(!is_array($ids)){
            return false;
        }
        $info = $this->get_info(intval($ids[0]));

        $in_sql = '';
        foreach($ids as $i){
            $in_sql .= intval($i).',';
        }
        $in_sql = trim($in_sql,',');

        $this->db->update('#@photos','id in ('.$in_sql.')',array('deleted'=>1));
        if(!$this->db->query()){
            return false;
        }
        trash_status(1);
        $album_mdl =& loader::model('album');
        $album_mdl->update_photos_num($info['album_id'],false);
        $album_mdl->check_repare_cover($info['album_id']);
        return true;
    }
    
    function move($id,$album_id){
        $photo_info = $this->get_info($id);
        $old_album  = $photo_info['album_id'];
        $album_mdl =& loader::model('album');
        $album_info = $album_mdl->get_info($album_id);

        if($this->update($id,array('album_id'=>$album_id,'is_cover'=>0,'cate_id'=>$album_info['cate_id']))){
            
            $album_mdl->update_photos_num($old_album);
            $album_mdl->update_photos_num($album_id);
            $album_mdl->check_repare_cover($old_album);
            $album_mdl->check_repare_cover($album_id);
            
            return true;
        }else{
            return false;
        }
    }
    
    function move_batch($ids,$album_id){
        $photo_info = $this->get_info(intval($ids[0]));
        $old_album  = $photo_info['album_id'];

        $album_mdl =& loader::model('album');
        $album_info = $album_mdl->get_info($album_id);

        $in_sql = '';
        foreach($ids as $i){
            $in_sql .= intval($i).',';
        }
        $in_sql = trim($in_sql,',');

        $this->db->update('#@photos','id in ('.$in_sql.')',array('album_id'=>$album_id,'is_cover'=>0,'cate_id'=>$album_info['cate_id']));
        if(!$this->db->query()){
            return false;
        }
        
        
        $album_mdl->update_photos_num($old_album);
        $album_mdl->update_photos_num($album_id);
        $album_mdl->check_repare_cover($old_album);
        $album_mdl->check_repare_cover($album_id);
        return true;
    }

    function save_upload($album_id,$tmpfile,$filename,$new_photo = true,$photo_info = array()){
        $apath = str_pad($album_id,6,'0',STR_PAD_LEFT);
        $apath = substr($apath,0,4).'/'.substr($apath,4,2);

        $media_dirname = 'data/'.$apath;
        $thumb_dirname = 'data/t/'.$apath;
        
        $storlib =& loader::lib('storage');
        $imglib =& loader::lib('image');
        $exiflib =& loader::lib('exif');
        $fileext = file_ext($filename);
        $filepure = file_pure_name($filename);
        $key = str_replace('.','',microtime(true));
        
        $tmpfs_lib =& loader::lib('tmpfs');
        $tmpfile_thumb = $tmpfs_lib->get_path($key.'_thumb.'.$fileext);
        
        if(file_exists($tmpfile)){
            $setting =& Loader::model('setting');

            if(!$imglib->load($tmpfile)){//如果不是有效的直接退出，并删除临时文件
                $tmpfs_lib->delete($tmpfile,true);
                return false;
            }
            if(!in_array($fileext,array('jpg','png','jpeg','gif','bmp')) ){
                $fileext = $imglib->getExtension();
            }
            if(isset($photo_info['cate_id'])){
                $arr['cate_id'] = $photo_info['cate_id'];
            }

            if(!$new_photo){
                $filepath = $photo_info['path'];
                $thumbpath = $photo_info['thumb'];
            } else {
                $use_old_img_name = $setting->get_conf('upload.use_old_imgname',false);
                //如果文件名是只包含数字
                if($use_old_img_name && file_en_name($filepure)){
                    //如果存在此图片，则生成唯一的名字
                    $filepath = $media_dirname.'/'.$filepure.'.'.$fileext;
                    $count = 1;
                    while(file_exists($filepath)){
                        $filepath = $media_dirname.'/'.$filepure.'_'.$count.'.'.$fileext;
                        $count++;
                    }
                }else{
                    $filepath = $media_dirname.'/'.$key.'.'.$fileext;
                }

                $thumbpath = $thumb_dirname.'/'.$key.'.'.$fileext;
            }
            
            $arr['width'] = $imglib->getWidth();
            $arr['height'] = $imglib->getHeight();
            if( $fileext == 'jpg'){
                $exif = $exiflib->get_exif($tmpfile);
                if($exif){
                    $arr['exif'] = serialize($exif);
                    $taken_time = strtotime($exif['DateTimeOriginal']);
                    $arr['taken_time'] = $taken_time;
                    $arr['taken_y'] = date('Y',$taken_time);
                    $arr['taken_m'] = date('n',$taken_time);
                    $arr['taken_d'] = date('j',$taken_time);
                }
            }
            
            //如果开启自动裁剪大图片,将临时文件直接裁剪
            if($setting->get_conf('upload.enable_cut_big_pic',false)){
                $max_width = $setting->get_conf('upload.max_width',1600);
                $max_height = $setting->get_conf('upload.max_height',1600);
                if($arr['width'] > $max_width || $arr['height'] > $max_height){
                    $imglib->resizeScale($max_width,$max_height);
                    $imglib->save($tmpfile);
                    $arr['width'] = $imglib->getWidth();
                    $arr['height'] = $imglib->getHeight();
                }
            }
            
            //设置水印前生成缩略图 
            $thumb_width = $setting->get_conf('upload.thumb_width',180);
            $thumb_height = $setting->get_conf('upload.thumb_height',180);
            
            if($arr['width'] > $thumb_width || $arr['height'] > $thumb_height){
                if($setting->get_conf('upload.enable_thumb_cut',false)){
                    $imglib->resizeCut($thumb_width,$thumb_height);//裁剪
                }else{
                    $imglib->resizeScale($thumb_width,$thumb_height);
                }
                $imglib->save($tmpfile_thumb);
            }else{//图片过小的话则直接拷贝至缩略图
                @copy($tmpfile,$tmpfile_thumb);
            }
            
            $water_setting = $setting->get_conf('watermark');
            if($water_setting['type'] != 0){
                $imglib->load($tmpfile);
                if($water_setting['type'] == 1){
                    $water_setting['water_mark_type'] = 'image';
                    if(STORAGE_ENGINE != 'file'){
                        $ws_tmpfile = 'ws_tmp';
                        $ws_file_content = $storlib->read($water_setting['water_mark_image']);
                        if($ws_file_content){
                            $tmpfs_lib->write($ws_tmpfile,$ws_file_content);
                            $water_setting['water_mark_image'] = $tmpfs_lib->get_path($ws_tmpfile);
                            $imglib->waterMarkSetting($water_setting);
                            $imglib->waterMark();
                            $imglib->save($tmpfile);
                            $tmpfs_lib->delete($ws_tmpfile);
                        }
                    }else{
                        $imglib->waterMarkSetting($water_setting);
                        $imglib->waterMark();
                        $imglib->save($tmpfile);
                    }
                }elseif($water_setting['type'] == 2){
                    $water_setting['water_mark_type'] = 'font';
                    $water_setting['water_mark_font'] = $water_setting['water_mark_font']?ROOTDIR.'statics/font/'.$water_setting['water_mark_font']:'';
                    $imglib->waterMarkSetting($water_setting);
                    $imglib->waterMark();
                    $imglib->save($tmpfile);
                }
            }

            $imglib->close();
            
            if( $storlib->upload($filepath,$tmpfile)){
                $arr['album_id'] = $album_id;
                $arr['path'] = $filepath;
                $arr['thumb'] = $thumbpath;
                if($new_photo){
                    $arr['name'] = isset($photo_info['name'])?$photo_info['name']:$filepure;
                    $arr['create_time'] = time();
                    $arr['create_y'] = date('Y');
                    $arr['create_m'] = date('n');
                    $arr['create_d'] = date('j');
                }

                $arr = $arr+$photo_info;
                //move thumb img
                $storlib->upload($thumbpath,$tmpfile_thumb);

                if($new_photo){
                    $photo_id = $this->save($arr);
                    if(!$photo_id){
                        $storlib->delete($filepath);
                        $storlib->delete($thumbpath);
                    }
                }else{
                    $photo_id = $photo_info['id'];
                    $result = $this->update($photo_id,$arr);
                }

                //remove tmp files
                $tmpfs_lib->delete($tmpfile,true);
                $tmpfs_lib->delete($tmpfile_thumb,true);
                
                $plugin =& Loader::lib('plugin');
                $plugin->trigger('uploaded_photo',$photo_id);
                return $photo_id;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    function rotate_photo($id,$degree){
        $tmpfslib =& loader::lib('tmpfs');
        $storlib =& loader::lib('storage');
        $photo_info = $this->get_info($id);
        
        $path = $photo_info['path'];
        //将存储的图片读取到临时文件
        $tmpfile = time().rand(1000,9999).'.'.file_ext($path);
        $tmpfslib->write($tmpfile,$storlib->read($path));
        $tmpfilepath = $tmpfslib->get_path($tmpfile);
        $thumbtmpfilepath = $tmpfilepath.'.thumb.tmp';
        
        $imglib =& loader::lib('image');
        $imglib->load($tmpfilepath);
        $imglib->rotate(intval($degree));
        $imglib->save($tmpfilepath);
        $data['width'] = $imglib->getWidth();
        $data['height'] = $imglib->getHeight();
        
        
        $setting =& Loader::model('setting');
        $thumb_width = $setting->get_conf('upload.thumb_width',180);
        $thumb_height = $setting->get_conf('upload.thumb_height',180);
        if($data['width'] > $thumb_width || $data['height'] > $thumb_height){
            if($setting->get_conf('upload.enable_thumb_cut',false)){
                $imglib->resizeCut($thumb_width,$resizeCut);//裁剪
            }else{
                $imglib->resizeScale($thumb_width,$thumb_height);
            }
            $imglib->save($thumbtmpfilepath);
        }else{
            @copy($tmpfile,$thumbtmpfilepath);
        }
        $imglib->close();

        $storlib->upload($photo_info['path'] , $tmpfilepath);
        $storlib->upload($photo_info['thumb'] , $thumbtmpfilepath);

        $this->update($id,$data);

        $tmpfslib->delete($tmpfilepath);
        $tmpfslib->delete($thumbtmpfilepath);

        if($photo_info['is_cover']){
            $mdl_album =& Loader::model('album');
            $mdl_album->set_cover($id);
        }

        return true;
    }

    function add_hit($id){
        return $this->update($id,array('hits'=>new DB_Expr('hits+1')));
    }
    
    function update_comments_num($id){
        $this->db->select('#@comments','count(id)','ref_id='.intval($id).' and status=1 and type=2');
        $arr['comments_num'] = $this->db->getOne();
        return $this->update($id,$arr);
    }

    function get_photo_by_name_aid($aid,$name){
        $this->db->select('#@photos','*','album_id='.intval($aid).' and name='.$this->db->q_str($name));
        return $this->db->getRow();
    }
    
    
    function save_extra($id,$extra){
        if(is_array($extra)){
            foreach($extra as $k => $v){
                $this->db->select('#@photometa','meta_value','photo_id='.intval($id).' and meta_key='.$this->db->q_str($k));
                $row = $this->db->getRow();
                if($row){
                    $this->db->update('#@photometa','photo_id='.intval($id).' and meta_key='.$this->db->q_str($k),array('meta_value'=>$v));
                }else{
                    $this->db->insert('#@photometa',array('photo_id'=>intval($id),'meta_key'=>$k,'meta_value'=>$v));
                }
                $this->db->query();
            }
        }
    }
    
    function get_extra($id){
        if($value){
            return $value;
        }
        $this->db->select('#@photometa','meta_key,meta_value','photo_id='.intval($id));
        $value = $this->db->getAssoc();
        return $value;
    }

    function update_by_aid($aid,$arr){
        $this->db->update($this->table_name,'album_id='.intval($aid),$arr);

        return $this->db->query();
    }
}