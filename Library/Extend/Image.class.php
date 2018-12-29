<?php
/**
*    图片处理类Image.class V3.0
*    修改时间2017年2月10日15:48:27
*/
class Image{
    private $length = 4;//验证码长度
    private $im;
    private $bgcolor;
    private $red;
    private $width = 60;
    private $height = 25;
    private $session;
    public function __construct(){
        
    }
    /**
     * 图片初始化成员方法，启动session
     */
    private function imageinit(){
        session_start();
        $this->im = imagecreatetruecolor($this->width, $this->height);
        $this->bgcolor = imagecolorallocate($this->im,255,0,0);
        $this->red = imagecolorallocate($this->im, 255, 255, 0);
        imagefill($this->im,0,0,$this->bgcolor);
    }
    
    /**
     * 释放图片资源成员方法，收集session
     */
    private function imageclose(){
        for($m=1;$m<=5;$m++){//随机横线
            imageline($this->im, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), imagecolorallocate($this->im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
        }
        for($n=1;$n<=8*$this->length;$n++){//随机点
            imagesetpixel($this->im, mt_rand(0, $this->width), mt_rand(0, $this->height), imagecolorallocate($this->im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
        }
        imagegif($this->im);
        imagedestroy($this->im);
        if(!empty($this->session)){
            $_SESSION['verify'] = $this->session;
        }
    }
    /**
     * 验证码
     * $length是字母长度
     * $status值：1是大写字母和数字组合(全是大写),2是字母和数字组合(大写和小写)，3是字母(大写)，4是字母(小写),5是字母(大写和小写)，6是纯数字
     */
    public function verify($length='',$status=6){
        if(!empty($length)){
            $this->length = $length;
        }
        $this->width = 5+($this->length*13);
        $this->height = 25;
        $this->imageinit();//初始化图片
        for ($i=1;$i<=$this->length;$i++){//随机数字
            switch ($status){
                case 1:
                    $state = mt_rand(1,2);
                    switch ($state){
                        case 1:
                            $rand_string = mt_rand(0,9);
                            break;
                        case 2:
                            $rand_string = chr(mt_rand(65,90));
                            break;
                    }
                break;
                case 2:
                    $state = mt_rand(1,3);
                    switch ($state){
                        case 1:
                            $rand_string = mt_rand(0,9);
                            break;
                        case 2:
                            $rand_string = chr(mt_rand(65,90));
                            break;
                        case 3:
                            $rand_string = chr(mt_rand(97,122));
                            break;
                    }                
                    break;
                case 3:
                    $rand_string = chr(mt_rand(65,90));
                    break;
                case 4:
                    $rand_string = chr(mt_rand(97,122));
                    break;
                case 5:
                    $state = mt_rand(1,2);
                    switch ($state){
                        case 1:
                            $rand_string = chr(mt_rand(65,90));
                            break;
                        case 2:
                            $rand_string = chr(mt_rand(97,122));
                            break;
                    }
                    break;
                case 6:
                    $rand_string = mt_rand(0,9);
                    break;
            }
            $this->session .= $rand_string;
            imagestring($this->im, 5, (12*$i)-5,5, $rand_string, $this->red);
        }
        $this->imageclose();//释放图片资源
    }
    
    /**
     * 缩略图
     * @param string $big_src 大图路径
     * @param string $thum_w 缩略图宽度
     * @param string $thum_h 缩略图高度
     * @param string $thum_src 缩略图路径
     */
    public function thum($big_src,$thum_w,$thum_h,$thum_src=''){
        $imagesize = getimagesize($big_src);
        $imagecreate = $this->imagecreatefromtype($imagesize[2]);
        $big_img =$imagecreate['createfrom']($big_src);
        list($big_w,$big_h) = $imagesize;
        if($big_w/$big_h-$thum_w/$thum_h<0){
            $height = $thum_h;
            $width = round(($thum_h/$big_h)*$big_w);
        }elseif($big_w/$big_h-$thum_w/$thum_h==0){
            $height = $thum_h;
            $width = $thum_w;
        }elseif($big_w/$big_h-$thum_w/$thum_h>0){
            $width = $thum_w;
            $height = round(($thum_w/$big_w)*$big_h);
        }
        $thum_img = imagecreatetruecolor($width, $height);
        $transparency = imagecolortransparent($big_img);
        if($transparency>=0&&$transparency<imagecolorstotal($big_img)){
            $forindex = imagecolorsforindex($big_img, $transparency);
            $bg_color = imagecolorallocate($thum_img, $forindex['red'], $forindex['green'], $forindex['blue']);
            imagefill($thum_img, 0, 0, $bg_color);
            imagecolortransparent($thum_img,$bg_color);
        }
        imagecopyresized($thum_img, $big_img, 0, 0, 0, 0, $width, $height, $big_w, $big_h);
        $thum_img_url = '';
        if(!empty($thum_src)){
            $extension_name = strrchr($thum_src,'.');
            $extension_name = ltrim($extension_name,'.');
            if($extension_name=='png'){
                imagepng($thum_img,$thum_src);
            }
            if($extension_name=='jpg'){
                imagejpeg($thum_img,$thum_src);
            }
            if($extension_name=='gif'){
                imagegif($thum_img,$thum_src);
            }
            imagedestroy($big_img);
            imagedestroy($thum_img);
            $thum_img_url = $thum_src;
        }else{
            $thum_img_url = $thum_img;
        }
        return $thum_img_url;
    }
    private function imagecreatefromtype($type){
        //1 = GIF，2 = JPG，3 = PNG，
        switch ($type){
            case 1:
                $image['createfrom'] = 'imagecreatefromgif';
                $image['create'] = 'imagegif';
                break;;
            case 2:
                $image['createfrom'] = 'imagecreatefromjpeg';
                $image['create'] = 'imagejpeg';
                break;
            case 3:
                $image['createfrom'] = 'imagecreatefrompng';
                $image['create'] = 'imagepng';
                break;
        }
        return $image;
    }
    /**
     * 水印
     * @param string $big_src 大图地址
     * @param string $water_src 水印图片地址
     * @param string $dst_src 目标图片地址
     * @param int $place 水印位置 1左上角，2又上角，3正中间，4左下角，5右下角
     * @param string $source 水印图片的资源
     */
    public function watermark($big_src,$water_src='',$dst_src,$place=3,$source=''){
        $imagesize_big = getimagesize($big_src);
        $imagecreate_big = $this->imagecreatefromtype($imagesize_big[2]);
        $big_img =$imagecreate_big['createfrom']($big_src);    
        if(empty($source)){
            $imagesize_water = getimagesize($water_src);
            $imagecreate_water = $this->imagecreatefromtype($imagesize_water[2]);
            $water_img =$imagecreate_water['createfrom']($water_src);
        }else{
            $water_img = $source;
            $imagesize_water[0] = imagesx($water_img);
            $imagesize_water[1] = imagesy($water_img);
        }
        $wall = 10;//下面的位置，统统都距离周边10px
        if($place==1){//左上角
            $water_x = $wall;
            $water_y = $wall;
        }elseif($place==2){//又上角
            $water_x = $imagesize_big[0]-$imagesize_water[0]-$wall;
            $water_y = $wall;
        }elseif($place==3){//正中间
            $water_x = $imagesize_big[0]/2-$imagesize_water[0]/2;
            $water_y = $imagesize_big[1]/2-$imagesize_water[1]/2;
        }elseif($place==4){//左下角
            $water_x = $wall;
            $water_y = $imagesize_big[1]-$imagesize_water[1]-$wall;
        }elseif($place==5){//又下角
            $water_x = $imagesize_big[0]-$imagesize_water[0]-$wall;
            $water_y = $imagesize_big[1]-$imagesize_water[1]-$wall;
        }
        imagecopy($big_img, $water_img, $water_x, $water_y, 0, 0, $imagesize_water[0], $imagesize_water[1]);
        $imagecreate_big['create']($big_img,$dst_src);
        imagedestroy($big_img);
        imagedestroy($water_img);
    }
/**
 * 根据上传的图片类型返回后缀名
 * $imageType ：上传的图片类型，例如：$_FILES['file']['type']
 */
    public function imageSuffix($imageType){
        $suffix = '';
        switch($imageType){
            case 'image/jpeg':
                $suffix = 'jpg';
                break;
            case 'image/png':
                $suffix = 'png';
                break;
            case 'image/gif':
                $suffix = 'gif';
                break;
        }
        if(!empty($suffix)){
            return $suffix;
        }else{
            return false;
        }
    }
    
}
// header('content-type:image/gif');
// $image = new Image();
// $image->verify(4,1);
//  $img = new Image();
//  $img->thum('logo.png', 500,500,'logo111.png');
//header('content-type:image/jpg');
// $img = new Image();
// $img->watermark('b.jpg','logo111.gif','bbb.jpg',5);
