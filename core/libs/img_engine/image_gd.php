<?php
/**
 * $Id: image_gd.php 414 2012-10-25 05:12:48Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */

class image_gd {
    /**
     * 图片文件句柄
     *
     * @var image
     */
    var $image;

    /**
     * 图片类型
     *
     * @var imagetype
     */
    var $image_type;
    
    var $image_quality=90;

    var $true_color = false;
    
    /**
     * 装载图像
     *
     * @param string $filename 文件完整路径
     * @return void
     */
    function load($filename) {
        $image_info = @getimagesize($filename);
        $this->image_type = $image_info[2];
        if( $this->image_type == IMAGETYPE_JPEG ) {
            $this->image = @imagecreatefromjpeg($filename);
        } elseif( $this->image_type == IMAGETYPE_GIF ) {
            $this->image = @imagecreatefromgif($filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) {
            $this->image = @imagecreatefrompng($filename);
        }else{
            return false;
        }
        if(function_exists("imagecopyresampled") && function_exists("imagecreatetruecolor") && $this->image_type != IMAGETYPE_GIF){
            $this->true_color = true;
        }
        if($this->image){
            return true;
        }
        return false;
    }
    
    function supportType(){
        return array('jpg','jpeg','gif','png');
    }
    
    function setQuality($q){
        if($q>0)
            $this->image_quality = $q;
    }
    /**
     * 返回扩展名
     * 
     * @return string 扩展名
     */
    function getExtension(){
        if( $this->image_type == IMAGETYPE_JPEG ) return 'jpg';
        elseif( $this->image_type == IMAGETYPE_GIF ) return 'gif';
        elseif( $this->image_type == IMAGETYPE_PNG ) return 'png';
    }

    /**
     * 将图形对象保存成文件
     *
     * @param string $filename 文件名
     * @param int $image_type 文件类型
     * return volid
     */
    function save($filename) {
        $image_type = $this->image_type;
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image,$filename,$this->image_quality);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif($this->image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng($this->image,$filename);
        }

    }
    
    /**
     * 将图像输出到数据流
     *
     * @param int $image_type 文件类型
     * @return void
     */
    function output() {
        $image_type = $this->image_type;
        if( $image_type == IMAGETYPE_JPEG ) {
            header('Content-Type: image/jpeg');
            imagejpeg($this->image,NULL,$this->image_quality);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            header('Content-type: image/gif');
            imagegif($this->image);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            header('Content-type: image/png');
            imagepng($this->image);
        }
    }

    /**
     * 获得图像宽度
     *
     * @return int 图像宽度
     */
    function getWidth() {
        return imagesx($this->image);
    }

    /**
     * 获得图像高度
     *
     * @return int 图像高度
     */
    function getHeight() {
        return imagesy($this->image);
    }

    /**
     * 等比例缩小到指定高度
     * 
     * @param int $height 指定高度
     */
    function resizeToHeight($height) {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
    }

    /**
     * 缩小到指定尺寸
     * 
     * @param int $w 指定宽度
     * @param int $h 指定高度
     */
    function resizeTo($w=0, $h=0) {
        if($w>0 && $h>0) return $this->resize($w,$h);
        else if($w>0) return $this->resizeToWidth($w);
        else if($h>0) return $this->resizeToHeight($h);
    }
    /**
     * 指定最大宽度和最大高度
     * @param int $w 最大宽度
      * @param int $h 最大高度
     */
    function resizeScale($w=0,$h=0){
        if($w == 0 && $h>0){
            return $this->resizeToHeight($h);
        }
        if($h == 0 && $w>0){
            return $this->resizeToWidth($w);
        }
        if($w == 0 && $h==0){
            return false;
        }
        $maxwidth = $w;
        $maxheight = $h;
        
        $width = $this->getWidth();
        $height = $this->getHeight();
        
        $RESIZEWIDTH = $RESIZEHEIGHT = false;
        if($maxwidth && $width > $maxwidth){
            $widthratio = $maxwidth/$width;
            $RESIZEWIDTH=true;
        }
        if($maxheight && $height > $maxheight){
            $heightratio = $maxheight/$height;
            $RESIZEHEIGHT=true;
        }
        if($RESIZEWIDTH && $RESIZEHEIGHT){
            if($widthratio < $heightratio){
                return $this->resizeToWidth($w);
            }else{
                return $this->resizeToHeight($h);
            }
        }elseif($RESIZEWIDTH){
            return $this->resizeToWidth($w);
        }elseif($RESIZEHEIGHT){
            return $this->resizeToHeight($h);
        }
    }
    /**
     * 等比例缩小到指定宽度，并切成方形
     * 
     * @param int $v 指定宽度/高度
     */
    function square($v){
        $width = $this->getWidth();
        $height = $this->getHeight();
        $left = 0;
        $top = 0;
        if($width>$height){
            $this->resizeToHeight($v);
            $left = ceil(($v/$height * $width - $v)/2); 
        }else{
            $this->resizeToWidth($v);
            $top = ceil(($v/$width * $height - $v)/2); 
        }
        $this->cut($v,$v,$left,$top);
    }

    //缩小并剪切
    function resizeCut($w,$h){
        $left = 0;
        $top = 0;

        $width = $this->getWidth();
        $height = $this->getHeight();

        if($w == $width && $h == $height){
            return true;
        }

        $ratio_o = $width/$height;
        $ratio_t = $w/$h;

        if($width>$w && $height > $h){
            if($ratio_o == $ratio_t){
                return $this->resizeTo($w,$h);
            }

            if($ratio_o > $ratio_t){
                $this->resizeToHeight($h);
                $left = ceil(($h/$height * $width - $w)/2);
            }else{
                $this->resizeToWidth($w);
                $top = ceil(($w/$width * $height - $h)/2);
            }
            return $this->cut($w,$h,$left,$top);
        }elseif($width<$w && $height < $h){
            return true;
        }else{
            if($width < $w){
                $top = ceil(($height - $h)/2);
                return $this->cut($width,$h,$left,$top);
            }else{
                $left = ceil(($width - $w)/2);
                return $this->cut($w,$height,$left,$top);
            }
        }
    }
    /**
     * 等比例缩小到指定宽度
     * 
     * @param int $width 指定宽度
     */
    function resizeToWidth($width) {
        if($width>=$this->getWidth()) return;
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width,$height);
    }

    /**
     * 维持宽高比缩小指定比例
     * 
     * @param int $scale 指定比例
     */
    function scale($scale) {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getHeight() * $scale/100;
        $this->resize($width,$height);
    }

    /**
     * 改变图像尺寸
     * 
     * @param int $width 指定宽度
     * @param int $height 指定高度
     */
    function resize($width,$height) {
        if($this->true_color){
            $newim = imagecreatetruecolor($width, $height);
            imagecopyresampled($newim, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        }else{
            $newim = imagecreate($width, $height);
            imagecopyresized($newim, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        }
        imagedestroy($this->image);
        $this->image = $newim;
    }

    /**
     * 裁剪图像
     *
     * @param int $width 指定宽度
     * @param int $height 指定高度
     */
    function cut($width,$height,$left = 0,$top = 0){
        if($this->true_color){
            $new_image = imagecreatetruecolor($width, $height);
        }else{
            $new_image = imagecreate($width, $height);
        }
        imagecopy($new_image, $this->image, 0, 0, $left, $top, $width, $height);

        imagedestroy($this->image);
        $this->image = $new_image;
    }

    /**
     * 截取从某纵向位置开始指定高度的图像
     *
     * @param int $top 指定位置
     * @param int $height 指定高度
     */
    function vcut($top,$height){
        $width = $this->getWidth();
        $height = $this->getHeight()-$top+$height;
        if($height<200) return;
        if($this->true_color){
            $new_image = imagecreatetruecolor($width, $height);
        }else{
            $new_image = imagecreate($width, $height);
        }
        imagecopy($new_image, $this->image, 0, 0, 0, $top, $width, $height);

        imagedestroy($this->image);
        $this->image = $new_image;
    }
    
    /*
    旋转图片
    */
    function rotate($dgree){
        if(function_exists('imagerotate')){
            $tran = imagecolortransparent($this->image,NULL);
            $new_image = imagerotate($this->image, $dgree , $tran);

            imagedestroy($this->image);
            $this->image = $new_image;
        }
    }
    
    function waterMarkSetting($param){
        $this->param = $param;
    }
    
    function waterMarkImg(){
        if(empty($this->param['water_mark_image']) || !file_exists($this->param['water_mark_image'])){
            return false;
        }
    
        $water_info = getimagesize($this->param['water_mark_image']);
        $w = $water_info[0];//取得水印图片的宽
        $h = $water_info[1];//取得水印图片的高
        switch($water_info[2])//取得水印图片的格式
        {
            case 1:$water_im = imagecreatefromgif($this->param['water_mark_image']);break;
            case 2:$water_im = imagecreatefromjpeg($this->param['water_mark_image']);break;
            case 3:$water_im = imagecreatefrompng($this->param['water_mark_image']);break;
            default:return false;
        }
        $ground_w = $this->getWidth();
        $ground_h = $this->getHeight();
    
        //if( $ground_w<$w || $ground_h<$h ){
        //    return false;
        //}

        switch($this->param['water_mark_pos'])
        {
            case 0://随机
            $posX = rand(5,($ground_w - $w - 5));
            $posY = rand(5,($ground_h - $h - 5));
            break;
            case 1://1为顶端居左
            $posX = 5;
            $posY = 5;
            break;
            case 2://2为顶端居中
            $posX = ($ground_w - $w) / 2;
            $posY = 5;
            break;
            case 3://3为顶端居右
            $posX = $ground_w - $w -5;
            $posY = 5;
            break;
            case 4://4为中部居左
            $posX = 5;
            $posY = ($ground_h - $h) / 2;
            break;
            case 5://5为中部居中
            $posX = ($ground_w - $w) / 2;
            $posY = ($ground_h - $h) / 2;
            break;
            case 6://6为中部居右
            $posX = $ground_w - $w - 5;
            $posY = ($ground_h - $h) / 2;
            break;
            case 7://7为底端居左
            $posX = 5;
            $posY = $ground_h - $h - 5;
            break;
            case 8://8为底端居中
            $posX = ($ground_w - $w) / 2;
            $posY = $ground_h - $h - 5;
            break;
            case 9://9为底端居右
            $posX = $ground_w - $w - 5;
            $posY = $ground_h - $h - 5;
            break;
            default://随机
            $posX = rand(5,($ground_w - $w - 5));
            $posY = rand(5,($ground_h - $h - 5));
            break;
        }
        //设定图像的混色模式
        imagealphablending($this->image, true);
        if(function_exists('imagecopymerge') && $this->param['water_mark_opacity'] != 0){
            @imagecopymerge($this->image, $water_im, $posX, $posY, 0, 0, $w,$h,$this->param['water_mark_opacity']);
        }else{
            imagecopy($this->image, $water_im, $posX, $posY, 0, 0, $w,$h);//拷贝水印到目标文件
        }
        imagedestroy($water_im);
    }
    
    function waterMarkFont(){
        if($this->param['water_mark_color']){
            $color = $this->param['water_mark_color'];
        }else{
            $color = '#000000';
        }
        $r = hexdec( substr( $color, 1, 2 ) );
        $g = hexdec( substr( $color, 3, 2 ) );
        $b = hexdec( substr( $color, 5, 2 ) );
        
        if($this->param['water_mark_opacity']>0 && $this->param['water_mark_opacity']<100){
            $fontcolor = imagecolorallocatealpha( $this->image, $r, $g, $b ,$this->param['water_mark_opacity']);
        }else{
            $fontcolor = imagecolorallocate( $this->image, $r, $g, $b );
        }
        
        $box = ImageTTFBBox(
            $this->param['water_mark_fontsize'],
            $this->param['water_mark_angle'],
            $this->param['water_mark_font'],
            $this->param['water_mark_string']);
        $ground_w = $this->getWidth();
        $ground_h = $this->getHeight();
        $h = max($box[1], $box[3]) - min($box[5], $box[7]);
        $w = max($box[2], $box[4]) - min($box[0], $box[6]);
        $ax = min($box[0], $box[6]) * -1;
        $ay = min($box[5], $box[7]) * -1;
        switch($this->param['water_mark_pos'])
        {
            case 0://随机
            $posX = rand(5,($ground_w - $w - 5));
            $posY = rand(5,($ground_h - $h - 5));
            break;
            case 1://1为顶端居左
            $posX = 5;
            $posY = 5;
            break;
            case 2://2为顶端居中
            $posX = ($ground_w - $w) / 2;
            $posY = 5;
            break;
            case 3://3为顶端居右
            $posX = $ground_w - $w -5;
            $posY = 5;
            break;
            case 4://4为中部居左
            $posX = 5;
            $posY = ($ground_h - $h) / 2;
            break;
            case 5://5为中部居中
            $posX = ($ground_w - $w) / 2;
            $posY = ($ground_h - $h) / 2;
            break;
            case 6://6为中部居右
            $posX = $ground_w - $w - 5;
            $posY = ($ground_h - $h) / 2;
            break;
            case 7://7为底端居左
            $posX = 5;
            $posY = $ground_h - $h - 5;
            break;
            case 8://8为底端居中
            $posX = ($ground_w - $w) / 2;
            $posY = $ground_h - $h - 5;
            break;
            case 9://9为底端居右
            $posX = $ground_w - $w - 5;
            $posY = $ground_h - $h - 5;
            break;
            default://随机
            $posX = rand(5,($ground_w - $w - 5));
            $posY = rand(5,($ground_h - $h - 5));
            break;
        }
        
        imagettftext($this->image,
             $this->param['water_mark_fontsize'],
             $this->param['water_mark_angle'],
             $posX + $ax,
             $posY + $ay,
             $fontcolor,
             $this->param['water_mark_font'],
             $this->param['water_mark_string']);
    }
    
    function waterMark(){
        //读取水印文件
        if($this->param['water_mark_type'] == 'image'){
            $this->waterMarkImg();
        }elseif($this->param['water_mark_type'] == 'font'){
            $this->watermarkFont();
        }
        return false;
    }

    function close(){
        imagedestroy($this->image);
    }
}
