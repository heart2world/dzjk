<?php

/**
 * Created by PhpStorm.
 * User: yanglin
 * Date: 2017-04-13
 * Time: 10:26
 */



namespace Api\Controller;

use Common\Model\PayModel;
use Think\Controller;

/*
 * 支付回调控制器
 */
class NotifyController extends Controller
{
    protected $pay_model;//支付模型

    //析构函数
    public function __construct()
    {
        parent::__construct();
        $this->pay_model = new PayModel();

        //支付宝第三方插件加载
        vendor('Alipay.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
        //支付宝第三方插件加载结束

        //加载微信支付
        vendor('wxpay.wxpay');
    }

    //APP支付宝异步回调通知   因APP加密算法不一样所以需要单独分开
    public function app_alipay_notify_url(){



        $alipay_config=C('alipay_config');

        $alipay_config['sign_type'] = strtoupper('RSA');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);

        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result)
        {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no   = $_POST['out_trade_no'];      //商户订单号
            $trade_no       = $_POST['trade_no'];          //支付宝交易号
            $total_fee      = $_POST['total_fee'];         //交易金额
            $notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。

            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "data"     => $_REQUEST,     //回调所有数据；
                "money"     => $total_fee,    //交易金额；单位元
                "notify_id"     => $trade_no,    //微信支付订单号 32位
                "pay_time"   => strtotime($notify_time),  //通知的发送时间。
                "pay_type"   => 1,  //支付宝
            );

            if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                \Think\Log::write('alipay notify_url)_1111','ERR');
                $result=$this->pay_model->notifySuccess($parameter);
                if($result){
//                    $url='http://'.$_SERVER['HTTP_HOST'].U('Pay/pay_success')."/pay_sn/"+$out_trade_no;
//                    echo "<scrīpt LANGUAGE='Javascrīpt'>";
//                    echo "location.href='$url'";
//                    echo "</scrīpt>";
                    echo 'success';
                }else{
                    echo "fail";
                }
            }
        }else {

            //验证失败
            echo "fail";
        }
    }

    //APP支付宝同步回调通知   因APP加密算法不一样所以需要单独分开  这个接口APP是可能不会调用
    public function app_alipay_return_url(){
        $alipay_config=C('alipay_config');
        $alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no   = $_GET['out_trade_no'];      //商户订单号
            $trade_no       = $_GET['trade_no'];          //支付宝交易号
            $total_fee      = $_GET['total_fee'];         //交易金额
            $notify_time    = $_GET['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。


            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "data"     => $_REQUEST,     //回调所有数据；
                "money"     => $total_fee,    //交易金额；单位元
                "notify_id"     => $trade_no,    //微信支付订单号 32位
                "pay_time"   => strtotime($notify_time),  //通知的发送时间。
                "pay_type"   => 1,  //支付宝
            );

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $result=$this->pay_model->notifySuccess($parameter);
                if($result){
                    echo 'success';
                }else{
                    echo "fail";
                }
                //$this->redirect(U('Mobile/Pay/successful').'/id/'.$out_trade_no);//跳转到配置项中配置的支付成功页面；
                //todo 需要设置跳转地址
            }else {
                echo "trade_status=".$_GET['trade_status'];
                //$this->redirect(U('Mycompany/my_pay_log'));//跳转到配置项中配置的支付失败页面；
                //todo 需要设置跳转地址
            }
        }else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
    }

    //PC+移动异步回调通知   PC+移动加密一样共用
    public function alipay_notify_url(){
        $alipay_config=C('alipay_config');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no   = $_POST['out_trade_no'];      //商户订单号
            $trade_no       = $_POST['trade_no'];          //支付宝交易号
            $total_fee      = $_POST['total_fee'];         //交易金额
            $notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。

            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "data"     => $_REQUEST,     //回调所有数据；
                "money"     => $total_fee,    //交易金额；单位元
                "notify_id"     => $trade_no,    //微信支付订单号 32位
                "pay_time"   => strtotime($notify_time),  //通知的发送时间。
                "pay_type"   => 1,  //支付宝
            );
            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $result=$this->pay_model->notifySuccess($parameter);
                if($result){
                    echo 'success';
                }else{
                    echo "fail";
                }
            }
            echo 'success';
        }else {
            //验证失败
            echo "fail";
        }
    }

    //PC+移动异步回调通知   PC+移动加密一样共用
    public function alipay_return_url(){
        $alipay_config=C('alipay_config');
        $alipayNotify = new \AlipayNotify($alipay_config);//计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no   = $_GET['out_trade_no'];      //商户订单号
            $trade_no       = $_GET['trade_no'];          //支付宝交易号
            $total_fee      = $_GET['total_fee'];         //交易金额
            $notify_time    = $_GET['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。

            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "data"     => $_REQUEST,     //回调所有数据；
                "money"     => $total_fee,    //交易金额；单位元
                "notify_id"     => $trade_no,    //微信支付订单号 32位
                "pay_time"   => strtotime($notify_time),  //通知的发送时间。
                "pay_type"   => 1,  //支付宝
            );

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $result=$this->pay_model->notifySuccess($parameter);
                if($result){
                    $this->redirect(C('alipay.successpage').'/pay_sn/'.$out_trade_no);//跳转到配置项中配置的支付成功页面；
                }else{
                    $this->redirect(C('alipay.errorpage'));
                }
            }else {
                //echo "trade_status=".$_GET['trade_status'];
                $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
            }
        }else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //echo "支付失败！";
            $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
        }
    }

    //微信通知回调
    public function wxpay_notify(){
        $wxpay = new \wxpay();
        $res=$wxpay->respond();
        if (IS_POST) {
            if ($res == false) {
                exit('fail');
            }else{
                exit('SUCESS');
            }
        }else{
            if ($res == false) {
                $this->error('支付失败！');
            }else{
                $this->success('支付成功！');
            }
        }
    }
}