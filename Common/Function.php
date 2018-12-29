<?php
/**
*   author:周健东
*   QQ:395154675
*/
/**创建大M方法**/
function M($tableName='',$linkserver='',$singleton=true){
    $confAll = Configure::Db();
    $table = empty($confAll['prefix'])?$tableName:$confAll['prefix'].$tableName;
    if(!empty($linkserver)){
        if(is_array($linkserver)){
            $link = $linkserver;
        }else{
            if(!empty($confAll['dbRead'])&&!empty($confAll['dbWrite'])){
                $dbType = strtolower($linkserver);
                switch($dbType){
                    case 'read':
                        $link = $confAll['dbRead'];
                        break;
                    case 'write':
                        $link = $confAll['dbWrite'];
                        break;
                    default:
                        Base::abnormal('参数错误,输入的读写分离字符串有误，应该是read或write！错误码：100006');
                }
            }else{
                Base::abnormal('参数错误,没有配置读写分离选项！错误码：100007');
            }
        }
    }else{
        $link = $confAll;//自动判断是否是读写分离
        // if(empty($confAll['db'])){
        //     Base::abnormal('参数错误！没有配置任何数据库链接信息，错误码：100009');
        // }else{
        //     $link = $confAll['db'];
        // }
    }
    if(function_exists('mysqli_connect')){
        $db = new DbMysqli($table,$link,$singleton);
    }elseif(function_exists('mysql_connect')){
        $db = new DbMysql($table,$link,$singleton);
    }else{
        Base::abnormal('没有支持的数据库扩展！错误码：100011');
    }
    return $db;
}
