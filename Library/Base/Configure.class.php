<?php
/**
*   author:周健东
*   QQ:395154675
*   配置类文件
*/
$responseConfUser = include_once ENTRY_PATH.'/'.APPLICATION_NAME.'/Configure/Configure.php';//用户配置文件
$responseConf = include_once ENTRYPHP_PATH.'/Configure/Configure.php';//系统配置文件
class Configure{
    static public function Db(){
        $confAll = array_merge($GLOBALS['responseConf'],$GLOBALS['responseConfUser']);
        if(empty($confAll['username'])&&empty($confAll['password'])&&empty($confAll['database'])&&empty($confAll['host'])&&empty($confAll['port'])&&empty($confAll['db'])&&empty($confAll['dbRead'])&&empty($confAll['dbWrite'])){
            if(!empty($_SERVER['SINASRV_DB_HOST'])&&!empty($_SERVER['SINASRV_DB_USER'])&&!empty($_SERVER['SINASRV_DB_PASS'])){
                $confAll['dbRead'] = array(
                    'host'  =>  $_SERVER['SINASRV_DB_HOST_R'],
                    'port'  =>  $_SERVER['SINASRV_DB_PORT_R'],
                    'username'  =>  $_SERVER['SINASRV_DB_USER_R'],
                    'password'  =>  $_SERVER['SINASRV_DB_PASS_R'],
                    'database'  =>  $_SERVER['SINASRV_DB_NAME_R']
                );
                $confAll['dbWrite'] = array(
                    'host'  =>  $_SERVER['SINASRV_DB_HOST'],
                    'port'  =>  $_SERVER['SINASRV_DB_PORT'],
                    'username'  =>  $_SERVER['SINASRV_DB_USER'],
                    'password'  =>  $_SERVER['SINASRV_DB_PASS'],
                    'database'  =>  $_SERVER['SINASRV_DB_NAME']
                );
            }
        }
        if(empty($confAll['db'])&&!empty($confAll['username'])&&!empty($confAll['password'])&&!empty($confAll['database'])){
            $confAll['db']['host'] = $confAll['host'];
            $confAll['db']['port'] = $confAll['port'];
            $confAll['db']['username'] = $confAll['username'];
            $confAll['db']['password'] = $confAll['password'];
            $confAll['db']['database'] = $confAll['database'];
        }
        if(empty($confAll['dbRead'])){
            $confAll['dbRead'] = $confAll['db'];
        }
        if(empty($confAll['dbWrite'])){
            $confAll['dbWrite'] = $confAll['db'];
        }
        return $confAll;
    }
    static public function Conf($parameterName=''){
        $confAll = self::Db();
        if(!empty($parameterName)){
            $confAll = $confAll[$parameterName];
        }
        return $confAll;
    }
}