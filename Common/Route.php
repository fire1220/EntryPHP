<?php
/**
*   author:周健东
*   QQ:395154675
*/
$controller = 'Index';
$actionName = 'index';
if(isset($_SERVER['PATH_INFO'])){
    $pathInfo = explode('/',trim($_SERVER['PATH_INFO'],'/'));
    $controller = empty($pathInfo[0])?$controller:$pathInfo[0];
    $actionName = empty($pathInfo[1])?$actionName:$pathInfo[1];
    array_shift($pathInfo);
    array_shift($pathInfo);
    $get = '0';
    foreach($pathInfo as $key=>$val){
        if(($key&1)===0){
            $get = $val;
        }else{
            $_GET[$get] = $val;
        }
    }
}elseif(isset($_GET['c'])||isset($_GET['a'])){
    $controller = empty($_GET['c'])?$controller:$_GET['c'];
    $actionName = empty($_GET['a'])?$actionName:$_GET['a'];
}
$controllerName = $controller.'Controller';
define('CONTROLLER',$controller);
define('ACTION',$actionName);
define('PHP_SELF',trim(dirname($_SERVER['SCRIPT_NAME']),'/').'/'.basename($_SERVER['SCRIPT_FILENAME']).'?c='.CONTROLLER.'&a='.ACTION.'&'.http_build_query($_GET));
define('__URL__',__ROOT__.'/'.basename($_SERVER['SCRIPT_FILENAME']).'/'.CONTROLLER);
spl_autoload_register('Base::autoLoad');
$page = new $controllerName();
$page->$actionName();
