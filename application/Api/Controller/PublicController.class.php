<?php
/**
 * 公共方法控制器
 */

namespace Api\Controller;
use Common\Model\Member\MemberModel;
use Api\Controller\CommonController;
class PublicController extends CommonController

{

    private $MemberModel;
    public function __construct()
    {
        parent::__construct();
        $this->MemberModel = new MemberModel();
    }
    
    
    /**
     *登录
     */
    public function login()
    {
        $data['mobile']=I('mobile');
        $data['password']=I('password');//已经在客户端md5加密过的字符串
        //$data['mobile']='15982016300';
        //$data['password']=md5('123456');//已经在客户端md5加密过的字符串
        $data = $this->MemberModel->login($data);
        if ($data) {
            $this->success($data);
        } else {
            $this->error($this->MemberModel->getCode(),$this->MemberModel->getError());
        }
    }



    //注销登录
    public function login_out(){
        $token='15982016366';
        $res=$this->MemberModel->login_out($token);
        if($res){
            $this->success("成功退出登录");
        }else{
            $this->error($this->MemberModel->getCode(),$this->MemberModel->getError());
        }
    }
    /**
     * 注册
     * 
     */
    public function register(){
        $data['mobile']='15982016366';
        $data['password']=md5('123456');
        $data['confim_password']=md5('123456');
        $data['verify']='320440';
        $res=$this->MemberModel->register($data);
        if($res){
            $this->success($res);
        }else{
            $this->error($this->MemberModel->getCode(),$this->MemberModel->getError());
        }
    }
 

   
     /**
     * 忘记密码(找回密码)
     */
    public function forget_pwd(){
        $data['mobile']='15982016366';
        $data['password']=md5('123456');
        $data['confim_password']=md5('123456');
        $data['verify']='320440';
        $res=$this->MemberModel->forget_pwd($data);
        if($res){           
            $this->success('重置成功');
        }else{
            $this->error($this->MemberModel->getCode(),$this->MemberModel->getError());
        }   
    } 

  
    /**
     * [get_code 发送短信]
     * @return [type] [description]
     */
    public function get_code(){
        $data['mobile']='15982016300';
        $data['type']=0;//0为注册1找回密码2找回支付密码
        //注册
        $res=$this->MemberModel->get_code($data);
        if($res){
            $this->success();
        }else{
            $this->error($this->MemberModel->getCode(),$this->MemberModel->getError());
        }
    }


    /**
     * [check_verify [短信验证]
     * @return [type] [description]
     */
    public function check_mobile_verify(){
        $data['mobile']='15982016300';
        $data['type']=0;
        $data['code']='320440';
        $res=$this->MemberModel->check_mobile_verify($data);
        if($res){
            $this->success('验证码正确');
        }else{
            $this->error(2014,'短信验证码错误');
        }
    }


    public function register_agreement(){
        $config=getOptions("site_options");
        if(!$config['register_agreement']){
            $config['register_agreement']='';
        }
        $this->success($config['register_agreement']);
    }
}  