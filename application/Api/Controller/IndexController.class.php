<?php

/**
 * Created by PhpStorm.
 * User: yanglin
 * Date: 2017-04-18
 */



namespace Api\Controller;

use Api\Controller\CommonController;
use Think\Exception;
use Think\Log;



//首页启动页
class IndexController extends CommonController
{

    public function index(){
        $config=getOptions("start_pic");
        if($config['start_pic']){
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $path=isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$config['start_pic'];
            $this->success($http_type.$path);
        }else{
            //没有设置返回空，由APP默认一张
            $this->success('');
        }

    }
}