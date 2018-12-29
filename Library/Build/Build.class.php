<?php
/**
*   author:周健东
*   QQ:395154675
*/
class Build{
    public function __construct(){
        $this->buildingInit();
    }
    public function buildingInit(){//检查目录是否存在
        if(!is_dir(APPLICATION_NAME)){
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME);
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/App');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Common');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Configure');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Controller');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Model');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Template');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/Index');
            mkdir(ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/Static');
$configureContents = <<<STR
<?php
return array(
//     'db'    => array(
//         'host'  =>  '127.0.0.1',     //MySQL主机地址
//         'port'  =>  '3306',          //MySQL端口号 
//         'username'  =>  'root',      //MySQL用户名
//         'password'  =>  '',          //MySQL密码
//         'database'  =>  ''           //MySQL数据库
//     ),
//    'dbRead' => array(//读写分离中的读取的数据库
//         'host'  =>  '',
//         'port'  =>  '',
//         'username'  =>  '',
//         'password'  =>  '',
//         'database'  =>  ''
//    ),
//    'dbWrite' => array(//读写分离中的写入的数据库
//         'host'  =>  '',
//         'port'  =>  '',
//         'username'  =>  '',
//         'password'  =>  '',
//         'database'  =>  ''
//    ),
//     'WeixinUserAccredit'  =>    array(
//         'WeixinConf'  =>  array(//新浪黑龙江服务号
//             'appid'  =>  '',//微信授权的appid
//             'secret'  =>  '',//微信授权的秘钥secret
//             'DomainName'   =>  '',//微信授权域名，注意结尾一定要加/，例如：http://common.hlj.sina.com.cn/
//         ),
//         'DbField'   =>  array(// 对应数据库字段
//             'DB_id' =>  '',//自增的字段，例如id
//             'DB_openid' =>  '',//存放openid的字段
//             'DB_nickname' =>  '',//存放nickname的字段
//             'DB_headimgurl' =>  '',//存放headimgurl的字段
//             'DB_create_time' =>  '',//存放create_time时间的字段
//             'DB_status' =>  '',//存放status状态的字段
//         ),
//         'DefaultValue'  =>  array(// 插入数据默认值'字段'=>'值'

//         ),
//         'CookieField'  =>  array(// 对应存储的COOKIE名称
//             'COOKIE_uid'    =>  'uid',//插入数据库后，返回的自增id值
//             'COOKIE_openid'    =>  'openid',//openID值
//         ),
//     ),
);
STR;
            file_put_contents(ENTRY_PATH.'/'.APPLICATION_NAME.'/Configure/Configure.php',$configureContents);
$IndexControllerContents = <<<STR
<?php
class IndexController extends Controller{
    public function index(){
        \$welcome =  "Welcome to EntryPHP";
        \$this->assign('welcome',\$welcome);
        \$this->display();
    }
}
STR;
            file_put_contents(ENTRY_PATH.'/'.APPLICATION_NAME.'/Controller/IndexController.class.php',$IndexControllerContents);
$indexContents = <<<STR
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to EntryPHP</title>
    <script src="__TEMPLATE__/Static/angular.min.js"></script>
    <script>
        var app = angular.module('myapp',[]);
        app.controller('mynct',function(\$scope){
            \$scope.welcome = EntryData.welcome;
        });
    </script>
</head>
<body>
    <div ng-app="myapp" ng-controller="mynct">
        <h1 style="margin-top:20%;text-align:center;" ng-bind="welcome"></h1>
    </div>
</body>
</html>
STR;
            file_put_contents(ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/Index/index.html',$indexContents);



            copy(ENTRYPHP_PATH.'/Extend/Tools/angular.min.js',ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/Static/angular.min.js');

           
        }
    }
}
new Build();
