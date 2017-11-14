<?php  






    /**
     * @param null $src_path图片路径
     * @param null $save_path保存路径
     * @param null $width图片宽
     * @param null $height图片高
     * @param null $orientation是否选择图片
     * @return bool
     */
    public function image_size($src_path = null,$save_path = null,$width= null,$height= null,$orientation = null){
//
//        ini_set('memory_limit', '-1');
//        set_time_limit(0);
        //源图的路径，可以是本地文件，也可以是远程图片
        if (empty($src_path)) {
            return true;
        }
        if (empty($width) && empty($height)) {
            //最终保存图片的宽
            $width = 120;
            //最终保存图片的高
            $height = 160;
        }
        //源图对象
        $src_image = imagecreatefromstring(file_get_contents($src_path));
        //获取图片信息判断图片是否需要旋转
        $exif = exif_read_data($_FILES['image_upload']['tmp_name']);
        if (file_exists($src_path) || exif_imagetype($src_path) == 2) {
            $exif = exif_read_data($src_path,'EXIF', 0);
            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 8:
                        $src_image = imagerotate($src_image,90,0);
                        break;
                    case 3:
                        $src_image = imagerotate($src_image,180,0);
                        break;
                    case 6:
                        $src_image = imagerotate($src_image,-90,0);
                        break;
                }
            }
        }
        //根据传参来判断图片是否需要旋转
//        if ($orientation === 'landscape-left') {
//            $src_image = imagerotate($src_image,-90,0);
//        }

        $src_width = imagesx($src_image);//获取图片的宽
        $src_height = imagesy($src_image);//获取图片的高
        //生成等比例的缩略图
        $tmp_image_width = 0;
        $tmp_image_height = 0;
        if ($src_width / $src_height >= $width / $height) {//图片高大于等于宽
            $tmp_image_width = $width;
            $tmp_image_height = round($tmp_image_width * $src_height / $src_width);
        } else {//图片宽大于高
            $tmp_image_height = $height;
            $tmp_image_width = round($tmp_image_height * $src_width / $src_height);
        }

        $tmpImage = imagecreatetruecolor($tmp_image_width, $tmp_image_height);
        imagecopyresampled($tmpImage, $src_image, 0, 0, 0, 0, $tmp_image_width, $tmp_image_height, $src_width, $src_height);

        //添加白边
        $final_image = imagecreatetruecolor($width, $height);
        //这一句一定要有
        imagesavealpha($final_image,true);
        //拾取一个完全透明的颜色，最后一个参数为127为完全透明
        $color = imagecolorallocatealpha($final_image, 255, 255, 255,127);
        imagefill($final_image, 0, 0, $color);
        $x = round(($width - $tmp_image_width) / 2);
        $y = round(($height - $tmp_image_height) / 2);
        imagecopy($final_image, $tmpImage, $x, $y, 0, 0, $tmp_image_width, $tmp_image_height);
        //输出图片
        header('Content-Type: image/png');
//        imagejpeg($final_image,null,100);
        if (empty($save_path)) {
            $timestamp = time().'AD001';
            $save_path = ROOT_DIR."/public/$timestamp.png";
        }
        imagepng($final_image, $save_path, 50);
        imagedestroy($final_image);
    }





    /**
     * [cut_img 图片缩放加白边]
     * Author: vincent
     * @$imgs  str $imgs    图片路径数组
     * @param  array $info    图片宽高数组array('width','height')
     * @param  bool $cover    是否覆盖原图，默认不覆盖
     * @return array          若覆盖原图返回裁剪数量，不覆盖返回图片路径组成的数组
     * @image_path array      保存路径
     */
    function zoom_img($file='',$infoarr=array(500,500),$image_path)
    {

        //如果不覆盖原图，可重新定义图片保存路径
//        $file = $imgs;
//        if(false==$cover){
//        } else {
//            $file = $image_path;
//        }
        //要保存的宽
        $saveWidth = $infoarr[0];

        //要保存的高
        $saveHeight = $infoarr[1];

        //判断图片是否存在
        if(!file_exists($file)) {
            return false;
        }

        //获取图片信息
        $imgize = getimagesize($file);

        //图片宽度
        $width = $imgize[0];
        //图片高度
        $height = $imgize[1];

        //原宽高比
        $ratio = $width/$height;

        //判断图片原宽高比与裁剪宽高比的大小
        if($width>=$height){
            $height = $saveWidth/$ratio;
            $width = $saveWidth;
        }else{
            $width = $saveHeight*$ratio;
            $height = $saveHeight;
        }

        //创建源图的实例
        $src = imagecreatefromstring(file_get_contents($file));

        if(false!=$src){
            //创建图像
            $final_image = imagecreatetruecolor($saveWidth, $saveHeight);
            //定义颜色
            $color = imagecolorallocate($final_image, 255, 255, 255);
            //定义为透明色
            imagecolortransparent($final_image,$color);
            //填充
            imagefill($final_image, 0, 0, $color);
            $x = round(($saveWidth - $width) / 2);
            $y = round(($saveHeight - $height) / 2);
            imagecopyresized($final_image, $src, $x, $y, 0, 0, $width, $height,$imgize[0],$imgize[1]);
            //保存
            imagepng($final_image,$image_path);
            imagedestroy($final_image);
            imagedestroy($src);
        }
        return $image_path;
    }
?>