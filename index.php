<?php
/**
 * 入口文件
 */

//$PHPSESSID = $_REQUEST['PHPSESSID']?$_REQUEST['PHPSESSID']:'';
//if($PHPSESSID){
//	session_id($PHPSESS ID);
//}


//暂时不开启参数过滤
//if (ini_get('magic_quotes_gpc')) {
//	function stripslashesRecursive(array $array){
//		foreach ($array as $k => $v) {
//			if (is_string($v)){
//				$array[$k] = stripslashes($v);
//			} else if (is_array($v)){
//				$array[$k] = stripslashesRecursive($v);
//			}
//		}
//		return $array;
//	}
//	$_GET = stripslashesRecursive($_GET);
//	$_POST = stripslashesRecursive($_POST);
//}

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
//项目资源目录，不可更改
define('SPSTATIC',   SITE_PATH.'statics/');
//定义缓存存放路径
define("RUNTIME_PATH", SITE_PATH . "data/runtime/");
//静态缓存目录
define("HTML_PATH", SITE_PATH . "data/runtime/Html/");
//版本号
define("SIMPLEWIND_CMF_VERSION", '0.1');
define("THINKCMF_CORE_TAGLIBS", 'cx,Common\Lib\Taglib\TagLibSpadmin,Common\Lib\Taglib\TagLibHome');

//uc client root
define("UC_CLIENT_ROOT", './api/uc_client/');

define("HTTP_HOST",  isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));

if(file_exists(UC_CLIENT_ROOT."config.inc.php")){
	include UC_CLIENT_ROOT."config.inc.php";
}
//载入框架核心文件
require SPAPP_PATH.'Core/ThinkPHP.php';



