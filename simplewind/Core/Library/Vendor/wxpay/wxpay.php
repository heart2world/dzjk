<?php

/*
 * 作者：ehecd团队
 * 官网：www.ehecd.com
 * 邮件: youge@ehecd.com  QQ 751812834
 */
class wxpay {
    private $config = array(
        'appId'=>'wx5f48869e357d5e5d',
        'mid'=>'1487802612',
        'appSecret'=>'ba12856cc902770b516fd854e5900a89',
        'key'=>'cbuebciowenhi9832ebg83eg93888888'
    );
    public function init($payment) {
        define('WEIXIN_SSLCERT_PATH', '../cert/apiclient_cert.pem');
        define('WEIXIN_SSLKEY_PATH', '../cert/apiclient_key.pem');
        define('WEIXIN_CURL_PROXY_HOST', "0.0.0.0"); //"10.152.18.220";
        define('WEIXIN_CURL_PROXY_PORT', 0); //8080;
        define('WEIXIN_REPORT_LEVENL', 1);
        require_once "wxpay/WxPay.Api.php";
        require_once "wxpay/WxPay.JsApiPay.php";
    }
    public function GetPrePayUrl($order){
        $wx_config=get_options('weixin');
        $payment =array(
            'app_appid'=>$wx_config['appId'],
            'app_mchid'=>$wx_config['mid'],
            'app_appsecret'=>$wx_config['appSecret'],
            'app_appkey'=>$wx_config['key']
        );
        $this->init($payment);
        define('WEIXIN_APPID', $payment['app_appid']);
        define('WEIXIN_MCHID', $payment['app_mchid']);
        define('WEIXIN_APPSECRET', $payment['app_appsecret']);
        define('WEIXIN_KEY',$payment['app_appkey']);
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order['body']);
        $input->SetAttach($order['body']);
        $input->SetOut_trade_no($order['order_id']);
        $order['money'] = $order['money'] *100;
        $input->SetTotal_fee("{$order['money']}");
        $input->SetTime_start(date("YmdHis"));
        //$input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($order['body']);
        $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'] . U( 'Api/Notify/wxpay_notify'));
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($order['order_id']);
        $return_order = WxPayApi::unifiedOrder($input);
        return $return_order['code_url'];
    }
    public function getAppCode($logs){
        $payment=array(
            'app_appid'=>'wx5f48869e357d5e5d',
            'app_mchid'=>'1487802612',
            'app_appsecret'=>'ba12856cc902770b516fd854e5900a89',
            'app_appkey'=>'cbuebciowenhi9832ebg83eg93888888'
        );
        $this->init($payment);
        define('WEIXIN_APPID', $payment['app_appid']);
        define('WEIXIN_MCHID', $payment['app_mchid']);
        define('WEIXIN_APPSECRET', $payment['app_appsecret']);
        define('WEIXIN_KEY',$payment['app_appkey']);
        $input = new WxPayUnifiedOrder();
        $input->SetBody($logs['title']);
        $input->SetAttach($logs['title']);
        $input->SetOut_trade_no($logs['pay_sn']);
        $logs['logs_amount'] = $logs['money'] *100;
        $input->SetTotal_fee("{$logs['logs_amount']}");
        $input->SetTime_start(date("YmdHis"));
        //$input->SetTime_expire(date("YmdHis", time() + 3000));
        $input->SetGoods_tag($logs['title']);
        $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'] . U( 'Api/Notify/wxpay_notify'));
        $input->SetTrade_type("APP");
        $order = WxPayApi::unifiedOrder($input);

        if($order['return_code']=='SUCCESS' && $order['result_code']='SUCCESS'){
            $data = new WxPayAppPay();

            $data->SetAppid($order['appid']);
            $data->SetPartnerid($order['mch_id']);
            $data->SetPrepayid($order['prepay_id']);
            $data->SetPackage("Sign=WXPay");
            $data->SetNonceStr(WxPayApi::getNonceStr());
            $data->SetTimeStamp(NOW_TIME);
            $data->SetSign();
            return $data->GetValues();
        }else{
            return $order;
        }
    }

    public function getOpenid($order,$code,$is_team=0){
        $wx_config=getOptions('weixin');
        $payment =array(
                'appid'=>$wx_config['appId'],
                'mchid'=>$wx_config['mid'],
                'appsecret'=>$wx_config['appSecret'],
                'appkey'=>$wx_config['key']
        );
        $this->init($payment);
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);

        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid($order,$code,$is_team);
        return $openId;
    }

    public function getCode($logs,$openId='') {
        $wx_config=getOptions('weixin');
        $payment =array(
                'appid'=>$wx_config['appId'],
                'mchid'=>$wx_config['mid'],
                'appsecret'=>$wx_config['appSecret'],
                'appkey'=>$wx_config['key']
        );
        $this->init($payment);
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);

        //①、获取用户openid
        $tools = new JsApiPay();

        //$openId = $tools->GetOpenid($logs,$code);
        //$openId = $code;
        $input = new WxPayUnifiedOrder();
        $input->SetBody($logs['title']);
        $input->SetAttach($logs['title']);
        $input->SetOut_trade_no($logs['pay_sn']);
        $logs['logs_amount'] = $logs['money'] *100;
        $input->SetTotal_fee("{$logs['logs_amount']}");
        $input->SetTime_start(date("YmdHis"));
        //$input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($logs['title']);
        $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'] . U( 'Api/Notify/wxpay_notify'));
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);

        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }

    public function respond() {
        $wx_config=getOptions('weixin');
        $payment_weixin =array(
                'appid'=>$wx_config['appId'],
                'mchid'=>$wx_config['mid'],
                'appsecret'=>$wx_config['appSecret'],
                'appkey'=>$wx_config['key']
        );
        //微信浏览器微信支付参数
//        $payment_weixin =array(
//            'app_appid'=>$config['appId'],
//            'app_mchid'=>$config['mid'],
//            'app_appsecret'=>$config['appSecret'],
//            'app_appkey'=>$config['key']
//        );
        //app微信参数
        $payment_app =array(
            'app_appid'=>'wx5f48869e357d5e5d',
            'app_mchid'=>'1487802612',
            'app_appsecret'=>'ba12856cc902770b516fd854e5900a89',
            'app_appkey'=>'cbuebciowenhi9832ebg83eg93888888'
        );
        $xml = file_get_contents("php://input");
        \Think\Log::write($xml);

        if (empty($xml))
            return false;
        $xml = new SimpleXMLElement($xml);

        if (!$xml)
            return false;
        $data = array();
        foreach ($xml as $key => $value) {
            $data[$key] = strval($value);
        }

        if (empty($data['return_code']) || $data['return_code'] != 'SUCCESS') {
            //file_put_contents('public_html/Baocms/Lib/Payment/aaa.txt', '1');
            return false;
        }
        if (empty($data['result_code']) || $data['result_code'] != 'SUCCESS') {
            //file_put_contents('public_html/Baocms/Lib/Payment/aaa.txt', '2');

            return false;
        }
        if (empty($data['out_trade_no'])){
            // file_put_contents('public_html/Baocms/Lib/Payment/aaa.txt', '3');

            return false;
        }
        ksort($data);
        reset($data);


        // $payment = D('Payment')->getPayment('weixin');
        /* 检查支付的金额是否相符 */
//        if (!$pay_model->checkMoney($data['out_trade_no'], $data['total_fee'])) {
//            return false;
//        }

        $sign = array();
        foreach ($data as $key => $val) {
            if ($key != 'sign') {
                $sign[] = $key . '=' . $val;
            }
        }
        //判断是微信浏览器微信支付还是APP微信支付，JSAPI是微信浏览器，APP是APP
        if($data['trade_type'] == "APP"){
            $sign[] = 'key=' . $payment_app['app_appkey'];
        }else{
            $sign[] = 'key=' . $payment_weixin['appkey'];
        }
//        $sign[] = 'key=' . $payment['appkey'];
        $signstr = strtoupper(md5(join('&', $sign)));
        if ($signstr != $data['sign']){

            return false;
        }
        $pay_model = D('Pay');
        $parameter = array(
            "out_trade_no"     => $data['out_trade_no'], //商户订单编号；
            "data"     => $_REQUEST,     //回调所有数据；
            "money"     => $data['total_fee']/100,    //交易金额；单位元
            "notify_id"     => $data['transaction_id'],    //微信支付订单号 32位
            "pay_time"   => strtotime(get_time($data['time_end'])),  //通知的发送时间。
            "pay_type"   => 0,  //微信
        );

        $res=$pay_model->notifySuccess($parameter);
        if($res){
            return true;
        }else{
            return false;
        }
    }
    //APP
    //企业付款
    public function app_company_pay($data){
        $payment_app =array(
            'app_appid'=>'wx5f48869e357d5e5d',
            'app_mchid'=>'1487802612',
            'app_appsecret'=>'ba12856cc902770b516fd854e5900a89',
            'app_appkey'=>'cbuebciowenhi9832ebg83eg93888888'
        );
        $this->init($payment_app);
        define('WEIXIN_APPID', $payment_app['appid']);
        define('WEIXIN_MCHID', $payment_app['mchid']);
        define('WEIXIN_APPSECRET', $payment_app['appsecret']);
        define('WEIXIN_KEY',$payment_app['appkey']);

        $input = new WxCompanyPay();
        $input->SetOut_trade_no($data['order_sn']);
        $input->SetDesc('用户提现');
        $input->SetAmount($data['amount']*100);
        $input->SetOpenid($data['openid']);
        $result = WxPayApi::company_pay($input);
        return $result;
    }



    //企业付款
    public function wx_company_pay($data){
        $wx_config=getOptions('weixin');
        $payment =array(
                'appid'=>$wx_config['appId'],
                'mchid'=>$wx_config['mid'],
                'appsecret'=>$wx_config['appSecret'],
                'appkey'=>$wx_config['key']
        );
        $this->init($payment);
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);

        $input = new WxCompanyPay();
        $input->SetOut_trade_no($data['order_sn']);
        $input->SetDesc('用户提现');
        $input->SetAmount($data['amount']*100);
        $input->SetOpenid($data['openid']);
        $result = WxPayApi::company_pay($input);
        return $result;
    }
}
