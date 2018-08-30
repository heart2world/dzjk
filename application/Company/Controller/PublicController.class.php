<?php

namespace Company\Controller;
use Think\Controller;
class PublicController extends Controller {    

    //后台登录界面
    public function login() {
    	if(isset($_SESSION['COMP_ID'])){//已经登录
    		redirect(U('Company/Index/index'));
    	}else{
    	    
    		$this->display();    		
    	}
    }
    //注销登录
    public function logout(){
    	session('COMP_ID',null);
    	redirect(__ROOT__."/company/");
    }

    
    //登录检测
    public function dologin(){
       
    	$name = I("post.username");
    	if(empty($name)){
    		$this->error('用户名或邮箱不能为空！');
    	}
    	$pass = I("post.password",'','');
    	if(empty($pass)){
    		$this->error('密码不能为空！');
    	}
    	$verrify = I("post.verify");
    	if(empty($verrify)){
    		$this->error('验证码不能为空！');
    	}

    	//验证码
    	if(!sp_check_verify_code()){
    		$this->error('验证码错误！');
    	}else{
    		$user = D("company");    		
    		$where['userlogin']=$name;  
    		$result = $user->where($where)->find();
    		if(!empty($result) ){
    			if(sp_password($pass,$result['userpass'])){
    				
    				if($result["status"]==1 ){
    					$this->error('用户已被禁用');
    				}
    				//登入成功页面跳转
    				$_SESSION["COMP_ID"]=$result["id"];
    				$_SESSION['COMP_NAME']=$result["userlogin"];
    				$result['last_login_ip']=get_client_ip(0,true);
    				$result['last_login_time']=date("Y-m-d H:i:s");
    				$user->save($result);
    				setcookie("company_username",$name,time()+30*24*3600,"/");
                 
    				$this->success('登录成功！',U("Index/index"));
    			}else{
    				$this->error('密码错误！');
    			}
    		}else{
    			$this->error('用户名不存在！');
    		}
    	}
    }



}