<?php
/**
 *   EntryPHP V2.8.0
 *   author:周健东
 *   QQ:395154675
 *   V 1.14.0 添加微信异步接收类，用来做自动回复等功能，目前没有测试，类名：WeixinReceive.class.php
 *   V 1.16.0 数据库驱动类添加一个save()方法的array('*','-1')
 *   V 1.18.0 添加新浪图床方法,sinaUploadImage()
 *   V 1.18.2 微调,初始化生成文件去掉PHP结尾标签
 *   V 1.18.4 Base类修改,过滤微信错误机制触发类.
 *   V 1.18.6 完善微信支付使用方法注释
 *   V 1.18.8 完善注释
 *   V 1.20.0 V3上传图片的名称追加了一个随机数,防止重名
 *   V 1.22.0 微信红包的类修改,把配置的微信商户好的常量WX_KEY添加上去了
 *   V 1.24.0 微信支付和支付成功异步接受数据
 *   V 1.24.2 完善注释
 *   V 1.24.4 Base类修改,过滤微信错误机制触发类.过滤第二个错误 
 *   V 1.26.0 修改微信关注自动回复方法和回复关键词的方法。
 *   V 1.26.4 修复微信类二次跳转参数问题
 *   V 1.26.6 VerifyUser方法参数如果不填写就会显示出相关提示,还有配置文件增加相关注释
 *   V 2.0.0 微信授权结构发生改变，授权目录和数据表参数位置调换，类名是：WeixinUserAccredit,增加常亮PHP_SELF
 *   V 2.2.0 新增V2.4.0PHPExcel扩展，实现读取Excel文件内容，实现方法在基类里面
 *   V 2.4.0 解决微信认证之后COOKIE无法保存问题，原因处在URL上，如果用标准地址就可以了，（也可以在设置COOKIE里面添加一个目录的参数，目前授权这里没有用这个方法）
 *   V 2.6.0 修改微信自动回复关键词回复的类文件
 *   V 2.8.0 微信授权参数问题
*/
define('VERSION','2.8.0');
define('UPDATETIME','2018年03月06日08:46:00');
define('ENTRYPHP_PATH',dirname(__FILE__));
define('ENTRY_PATH',dirname(ENTRYPHP_PATH));
defined('APP_NAME')?True:define('APP_NAME',ucfirst(rtrim(basename($_SERVER['SCRIPT_FILENAME']),'.php')));
defined('APP_PATH')?define('APPLICATION_NAME',trim(trim(APP_PATH,'./'),'/')):define('APPLICATION_NAME',APP_NAME);
$_SERVER['REQUEST_SCHEME'] = isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http';
define('__ROOT__',rtrim($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']),'/'));
define('__APP__',__ROOT__.'/'.APPLICATION_NAME);
define('__TEMPLATE__',__APP__.'/Template');
!isset($_SESSION)?session_start():True;


require_once ENTRYPHP_PATH.'/Library/Build/Build.class.php';
require_once ENTRYPHP_PATH.'/Library/Base/Base.class.php';
require_once ENTRYPHP_PATH.'/Library/Base/Configure.class.php';
require_once ENTRYPHP_PATH.'/Common/Function.php';
require_once ENTRYPHP_PATH.'/Common/Route.php';