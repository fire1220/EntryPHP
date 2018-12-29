<?php
/**
*   author:周健东
*   QQ:395154675
*/
class Base {
    static public function autoLoad($className){
        if(substr($className,-10,10)==='Controller'){
            include_once ENTRYPHP_PATH.'/Library/Controller/Controller.class.php';
            $filePath = ENTRY_PATH.'/'.APPLICATION_NAME.'/Controller/'.$className.'.class.php';
        }elseif(substr($className,0,6)==='Weixin'){
            $filePath = ENTRYPHP_PATH.'/Library/Driver/Weixin/'.$className.'.class.php';
        }elseif($className==='DbMysqli'){
            $filePath = ENTRYPHP_PATH.'/Library/Driver/Db/'.$className.'.class.php';
        }elseif($className==='DbMysql'){
            $filePath = ENTRYPHP_PATH.'/Library/Driver/Db/'.$className.'.class.php';
        }elseif($className==='s3_upload'){
            $filePath = ENTRYPHP_PATH.'/Extend/S3img/'.$className.'.class.php';
        }elseif(substr($className,0,8)==='PHPExcel'){
            $filePath = ENTRYPHP_PATH.'/Extend/PHPExcel/'.str_replace('_','/',$className).'.php';
        }else{
            if($className!='WxPayException'){//过滤微信错误机制触发类.
                //Base::abnormal('没有注册自动加载类'.$className.'！错误码：100003');
            }
        }

        if(is_file($filePath)){
            include_once $filePath;
        }else{
            if($className!='WxPayException'){//过滤微信错误机制触发类.
                Base::abnormal('文件：'.$filePath.'不存在,无法实例化'.$className.'！错误码：100002');
            }
        }
    }
    static public function abnormal($abnormalMessages){
        header('content-type:text/html;charset=utf-8');
        die($abnormalMessages);
    }
}
