<?php
if (isset($_SERVER['SERVER_ENV']) && $_SERVER['SERVER_ENV']=='product') {
    define('DEBUG',false);
    define('DEVELOPMENT','product');
    error_reporting(0);
} else {
    define('DEBUG',true);
    define('DEVELOPMENT','development');
    error_reporting(E_ALL^E_NOTICE);
    ini_set("display_errors", "On");
}
session_start();
date_default_timezone_set('Asia/Shanghai');
if(Extension_Loaded("zlib")){
    Ob_Start('ob_gzhandler'); //开启gzip压缩模式
}
define("APPLICATION_PATH",  dirname(__FILE__));//定义常量
if (!extension_loaded("yaf")) {
    echo "YAF 框架丢失，请检查服务器环境";exit;
}
//初始化及路由
$app  = new Yaf_Application(APPLICATION_PATH . "/conf/application.ini");
$app->bootstrap()->run();
if(Extension_Loaded("zlib")){
    Ob_End_Flush();
}
