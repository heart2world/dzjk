<?php
if (file_exists("data/conf/db.php")) {
    $db = include "data/conf/db.php";
} else {
    $db = array();
}
if (file_exists("data/conf/config.php")) {
    $runtime_config = include "data/conf/config.php";
} else {
    $runtime_config = array();
}

if (file_exists("data/conf/route.php")) {
    $routes = include 'data/conf/route.php';
} else {
    $routes = array();
}

$configs = array(
    //激光推送配置
    "JPUSH_APP_KEY"=>'0414035cf57718554dae6781',
    "JPUSH_MASTER_SECRET"=>'3bcea82839d23d6d824c36a3',
    
    'BAIDU_MAP_AK'=>'nkovf6GWmnxwwbvN4Gez4tfl',
    'USER_AGENT_STRING'=>'xy_app',
    "LOAD_EXT_FILE" => "extend",
    'UPLOADPATH' => 'data/upload/',
    'UPLOAD_TYPE'=>'local',//local,remote 上传类型设置
    'UPLOAD_ACTION_HOST'=>'http://115.28.48.78:8092/api/upload/controller?action=uploadimage',//如果设置成远程，这个是上传服务器
    'UPLOAD_ACTION_KEY'=>'7GHMK4VF6PQE9T30L5O8W2UCA1SYINEWSAPP',//如果设置成远程，远程上传KEY
    'UPLOAD_REMOTE_URL'=>'http://115.28.48.78:8092/',
    //'SHOW_ERROR_MSG'        =>  true,    // 显示错误信息
    "LOGIN_SAVE_STATUS"=>false,//利用缓存保存登录状态，否则session
    'SHOW_PAGE_TRACE' => false,
    'TMPL_STRIP_SPACE' => false,// 是否去除模板文件里面的html空格与换行
    'THIRD_UDER_ACCESS' => false, //第三方用户是否有全部权限，没有则需绑定本地账号
    /* 标签库 */
    'TAGLIB_BUILD_IN' => THINKCMF_CORE_TAGLIBS,
    'MODULE_ALLOW_LIST' => array('Admin', 'Asset', 'Api','Mobile','Company'),
    'TMPL_DETECT_THEME' => false,       // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX' => '.html',     // 默认模板文件后缀
    'DEFAULT_MODULE' => 'Mobile',  // 默认模块
    'DEFAULT_CONTROLLER' => 'Index', // 默认控制器名称
    'DEFAULT_ACTION' => 'index', // 默认操作名称
    'DEFAULT_M_LAYER' => 'Model', // 默认的模型层名称
    'DEFAULT_C_LAYER' => 'Controller', // 默认的控制器层名称

    'DEFAULT_FILTER' => 'htmlspecialchars', // 默认参数过滤方法 用于I函数...htmlspecialchars

    'LANG_SWITCH_ON' => true,   // 开启语言包功能
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'LANG_LIST' => 'zh-cn,en-us,zh-tw',
    'LANG_AUTO_DETECT' => false,

    'VAR_MODULE' => 'g',     // 默认模块获取变量
    'VAR_CONTROLLER' => 'm',    // 默认控制器获取变量
    'VAR_ACTION' => 'a',    // 默认操作获取变量

    'APP_USE_NAMESPACE' => true, // 关闭应用的命名空间定义
    'APP_AUTOLOAD_LAYER' => 'Controller,Model', // 模块自动加载的类库后缀

    'SP_TMPL_PATH' => 'themes/',       // 前台模板文件根目录
    'SP_DEFAULT_THEME' => 'yidong',       // 前台模板文件
    'SP_TMPL_ACTION_ERROR' => 'error', // 默认错误跳转对应的模板文件,注：相对于前台模板路径
    'SP_TMPL_ACTION_SUCCESS' => 'error', // 默认成功跳转对应的模板文件,注：相对于前台模板路径
    'SP_ADMIN_STYLE' => 'flat',
    'SP_ADMIN_TMPL_PATH' => 'admin/themes/',       // 各个项目后台模板文件根目录
    'SP_ADMIN_DEFAULT_THEME' => 'simplebootx',       // 各个项目后台模板文件
    'SP_ADMIN_TMPL_ACTION_ERROR' => 'Admin/error.html', // 默认错误跳转对应的模板文件,注：相对于后台模板路径
    'SP_ADMIN_TMPL_ACTION_SUCCESS' => 'Admin/success.html', // 默认成功跳转对应的模板文件,注：相对于后台模板路径
    'TMPL_EXCEPTION_FILE' => SITE_PATH . 'public/exception.html',

    'AUTOLOAD_NAMESPACE' => array('plugins' => './plugins/'), //扩展模块列表

    'ERROR_PAGE' => '',//不要设置，否则会让404变302

    'VAR_SESSION_ID' => 'session_id',

    "UCENTER_ENABLED" => 0, //UCenter 开启1, 关闭0
    "COMMENT_NEED_CHECK" => 0, //评论是否需审核 审核1，不审核0
    "COMMENT_TIME_INTERVAL" => 60, //评论时间间隔 单位s

    /* URL设置 */
    'URL_CASE_INSENSITIVE' => false,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持
    'URL_PATHINFO_DEPR' => '/',    // PATHINFO模式下，各参数之间的分割符号
    'URL_HTML_SUFFIX' => '',  // URL伪静态后缀设置

    'VAR_PAGE' => "p",

    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => $routes,

    /*性能优化*/
    'OUTPUT_ENCODE' => false,// 页面压缩输出

    'HTML_CACHE_ON' => false, // 开启静态缓存
    'HTML_CACHE_TIME' => 60,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX' => '.html', // 设置静态缓存文件后缀

    'TMPL_PARSE_STRING' => array(
        '/Public/upload' => '/data/upload',
        '__UPLOAD__' => __ROOT__ . '/data/upload/',
        '__STATICS__' => __ROOT__ . '/statics/',
    ),
    'MOBILE_TPL_ENABLED' => false ,   //开启手机模版

    //新版权限认证
    'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'ehecd_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'ehecd_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'ehecd_auth_rule', //权限规则表
        'AUTH_USER' => 'ehecd_users'//用户信息表
    ),
    'alipay'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'xmgoing@163.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://'.$_SERVER['HTTP_HOST'].'/Home/Notify/notify_url',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://'.$_SERVER['HTTP_HOST'].'/Home/Notify/return_url',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'http://'.$_SERVER['HTTP_HOST'].'/Api/Pay/pay_success',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'http://'.$_SERVER['HTTP_HOST'].'/Api/Pay/pay_error',
        'app_key'=>'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJQc4+q5nirzG6L4WVUBsR5Tk16zz3JrMo2aP5Or4DQ71e6mkWw7yVqf/cYKwgUWhVq214mq0jtnJdzrvm6tDhxEVHqzFgs9A4TTiliIdbQ/9UYXcVr5DBJkN4J+s82a9pYAbed2jyp1tDDa6kOjQ3hUrjTnNSSCBzoTE6EnGjcnAgMBAAECgYA4MdHjf7Nb9krZBULXdsHdkLYMK0qczcEraoeOnpp9FuqvFjF8kB5wLN2a4wAD8Cx+Y3rZd581/IeoDyV8VlpQmXXTheaePBBccnE6eZSMtnw/wWa9gF1gWkkf85hur7MI8a9XrYxKleg7BYwveThJmxvEE59M+JLxi2LM8T8ICQJBAMLVf8RdcB/c6xFYCkTd/fHtJZ/hpTYimg9tVRrpVobubJHu/evJsJE3qz/YB3kZlVeUmEycqwCa+Rz4xmy4zqsCQQDCnHzQc1s5ASkbL0eYLlVsQo/pAOFUzUX3+FR8LVybXtAxOUTlYhyp8pJqbsWI4BGs1O2LOw5776pf43s3H0l1AkBzsuEqtOoOodwd8pA2kTVqYw+CwDahzS57lsuBLauqeQ+UIb48NQtbURmq0hit+1lKJv3CEQ8jTuQ8Jid/DMf9AkEAuXQQyUA0vTTq7Dn4+v+kbd9cbollMb/QHobqU8+SgYkv0silbAY8FIPRnVVkLpSAo3fhyp2Dpv10GZ6ZC6VoBQJBAKTrEuCUemOLEuJNjQ6GQGmp1D4yN7o8WB9Qe0sGTaAoZKctgJMW+iXjGSY83NhDJoVyKAbHKSV8GJwxtMPMNN4=',
    ),
    'alipay_config'=>array(
        'partner' =>'2088921174712884',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'88urju5fjl0vnpy8xuyqz89pjbckge8e',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
        'ali_public_key_path'=>'http://'.$_SERVER['HTTP_HOST'].'/key/alipay_public_key.pem',
        'private_key_path'=>'http://'.$_SERVER['HTTP_HOST'].'/key/rsa_private_key.pem',
        //'private_key'=>'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBANY5vcmRLnUS5pX1H5Xc26hxTsr9Bcq1X+NwvErKVnuw7LFKEKihAWsiIM2adzyqcWuEs9BIshn3X5qOXf0+eWPxyEU37OZiJ4tbCoOkO9kuRGvRloksl3hWh8sptqKoBoUBnkbuzPnEA6xBYpQZmhfLeQA8tAyhYTf6lmrQVy0JAgMBAAECgYAgMol91B5BZlfVzgOzSICTLoSDKchHET+aNYV3UDXzXUIV22XpklleOsDnZgHp0kw9trI36dUq5e0uk5s4xr0DrAs6c+Vd3EQGG8huz1wit5YXeOGIPbKEyVNwDqADKdKWA1eFrX7RPSKJrm+3MeyfRV4l5ScGKXw8rgwduUxeiQJBAPSOQ4BBAVr2DSbv/Xq7EFDIEI90O86FLO7O+eA31WnlZs9+hkQwkogBbZCvVK1oC9DqmhixMevrT6t6pKwimq8CQQDgQCCdvQCWPpUHEztDx7wjK2WGaMHtDECY8N4f25dKxFalynq6MSZqvovNgzpj1N48ozXECGHKczNBoAQrHcHHAkBCsrIyPohyGH1Jy1ZkrLQsdAQgO+E72BKDJyv7PP8VnJ1HpghUfLLaKRmKxmbfzGq8ld9lMJ6e61SVPiO/Vsi9AkEAr2s6mGm6xczawxgWKiVaVKCj4Iqd+JK3DWyONZmPNdt0dGh5rKC6DpJTxkW7LVDdL830Rw7PgJCxLcrAuAGlDQJAOSTa+xMmf0yH+/z/txLMW/9JLW0e3IHCJVOTpdPXa0HAFeTCl7UKw2l7HDoWTDReA5ZvNuqsLbqInSgt2if5nw==',//私钥
        'private_key'=>'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJQc4+q5nirzG6L4WVUBsR5Tk16zz3JrMo2aP5Or4DQ71e6mkWw7yVqf/cYKwgUWhVq214mq0jtnJdzrvm6tDhxEVHqzFgs9A4TTiliIdbQ/9UYXcVr5DBJkN4J+s82a9pYAbed2jyp1tDDa6kOjQ3hUrjTnNSSCBzoTE6EnGjcnAgMBAAECgYA4MdHjf7Nb9krZBULXdsHdkLYMK0qczcEraoeOnpp9FuqvFjF8kB5wLN2a4wAD8Cx+Y3rZd581/IeoDyV8VlpQmXXTheaePBBccnE6eZSMtnw/wWa9gF1gWkkf85hur7MI8a9XrYxKleg7BYwveThJmxvEE59M+JLxi2LM8T8ICQJBAMLVf8RdcB/c6xFYCkTd/fHtJZ/hpTYimg9tVRrpVobubJHu/evJsJE3qz/YB3kZlVeUmEycqwCa+Rz4xmy4zqsCQQDCnHzQc1s5ASkbL0eYLlVsQo/pAOFUzUX3+FR8LVybXtAxOUTlYhyp8pJqbsWI4BGs1O2LOw5776pf43s3H0l1AkBzsuEqtOoOodwd8pA2kTVqYw+CwDahzS57lsuBLauqeQ+UIb48NQtbURmq0hit+1lKJv3CEQ8jTuQ8Jid/DMf9AkEAuXQQyUA0vTTq7Dn4+v+kbd9cbollMb/QHobqU8+SgYkv0silbAY8FIPRnVVkLpSAo3fhyp2Dpv10GZ6ZC6VoBQJBAKTrEuCUemOLEuJNjQ6GQGmp1D4yN7o8WB9Qe0sGTaAoZKctgJMW+iXjGSY83NhDJoVyKAbHKSV8GJwxtMPMNN4=',
        'public_key'=>'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB',//支付宝公钥
    ),
    'alipay_fund'=>array(
        'app_id'=>'2017121800946817',//app_id
        'alipay_public_key_2048'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAstS31AkGtJowJv1a1ZmWSOLf0DWTxr07NK3QdEqa4SqU0YtZX4cGf+Vy5sQrgnHcxWcyD5jcBLyhpk1meFL8uyG+FXticsWe+mBf2vTSUG/k9rW72ErSwdqw4UQn1BAYiZDEl9DZ7RCvqPMz8wEc7cwmbfsyVWI1VBg2wsvrga5xXpJLhTWvO+Xv+2onJsfwuzjEwUnx2oJf6uOW9HubHo5A5R0aoEajTt05G1A9rqPXEeZW6FFtgXuyrLeAA6cb2HizKmhevPawsf2cvVUoKtzluA61ZF2jGI5iM6HfNa7p99oGBuw60hNlonuFKIl7hdm2SvExROoeXDgKYI89BwIDAQAB',
        'private_key_2048'=>'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDBvWr2VG5r5VvQx//0gwPabU7RAJGGhCrIytqvO/8CaImBL4I4hF4irgTq9xb7Re8Z4wMqYVYhxsan7ETvev0Fl+GLHUm1nuEI9eTMroBExmPD7il3XjkXxEHhRxHEusMfYZlw1+4CrDoGlogqq5lRF2GnVZPW2wiCIvMkd3sWfjD//reFItvZUYe4b0X9aaNTI5ocQOgT4/XCLjBg/2s0TjbmcLuQmHGtofhFQdE1QYdULnBUAoxkJWy83Jskgqii2uTdmCq0Jana9IRKM7LqftIfjK+Twya/iFI+9U7lApdEvq7P5ncsO7frs2pN7pa2bd7KUc/+eOstbnO/EAHdAgMBAAECggEBAIV1Du56wMnQaAwNCXMMoSXg8E8mvhhUiQhiPHxDrumgyz/TUzfNbu8iwr580TbsOMWzTGXwQR3pSMU6C06QgSVON47V8QWMWWYLxL1+Y1t88hJwx92zaU7G4b7mbwX+xRSj7cf+5rSuoRYSsd/67xUeLyXO3JU/sr+6hZUUPRUDSdw9aLkjm68Chlr3KTJ4aP3XGTzEVIQtcEqNSw+4AzF3dePdOmXh7EJ4S28jAt9rhHJMyuaX8E/otgZcuER0f4X2qOmEp020X32PUImUIesDFYzcGdr4mmMcKEJSAJDp0Low2tzKOBC02/zKsCEJvOCm3fzKpcHAoZdDedtUpR0CgYEA+KA2iqHQTeN+446aa1YvEs7bzkDcScWLJjMFXhzFG2A9/0qBLHbsjmoFZezgdGnENrPSpwFNigMqRWoe90L3RS9EoE8bvN9n9IWO3kQ7nv6BxgmH0ckumnWMyVMdsd+hbEBlLog5Wjvv4NWqggBB0cif9iiDqNNm1kUmZDHl4Q8CgYEAx3x2Xwkqkm/AUUMKQ8WEi+NZC7+kvARm/voanl/n2DQ40o6QVLE6avuAOv+PFakD/59njrcOIomq6LmP1cOzjEQYMwiK+aTKyhJH3x8Rrvp1ueEgHvgPyol3uwkUFeEdIk6QN3FjiaC3GgSyJb8AvvMrkRF9es1JqzUoXH9DVlMCgYBsnOD20xXC0P1fv65p8a6C0udnRSJ2/9t4BpXztTHOcc7jtdKUcCeLV3mgO0ka0hGrrAb8ei87eYJ/7Io2joSjLrEWLzj428f4JFhzdO9u19QthYBV/0NiqrOkR7ETQZStS7xoBY64grKaT/066Y3XbYOj77MEj8W/GVpaLwoztQKBgGY/JhDqCtcJzmIYYY+BEsH8omyCKvZvrzbrjG82qwfHJITPq6ytNksVY5FZ4cXxXmpMEvE0ZFtRgrdMPSwM5d90G480xr7UN1jBa/Mx4od0OpkuiZ81+CoQsby9F31rZ9pouESiqqklJhSA6aqtLr2wt9jgRReHV0YYtfpLBIGfAoGBALwTP1LGJRtcw4OsW2qFAG2k/PljFV+PKvH9wq7vDAK8ReNch0NIXhzRJyd1SS1tJqb73wTGbYnVl3MRH21wiIL4j+sUoAYAVJ8O/qmN6lYCO1pM19b6s6w2erZvQ9i+JDzfgeIzw5hIy2u2wBL8Gpk6FcSzEZLsfZots9Cv+qeC',
        'private_key_path_2048'=>'http://'.$_SERVER['HTTP_HOST'].'/key/rsa_private_key_2048.pem',
    ),
);
return array_merge($configs, $db, $runtime_config);
