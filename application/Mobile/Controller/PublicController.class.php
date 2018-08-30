<?php

namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use Think\Controller;

class PublicController extends CommonController
{

    //协议
    public function agre()
    {
        $site_options = getOptions('site_options');
        if(I('type') == 'ys')  //医生协议
        {
            $this->content = $site_options['content3'];
        }else
        {
            $this->content = $site_options['content'];
        }

        $this->display();

    }



    public function login()
    {
        $tiaourl=$_SERVER['HTTP_REFERER'];

        $this->display("/Index/login");
    }


    public function shareBack()
    {
        $id = intval(I('id'));
        $res = M('Article')->where(array('id'=>$id))->setInc('fxs',1);

//        dynamic_integral  用户

        if($this->member['member_id'])
        {

            $integrals = M('Member')->field('integral,types,is_ok')->where(array('id'=>$this->member['member_id']))->find();

            $opt = getOptions('site_options');
            if($integrals['types'] == 2 && $integrals['is_ok'] == 1)
            {
                $integral = $opt['d_d_integral'];
            }else
            {
                $integral = $opt['dynamic_integral'];
            }

            $irow['member_id'] = $this->member['member_id'];
            $irow['change'] = intval($integral);
            $irow['change_type'] = 3;
            $irow['change_status'] = 1;
            $irow['after'] = $integrals['integral'] + intval($integral);
            $irow['create_time'] = time();
            M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',intval($integral));
            M('IntegralLog')->add($irow);

        }


        //d_d_integral  医生

        if($res)
        {
            $this->success('ok');
        }else
        {
            $this->error('no');
        }


    }



    //获取是ios  还是 安卓
    function get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
        {
            $type = 'ios';
        }
        if(strpos($agent, 'android'))
        {
            $type = 'android';
        }
        echo  $type;
    }

    //APP微信授权登录
    public function app_wx_login()
    {
//        $appid = I("appid");
//        $openid = I("openid");
//        $unionid = I("unionid");

//        if($this->get_device_type() != 'ios' || $this->get_device_type() != 'android')
//        {  opLGT1PEKN5wIZ9G6q6Gi_GaxoWE
//            $this->error("请求错误!");
//        }




        $app_openid = I("app_openid");
        $nickname = I("nickname");
        $avatar = I("avatar");
        $prov = I("prov");
        $city = I("city");

        if(empty($app_openid))
        {
            $this->error("appid参数必须传入");
        }
        if(strlen($app_openid) < 25 || strlen($app_openid) > 32)
        {
            $this->error("appid参数错误");
        }


        if($app_openid && !$nickname && !$avatar)
        {
            $mid = M('Member')->field('id')->where(array('app_openid' => $app_openid))->find();
            if(!$mid)
            {
                $this->error("非法请求");
            }
        }




        //更新首页标签  加1
        $whereAD['st'] = array('lt',time());
        $whereAD['ent'] = array('gt',time());
        $whereAD['status'] = 1;
        $whereAD['is_delete'] = 0;
        $whereAD['zsqy'] = array('like','%1%');
        $res = M('Adve')
            ->where($whereAD)
            ->field('id')
            ->order('id desc')
            ->select();
        foreach ($res as $re)
        {
            M('Adve')->where(array('id'=>$re['id']))->setInc('visit',1);
        }



//        if($this->member['member_id'])
//        {
//            M('Adve')->where(array('id'=>$res['id']))->setInc('visit',1);
//        }


        $provId = $cityId = 0;
        if($prov)
        {
            $provId = M('Region')->where(array('region_name'=>$prov,'region_type'=>1))->getField('region_id');
        }
        if($city)
        {
            $cityId = M('Region')->where(array('region_name'=>$city,'region_type'=>2))->getField('region_id');
        }


//        if(empty($openid)){
//            $this->error("openid参数必须传入");
//        }
//        if(empty($unionid)){
//            $this->error("unionid参数必须传入");
//        }
        $member = M('Member')->field("id as member_id,mobile,types,is_ok,status,mobile")->where(array('app_openid' => $app_openid, 'is_delete' => 0))->find();
        if ($member)
        {
//            if ($member['status'] == 0) {
//                $this->error('账号被冻结');
//            }
            session("user", $member);
            $update['last_login_time'] = time();
            $update['last_login_ip'] = get_client_ip();
            $token = sp_random_string(10);
//            $update['token'] = md5($token . '_' . $member['member_id']);
            $res = M('Member')->where(array('id' => $member['member_id']))->save($update);
            if ($res !== false) {
                $time = 20 * 24 * 3600;//自动登录20天
                if (C('LOGIN_SAVE_STATUS')) {
                    cookie('member_login_token', get_token($member['member_id'], 'Member', $token, $time), $time);
                } else {
                    unset($member['pwd']);
                    session('user', $member);
                    cookie('user', $member, $time);
                }


                //已经关注的标签数量加1
                $ress = M('MemberLab')->field('lab')->where(array('uid'=>$member['member_id']))->find();

                if($ress['lab'])
                {
                    $labelListShow = M('Adve')->field('id')->where(array('lab'=>array('in',$ress['lab']),'zsqy'=>array('like','%2%')))->select();
//            var_dump($labelListShow);
                    foreach ($labelListShow as $item) {

                        M('Adve')->where(array('id'=>$item['id']))->setInc('visit',1);
                    }
                }

                if($member['types'] == 2 && $member['is_ok'] == 1)
                {
                    $this->success('ok');
                }else   if($member['types'] == 1)
                {
                    if(!$member['mobile'])
                    {
                        $this->success('nomobile');
                    }else
                    {
                        $this->success('ok');
                    }
                }else
                {
                    $this->success('ok');
                }

            } else {
                $this->error('更新用户数据失败');
            }


        } else {
            //如果没有就新建用户
            if(!$nickname)
            {
                $nickname = rand(66666,99999);
            }
            $rows['status'] = 1;
            $rows['is_delete'] = 0;
            $rows['create_time'] = time();
            $rows['last_login_time'] = time();
            $rows['nickname'] = $nickname;
            $rows['avatar'] = $avatar;
            $rows['app_openid'] = $app_openid;
            $rows['prov'] = $provId;
            $rows['city'] = $cityId;


            $res = M('Member')->add($rows);
            if($res)
            {
                $member = array();
                $member['member_id'] = $res;
                $member['status'] = 1;
                $token = sp_random_string(10);
                $time = 20 * 24 * 3600;//自动登录20天
                if (C('LOGIN_SAVE_STATUS')) {
                    cookie('member_login_token', get_token($member['member_id'], 'Member', $token, $time), $time);
                } else {
                    unset($member['pwd']);
                    session('user', $member);
                    cookie('user', $member, $time);
                }

                $this->success('nomobile');
//                $this->success('ok');
            }







//            $this->error('账号不存在');
        }
    }

//    public function testlogin(){
//        $mobile=I('mobile','');
//        if(!$mobile){
//            $this->error('请输入手机号');
//        }
//        $member=M('member')->where(array('mobile'=>$mobile,'is_delete'=>0,'status'=>1))->find();
//        $this->member=$member;
//        if($member){
//            unset($member['pwd']);
//            $time = 20 * 24 * 3600;//自动登录20天
//            session('user', $member);
//            cookie('user', $member, $time);
//            $this->success('登录成功');
//        }
//        else{
//            $this->error('登录失败');
//        }
//    }
    //退出
    public function exitAction()
    {

//        if ($this->user_id) {
//            M('Member')->where(array('id' => $this->user_id))->setField('token', '');
//        }
        $mid = $_SESSION['user']['member_id'];
        $types = M('Member')->where(array('id'=>$mid))->getField('types');
        if($types == 1)
        {
            $temp = '/Index/mnologin';
        }else   if($types == 2)
        {
            $temp = '/Index/dnologin';
        }
        session('user', NULL);
        cookie('member_login_token', NULL);

        $row['info'] = "退出成功";
        $row['url'] = $temp;
        $this->success($row);
    }
//ajax查看登录
    Public function examine_log(){
        if($this->member['member_id']){
            $this->success(1);
        }else{
            $this->success(2);
        }
    }

    //平台主页
//    public function plat()
//    {
//
//        $this->display();
//
//    }

}