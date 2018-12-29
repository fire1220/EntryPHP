<?php
/**
 *  s3_upload 图 片 操 作 类
 * 引入的类文件.
 * include_once 's3img/s3_upload.php';
 * $a = new s3_upload();
 * $b = $a->show($_FILES['img']);
 * $md = md5(time());
 * $d = $a->save($md);
 * echo '<pre>';
 * print_r($d);
 * echo '</pre>';
 * 
*/

class s3_upload
{

	var $allowext = 'jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG';

	var $domain = 's3img.hlj.sina.com.cn';

	var $accesskey = 'SINA0000000000DFZHLJ';

	var $secretkey = 'i8JGihca3M5r3X3pQpjFn8595tkzqJAFQbMyiLyt';

	private $domain_url = "http://s3img.hlj.sina.com.cn/";

	/**
	 * 构 造 函 数
	 *
	 * @author zhuayi
	 */
	function __construct()
	{
		$this->temp = ini_get('upload_tmp_dir');

		if (!file_exists('SinaStorageService.php') || !file_exists('SinaService.php'))
		{
			
			include_once 'SinaStorageService.php';

			include_once 'SinaService/SinaService.php';

		}
		else
		{
			throw new Exception("缺少文件 SinaStorageService 或 SinaService.php");	
		}
	}

	/**
	 * 获取图片二进制数据
	 *
	 * @author zhuayi
	 */
	function show($file)
	{
		$this->width = $this->height =  $this->type = '';

		if (is_array($file))
		{
			$filename = $file['tmp_name'];
			$this->h = trim(substr(strrchr(strtolower($file['name']),'.'),1,100));
		}
		else
		{
			$filename = preg_replace('#\?(.*)|&(.*)|#','',$file);

			/* 取文件后缀 */
			$this->h = trim(substr(strrchr(strtolower($filename),'.'),1,100));
		}

		/* 第一层简单判断后缀 */
		$upload_allowext = explode('|',$this->allowext);
		
		if (!in_array($this->h,$upload_allowext))
		{
			throw new Exception("图片格式不正确!", 1);
		}

		/* 下载文件数据,并判断是否图片 */
		$this->get_file_data($filename);

		/* 获取文件数据 */
		return $this;
	}

	function get_file_data($filename)
	{

		$opts = array(
						'http'=>array('method'=>"GET",'timeout'=>20)  
					);  
		  
		$context = stream_context_create($opts); 
		$this->file_data = file_get_contents($filename,false, $context);

		if (empty($this->file_data) || $this->file_data === false)
		{
			throw new Exception("图片下载失败", 1);
		}

		$this->filename = tempnam($this->temp,'zhuayi');
		
		if (empty($this->filename))
		{
			throw new Exception("创建临时文件失败", 1);
		}

		/*  把图片写入到临时文件 */
		file_put_contents($this->filename,$this->file_data);

		/* 取文件信息 */
		$reset = $this->info();

		/* 获取文件长度 */
		$this->size = abs(filesize($this->filename));
		$this->type = $reset['mime'];
		$this->width = $reset[0];
		$this->height = $reset[1];

		switch($reset[2])
		{ 
			case 1:
			$this->h = '.gif';
			break; 
			case 2:
			$this->h = '.jpg';
			break; 
			case 3:
			$this->h = '.png';
			break; 
			default:
			throw new Exception("图片格式不正确", 1);
			
			break;
		}


	}


	/**
	 * 获 取 图 片 信 息
	 *
	 * @author zhuayi
	 */
	function info($filename = '')
	{
		if (empty($filename))
		{
			return getimagesize($this->filename);
		}
		else
		{
			return getimagesize($filename);
		}
	}

	/**
	 * 返回图像资源句柄
	 *
	 * @author zhuayi
	 */
	function create($type,$filename)
	{
		switch($type)
		{ 
			case 1:
			return imagecreatefromgif($filename);
			break; 
			case 2:
			return imagecreatefromjpeg($filename);
			break; 
			case 3:
			return imagecreatefrompng($filename);
			break; 
			default:
			return -1;
		}
	}

	/**
	 * zoom 缩 放 图 片
	 *
	 * @author zhuayi
	 */
	function zomm($width = 0,$height = 0)
	{
		$info = $this->info();
		
		$x  = $y = 0;
		
		if ($width >0 && $height >0)
		{
			$max_width = $width;
			$max_height = $height;
			
			/* 先判断图片事横的还事树的 */
			if ($info['0'] > $info[1])
			{
				/* 横的 */
				$_height = intval($info['1']*$width/$info['0']);
				$y = ($height - $_height)/2;
				$height = $_height;
			}
			else
			{

				$height = intval($info['1']* $width/$info['0']);

			}
			
		}
		elseif ($width < $info['0'] && $width > 0 && $height == 0)
		{
			$height = intval($info['1']*$width/$info['0']);
			$max_width = $width;
			$max_height = $height;
		}
		elseif ($width > $info['0'] && $width > 0 && $height == 0)
		{
			$x = intval(($width-$info['0'])/2);
			$height = $info['1'];
			$max_width = $width;
			$max_height = $height;
			$width = $info['0'];
			
		}
		elseif ($height < $info['0'] && $width==0)
		{
			$width = intval($info['0']*$height/$info['1']);
			$max_width = $width;
			$max_height = $height;
		}
		else
		{
			$this->width = $max_width = $width = $info[0];
			$this->height = $max_height = $height= $info[1];
		}

		$this->width = $max_width;
		$this->height = $max_height;
		
		$image = $this->create($info[2],$this->filename);
		
		if ($image  == '-1')
		{
			throw new Exception("图片格式错误了", 1);
		}

		$image_p = imagecreatetruecolor($max_width, $max_height);
		$color = imagecolorAllocate($image_p,255,255,255);
		imagefill($image_p,0,0,$color);
		imagecopyresampled($image_p, $image, $x, $y, 0, 0, $width, $height, $info[0], $info[1]);

		$this->save_temp($image_p,$info['2']);

		return $this;
	}

	

	/**
	 * magicwand zoom 缩 放 图 片
	 *
	 * @author zhuayi
	 */
	function magicwand_zomm($width = 0,$height = 0)
	{
		
		$x  = $y = 0;
		
		if (function_exists("NewMagickWand"))
		{
			
			$mymagickwand = NewMagickWand();
			if (!MagickReadImage($mymagickwand, $this->filename))
			{
				return false;
			}
			
			$info['0'] = MagickGetImageWidth($mymagickwand);
	   	 	$info['1'] = MagickGetImageHeight($mymagickwand);
			
	   	 	
	   	 	
	   	 	if ($width >0 && $height >0)
			{
				$max_width = $width;
				$max_height = $height;
				
				/* 先判断图片事横的还事树的 */
				if ($info['0'] > $info[1])
				{
					/* 横的 */
					$_height = intval($info['1']*$width/$info['0']);
					$y = ($height - $_height)/2;
					$height = $_height;
				}
				else
				{
	
					$height = intval($info['1']* $width/$info['0']);
	
				}
				
			}
			elseif ($width < $info['0'] && $width > 0 && $height == 0)
			{
				$height = intval($info['1']*$width/$info['0']);
				$max_width = $width;
				$max_height = $height;
			}
			elseif ($width > $info['0'] && $width > 0 && $height == 0)
			{
				$x = intval(($width-$info['0'])/2);
				$height = $info['1'];
				$max_width = $width;
				$max_height = $height;
				$width = $info['0'];
				
			}
			elseif ($height < $info['0'] && $width==0)
			{
				$width = intval($info['0']*$height/$info['1']);
				$max_width = $width;
				$max_height = $height;
			}
			else
			{
				$this->width = $max_width = $width = $info[0];
				$this->height = $max_height = $height= $info[1];
			}
	
			$this->width = $max_width;
			$this->height = $max_height;
			
			
			//类型
			$srcT = MagickGetImageFormat($mymagickwand);
			if ($srcT == "JPEG")
			{
				$extension = "jpg";
			}
			elseif ($srcT == "GIF")
			{
				$extension = "gif";
			}
			elseif ($srcT == "PNG")
			{
				$extension = "png";
			}
			else
			{
				return false;
			}
			
			
			//建立临时文件
			$tmp_f = tempnam($_SERVER["SINASRV_CACHE_DIR"],"TMP_IMG");
		
			//生成背景图
			$bgmagickwand = NewMagickWand();
			MagickNewImage($bgmagickwand,$max_width,$max_height,$bgcolor="#ffffff");
			MagickSetFormat($bgmagickwand,$srcT);
		
			//缩放原图并合并到背景图上
			MagickScaleImage($mymagickwand, $width, $height);
			MagickCompositeImage($bgmagickwand,$mymagickwand,MW_OverCompositeOp,$x,$y);
		/*
			//处理水印图
			if ($watermark && is_file($watermark))
			{
				MagickRemoveImage($mymagickwand);
				$padding = intval($padding);
				if (MagickReadImage($mymagickwand, $watermark))
				{
					if ($position == 1)
					{
						$wmL = $padding;
						$wmT = $padding;
					}
					elseif ($position == 2)
					{
						$wmL = $Width-$padding-MagickGetImageWidth($mymagickwand);
						$wmT = $padding;
					}
					elseif ($position == 3)
					{
						$wmL = $padding;				
						$wmT = $Height-$padding-MagickGetImageHeight($mymagickwand);
					}
					else
					{
						$wmL = $Width-$padding-MagickGetImageWidth($mymagickwand);
						$wmT = $Height-$padding-MagickGetImageHeight($mymagickwand);
					}
					MagickCompositeImage($bgmagickwand,$mymagickwand,MW_OverCompositeOp,$wmL,$wmT);
				}
			}*/
		
			MagickWriteImage($bgmagickwand, $tmp_f);
			DestroyMagickWand($mymagickwand);
			DestroyMagickWand($bgmagickwand);
			
			$this->file_data = file_get_contents($tmp_f);
			$this->size = strlen($this->file_data);
			$this->h = ".".$extension;
			unlink($tmp_f);
			unlink($this->filename);
		}
		
		
		return $this;
	}
	
	
	/**
	 * save_temp pic
	 *
	 * @author zhuayi
	 */
	function save_temp($image_p,$type)
	{
		switch($type)
		{ 
			case 1:
			imagegif($image_p, $this->filename,90);
			break; 
			case 2:
			imagejpeg($image_p, $this->filename,90);
			break; 
			case 3:
			imagepng($image_p, $this->filename);
			break; 
			default:
			throw new Exception("图片格式错误了", 1);
			break;
		}

		$this->file_data = file_get_contents($this->filename);
		$this->size = strlen($this->file_data);
		unlink($this->filename);
	}

	/**
	 * save 保存图片 
	 *
	 * @author zhuayi
	 */
	function save($filename)
	{

		$h = trim(substr(strrchr(strtolower($filename),'.'),1,100));
		if (empty($h))
		{
			$filename .= $this->h; 
		}

		$s3 = SinaStorageService::getInstance($this->domain,$this->accesskey,$this->secretkey);
		$s3->setAuth(true);
		$reset = $s3->uploadFile($filename,$this->file_data,$this->size,$this->type);
		
		
		$array['src'] = "{$this->domain_url}{$filename}";
		$array['height'] = $this->height;
		$array['width'] = $this->width;
		$array['h'] = $this->h;
		if ($reset == '200')
		{
			return $array;
		}
		else
		{
			return false;
		}
	}
}