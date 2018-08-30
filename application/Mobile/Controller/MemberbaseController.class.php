<?php

namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class MemberbaseController extends CommonController
{
    public function __construct() {
        parent::__construct();
        $this->check_login();


//        if($this->member['status']==0){
//            $this->error('账号已被冻结');
//        }

//        if(empty($this->member['login_pwd'])&&ACTION_NAME !='set_pwd'){
//            redirect(U('Member/set_pwd'));
//        }
    }
    /**
     * [check_login 判断用户是否登陆]
     * @return [type] [description]
     */
    protected function check_login(){
        //dump($this->member);die;
        if(!$this->member){
            if(IS_AJAX){
                $this->error('请登录后访问',U('Index/login'));
            }else{
//                $this->(U('/Index/login'));
                header('location:/Index/login');exit;
            }
        }
    }
}