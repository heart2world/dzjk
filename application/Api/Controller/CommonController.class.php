<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-07-06
 * Time: 9:34
 */
namespace Api\Controller;
use Common\Model\MemberModel;
use Think\Controller;

class CommonController extends Controller

{
    protected $member;
    public function __construct()
    {
        parent::__construct();
        //$this->checkSign();
        $this->member=$this->get_member(I('token'));
    }


    private function get_member($token){
        if(empty($token)){
            return false;
        }else{
            $member=M('Member')->field('login_pwd,token,dzp_password',true)->where(array('token'=>$token,'is_delete'=>0))->find();
            if($member['token_expire_time']&&$member['token_expire_time']>=time()){
                if($member['pay_pwd']){
                    $member['pay_pwd']=1;
                }else{
                    $member['pay_pwd']=0;
                }
                return $member;
            }
            return false;
        }
    }

    /**
     * 验证签名是否正确
     */
    protected function checkSign(){
        $timestamp=I('timestamp',0,"intval");
        //可关闭该检查
        if(!check_timestamp($timestamp)){
            $this->error(1001,'系统时间设置错误');
        }
        $data=array_merge($_POST,$_GET);
        $sign=I('signature');
        if(!$sign){
            $this->error(1000,'签名错误');
        }
        $app_key=C('APP_KEY');
        if(!getSignVeryfy($data,$sign,$app_key)){
            //检查签名
            $this->error(1000,'签名错误');
        }
    }

    /*
     * 返回错误
     */
    protected function appReturn($data,$code=0,$msg='操作成功'){
        $arr = array(
            "code"=>$code,
            "msg"=>$msg,
            "data"=>$data,
        );
        //\Think\Log::record(json_encode($arr),"INFO");
        $this->ajaxReturn($arr);

    }

    protected function error($code=1,$err){
        $this->appReturn($err,$code,'操作失败');
    }

    protected function success($data=''){
        $this->appReturn($data);
    }
}