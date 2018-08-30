<?php
// +----------------------------------------------------------------------
// | ThinkCMF 前台接口
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 业余爱好者 <649180397@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

    public function index()
    {
    	$data  = array('212' => 2222);
    	$this->ajaxReturn(array('state' =>200,'msg'=>'返回成功','data'=>$data));
    }

}