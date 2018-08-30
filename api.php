<?php
//开启调试模式
define("APP_DEBUG", true);

//网站当前路径
define('SITE_PATH', dirname(__FILE__)."/");

//项目路径，不可更改
define('APP_PATH', SITE_PATH . 'application/');

//项目相对路径，不可更改
define('SPAPP_PATH',   SITE_PATH.'simplewind/');

//
define('SPAPP',   './application/');

//定义缓存存放路径
define("RUNTIME_PATH", SITE_PATH . "data/runtime/");

//版本号
define("SIMPLEWIND_CMF_VERSION", '0.1');


define("URL_ROOT","http://" . $_SERVER['HTTP_HOST']);
//定义应用模式
//define('APP_MODE','Api');
define('BIND_MODULE', 'Api'); // 绑定Api模块到当前入口文件

//载入框架核心文件
require SPAPP_PATH.'Core/ThinkPHP.php';