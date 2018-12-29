<?php 
/***上传 begin***/
/**
 *下面是公司是S3服务器上传方法，这个文件也可以用普通的上传方式把图片或文件上传到服务器上面 
 *然后返回地址放到数据库里面
 ***/
$message = 0;
$url = '';
$returnArr = array();
$i = 0;
$file = '';
foreach($_FILES as $key=>$val){
	if($i===0){
		$file = $key;
	}
	++$i;
}
if(!empty($_FILES[$file]['name'])){
	$a = new s3_upload();
	$b = $a->show($_FILES[$file]);
	$md = md5(time());
	$d = $a->save($md);
	$url = $d['src'];
	if(!empty($url)){
		$message = 1;
	}else{
		$message = -3;
	}	
}else{
	$message = -2;
}
$returnArr['message'] = $message;
$returnArr['url'] = $url;
echo json_encode($returnArr);
?>