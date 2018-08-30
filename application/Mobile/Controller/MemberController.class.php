<?php

namespace Mobile\Controller;

use Common\Model\CouponLogModel;
use Common\Model\KfLogModel;
use Common\Model\LabelModel;
use Common\Model\OrderModel;
use Mobile\Controller\MemberbaseController;
use Common\Model\MemberModel;
use Common\Model\MemberMoneyModel;
use Common\Model\IntegralLogModel;
use Common\Model\MessageModel;
use Think\Controller;

//会员控制器
class MemberController extends MemberbaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->LabelModel = new LabelModel();
        $this->message_model = new MessageModel();

    }

    //协议
    public function agre()
    {

        $mid = $this->member['member_id'];
        $mifo = M('Member')->field('types,is_ok')->where(array('id'=>$mid))->find();
        if($mifo['types'] == 2 && $mifo['is_ok'] == 0)
        {
            $this->showSQ = 1;
        }
        $site_options = getOptions('site_options');
        $this->content = $site_options['content3'];

        $this->display();

    }



    //医生开启咨询页面
    public function openzx()
    {
        if(IS_POST)
        {
            $isopen = intval(I('isopen'));
            if($isopen < 0)
            {
                $this->error('参数错误');
            }else
            {
                $res = M('Member')->where(array('id'=>$this->member['member_id'],'types'=>2))->save(array('iszx'=>$isopen));
            }
            if($res)
            {
                $this->success('操作已成功');
            }else
            {
                $this->error('操作失败');
            }
        }else
        {
            $this->iszx = M('Member')->where(array('id'=>$this->member['member_id'],'types'=>2))->getField('iszx');
            $site_options = getOptions('site_options');
            $this->info = $site_options['content1'];
            $this->display();
        }
    }
    //用户中心
    public function index()
    {

//        $labelListShow = $res = M('Label')->field('id,name')
//            ->where(array('uid'=>$this->member['member_id']))
//            ->select();
        $lab = M('MemberLab')->field('lab')->where(array('uid'=>$this->member['member_id']))->find();
        $labelListShow = array();
        if($lab)
        {
            $labelListShow = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$lab['lab'])))->select();
        }

        $this->lablist = $labelListShow;

        $info = M('Member')->field('create_time,status,nickname,types,is_ok,integral,mobile,avatar')->where(array('id'=>$this->member['member_id']))->find();



        $mid = $this->member['member_id'];
        $where['receive_member_id'] = $mid;
        $where['is_delete'] = 0;
        $where['is_read'] = 0;
        $where['type'] = array('in','8,9');
        $meDatas = M('Message')->field('id')->where($where)->select();

//        var_dump($meDatas);

//        $lists = $this->message_model->list_member_count($mid,$info['create_time']);

//var_dump($lists);


        //站内信
        $search['member_id'] = $this->member['member_id'];
        $member = M('Member')->where(array('id'=>$search['member_id']))->field('create_time,types,is_ok')->find();
        $search['reg_time'] = $member['create_time'];
        if($member['types'] == 2 && $member['is_ok'] == 1)
        {
            $search['member_type'] = 2;
        }else
        {
            $search['member_type'] = 3;
        }

        $lists = $this->message_model->list_member($search,'',1,10000);  //获取系统消息

        $lco = count($lists['list']);

        //已读的数据
        $ress = M('MemberMessage')->field('mes_id')->where(array('member_id'=>$this->member['member_id']))->select();
        $newRow = array();
        if($ress)
        {
            foreach ($ress as $ress) {
                $newRow[] = $ress['mes_id'];
            }
        }

        $rowNs = 0;
        foreach ($lists['list'] as $list)
        {
            if(!in_array($list['id'],$newRow))
            {
            $rowNs = $rowNs +  1;
            }
        }

        //回复
        if($meDatas)
        {
            foreach ($meDatas as $meData) {
                if(!in_array($meData['id'],$newRow))
                {
                $rowNs = $rowNs +  1;
                }
            }
        }

//var_dump($newRow);

//        var_dump($lists);
//        var_dump($meDatas);


        $ms = $lists + $meDatas;

        $this->messages = $rowNs;

        if($info['types'] == 2 && $info['is_ok'] == 0)
        {
            $info['showSQ'] = 1;
        }
        if($info['mobile'])
        {
            $info['isbdphone'] = 1;
        }else
        {
            $info['isbdphone'] = 0;
        }
        if($info['types'] == 2 && $info['is_ok'] == 1)  //说明已经是医生了
        {
            $this->info = $this->getDoctInfo();
            $this->display('zjindex');
        }else
        {
            $this->info = $info;
            $this->display();
        }


    }
    //专家个人中心
    public function zjindex()
    {



//        $follData = M('MemberFollo')->alias('MF')
//            ->field('M.avatar,M.truename,MI.zw,MI.hosp,MI.intro,MF.to_id,MF.id as follid')
//            ->join('LEFT JOIN __MEMBER__ M on MF.member_id = M.id')
//            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')


        $this->info = $this->getDoctInfo();
        $this->display();

    }
    private function getDoctInfo()
    {
        $info = M('Member')->alias('M')
            ->field('M.nickname,M.integral,MI.grjs,M.mobile,M.avatar,M.zy,MI.hosp,MI.zw,MI.intro')
            ->where(array('M.id'=>$this->member['member_id']))
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->find();
        $info['zy'] = $this->LabelModel->getNameById($info['zy']);

        $info['fss'] = M('MemberFollo')->where(array('to_id'=>$this->member['member_id']))->count();
        $info['gzs'] = M('MemberFollo')->where(array('member_id'=>$this->member['member_id']))->count();
        $info['wzs'] = M('Article')->where(array('author'=>$this->member['member_id'],'type'=>0,'is_delete'=>0))->count();
        $info['dts'] = M('Article')->where(array('author'=>$this->member['member_id'],'type'=>1,'is_delete'=>0))->count();

        return $info;
    }


    public function delColl()
    {
        $ids = I('ids');
        $res = M('MemberCollection')->where(array('id'=>array('in',$ids)))->delete();
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->error('操作失败');
        }

    }

    //我的收藏
    public function memberColl()
    {

        if(IS_AJAX) {
            $p = I('p')?I('p'):1;
            $pagesize = 10;
            $start = ($p - 1) * $pagesize;
            $mid = $this->member['member_id'];
            $data = M('MemberCollection')->alias('mc')
                ->field('mc.id,a.title,a.pls,mc.to_id,a.thumb,a.label,a.author,a.shownum')
                ->join('LEFT JOIN __ARTICLE__ a on a.id = mc.to_id')
                ->where(array('mc.member_id'=>$mid,'mc.is_delete'=>0,'mc.type'=>1))
                ->limit($start,$pagesize)
                ->order('mc.id desc')
                ->select();
            if($data)
            {
                foreach ($data as &$datum) {
                    $datum['label'] = M('Label')->where(array('id'=>$datum['label']))->getField('name');

                    if($datum['author'] == 0)
                    {
                        $datum['author'] = '官方发布';
                    }else
                    {
                        $datum['author'] = M('Member')->where(array('id'=>$datum['author']))->getField('nickname');
                    }



                    $arrs = explode(',',$datum['thumb']);
                    $datum['tp'] = count($arrs);
                    $arrsN = array();
                    foreach ($arrs as $k=>$items) {
                        if($k <= 2)
                        {
                            $arrsN[] = $items;
                        }
                    }
                    $datum['thumb'] = $arrsN;

                }
            }

            $count = M('MemberCollection')->alias('mc')
                ->join('LEFT JOIN __ARTICLE__ a on a.id = mc.to_id')
                ->where(array('mc.member_id'=>$mid,'mc.is_delete'=>0,'mc.type'=>1))
                ->count();

            $total_page = ceil($count / $pagesize);
            $result['list'] = $data;
            $result['p'] = $p;
            $result['total'] = $count;
            $result['pagesize'] = $pagesize;
            $result['total_page'] = $total_page;
            $this->success($result);

        }else
        {
            $this->display();
        }
    }
    //关注的医生
    public function follDoctor()
    {

        if(IS_AJAX)
        {
            $mid = $this->member['member_id'];
            $p = I('p')?I('p'):1;
            $pagesize = 10;
            $start = ($p-1) * $pagesize;
            $follData = M('MemberFollo')->alias('MF')
                ->field('M.id,M.avatar,M.truename,M.nickname,MI.zw,MI.hosp,MI.intro,MI.grjs,MF.to_id,MF.id as follid')
                ->join('LEFT JOIN __MEMBER__ M on MF.to_id = M.id')
                ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                ->limit($start,$pagesize)
                ->where(array('MF.member_id'=>$this->member['member_id']))->select();

            $str = '';
            if($follData)
            {
                foreach ($follData as $follDatum)
                {
                    if($follDatum['id'] == 0)
                    {
                        $opt = getOptions('site_options');
                        $follDatum['grjs'] = $opt['intro'];
                        $follDatum['avatar'] = $opt['pic'];
                        $follDatum['hosp'] = '番茄医学官方平台';
                    }
                    $counts = M('MemberFollo')->where(array('to_id'=>$follDatum['to_id']))->count();
                    $str .= '<a href="javascript:;" class="guanzhu_item ">';
                    $str .= '<div class="person_name flex_dom flex_item_mid">';
                    $str .= '<div onclick="jumpDoctIndex('.$follDatum['to_id'].')"  class="img">';
                    $str .= '<img  style="border-radius: 0.9rem;" src="'.$follDatum['avatar'].'"/>';
                    $str .= '<i class="tag"><img src="/themes/Public/Mobile/image/slice/dav1.png"/></i>';
                    $str .= '</div>';
                    $str .= '<div class="name flex_1">';
                    $str .= '<h4 class="flex_dom flex_item_between"><div class="left"><span>'.$follDatum['nickname'].'</span>
                            <em>粉丝:<i>'.$counts.'</i></em></div><button onclick="qxgz('.$follDatum['follid'].')">已关注</button></h4>';
                    $str .= '<p>认证:<em>'.$follDatum['hosp'].$follDatum['zw'].'</em></p>';
                    $str .= '</div>';
                    $str .= '</div>';
                    $str .= '<div onclick="jumpDoctIndex('.$follDatum['to_id'].')"  class="about_con text-ellipsis-2line">'.$follDatum['grjs'].'</div>';
                    $str .= '</a>';
                }
            }
            echo $str;

//           var_dump($follData);


        }else
        {
            $this->display();
        }

    }

    //取消关注
    public function qxFoll()
    {
        $id = intval(I('id'));
        $res = M('MemberFollo')->where(array('id'=>$id))->delete();
        if($res)
        {
            $this->success('取消关注成功');
        }else
        {
            $this->error('取消关注失败');
        }

    }
    //申请认证
    public function applyAuthFir()
    {
        if(IS_AJAX)
        {
            $data = I('');
            if(!$data['name'])
            {
                $this->error('姓名不能为空');
            }
            if(!$data['hosp'])
            {
                $this->error('医院不能为空');
            }
            if(!$data['ks'])
            {
                $this->error('科室不能为空');
            }
            if(!$data['zw'])
            {
                $this->error('职位不能为空');
            }
            if(!$data['choice'])
            {
                $this->error('专业不能为空');
            }
            if($data['xx'] != 1)
            {
                $this->error('请同意协议');
            }

            $memberRow['truename'] = $data['name'];
            $memberRow['zy'] = $data['choice'];
            $memberRow['types'] = 2;
            $memberRow['is_ok'] = 0;
            $option=get_site_options();
            $zdsh=$option['yssh'];
            M('Member')->startTrans();

            $mres = M('Member')->where(array('id'=>$this->member['member_id']))->save($memberRow);


            if(count(explode(',',$data['szList'])) != 2)
            {
                $this->error('身份证必须为两张!');
            }

            $memberInfoRow['zw'] = $data['zw'];
            $memberInfoRow['ks'] = $data['ks'];
            $memberInfoRow['hosp'] = $data['hosp'];
            $memberInfoRow['sq_t'] = time();
            $memberInfoRow['sfz'] = $data['szList'];
            $memberInfoRow['zyzgz'] = $data['zyzList'];
            $memberInfoRow['grjsimg'] = $data['qtList'];

            $memberInfoRow['intro'] = $data['intro'];

            $memberInfoRow['pid'] = $this->member['member_id'];

            $miid = M('MemberIntro')->field('id')->where(array('pid'=>$this->member['member_id']))->find();
            if($miid)
            {
                $mires = M('MemberIntro')->where(array('pid'=>$this->member['member_id']))->save($memberInfoRow);
            }else
            {
                $mires = M('MemberIntro')->add($memberInfoRow);
            }
            if($mires && $mres)
            {
                M('Member')->commit();

                // 2 自动审核
                if($zdsh == 2)
                {
                    $Mdata = M('Member')->field('mobile,nickname,last_login_time')->where(array('id'=>$this->member['member_id']))->find();
                    $row['user_login'] = $Mdata['mobile'];
                    $row['user_pass'] = '###fb947d45f98c36457dc549b68ac42386';
                    $row['user_nicename'] = $Mdata['nickname'];
                    $row['mobile'] = $Mdata['mobile'];
                    $row['last_login_time'] = $Mdata['last_login_time'];
                    $row['mid'] = $this->member['member_id'];
                    $row['user_type'] = 2;

                    $reid = M('Users')->add($row);
                    $AG['uid'] = $reid;
                    $AG['group_id'] = 23;
                    $AG['type'] = 1;
                    $areid = M('AuthGroupAccess')->add($AG);
                    $resI = M('MemberIntro')->where(array('pid'=>$this->member['member_id']))->save(array('opt_t'=>time()));
                    $res = M('Member')->where(array('id'=>$this->member['member_id']))->save(array('is_ok'=>1));
                    if($res && $resI)
                    {
                        $url = $_SERVER['HTTP_HOST'].'/admin';
                        $res =  send_sms('您可使用手机号+123456登录医生后台',$Mdata['mobile']);
                    }
                }

                $this->success('申请成功!请等待平台审核并联系');
            }else
            {
                M('Member')->rollback();
            }

        }else
        {

            $site_options = getOptions('site_options');
            $this->content = $site_options['content3'];

            $info = M('Member')->where(array('id'=>$this->member['member_id']))->field('mobile')->find();
            $this->mobile = $info['mobile'];
            $this->label = M('Label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();
            $this->display();
        }


    }


    public function publish_dyna()
    {
        if(IS_AJAX)
        {

            $minfo = M('Member')->field('status')->where(array('id'=>$this->member['member_id']))->find();
            if($minfo['status'] == 0)
            {
                $this->error('已被冻结，不能执行此操作!');
            }


            $data = I('');
            $row['author'] = $this->member['member_id'];
            $row['create_time'] = time();
            $row['content'] = $data['cont'];
            $row['img'] = $data['img'];
            $row['type'] = 1;

//            if($data['content_img']){
//                $img = array();
//                foreach($data['content_img'] as $k=>$y){
//                    $img[] = $y['value'];
//                }
//                $img = join(',',$img);
//                $row['thumb'] = $img;
//            }
            if($data['content_img'])
            {
                $row['thumb'] = $data['content_img'];
            }

            $res =  M('Article')->add($row);
            if($res)
            {


                $opt = getOptions('site_options');
                $integral = $opt['d_pl_dt'];
                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = intval($integral);
                $irow['change_type'] = 4;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + intval($integral);
                $irow['create_time'] = time();
                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',intval($integral));
                M('IntegralLog')->add($irow);


                $this->success('操作已成功');
            }else
            {
                $this->error('操作失败');
            }
        }else
        {
            $this->display();
        }
    }

    //申请认证 待审核
    public function applyAuth()
    {
        $mid = $this->member['member_id'];

        $info = M('Member')->alias('M')
            ->field("M.types,M.zy,M.iszx,M.nickname,M.truename,M.mobile,M.avatar,MI.sfz,
            MI.zyzgz,MI.zw,MI.grjs,MI.hosp,MI.intro,MI.ks,MI.grjsimg,MI.id")
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where(array('M.id'=>$mid,'M.types'=>2,'M.is_delete'=>0))
            ->find();
        $info['zy'] = M('Label')->where(array('id'=>$info['zy']))->getField('name');
        if($info['sfz'])
        {
            $info['sfz'] = explode(',',$info['sfz']);
        }
        if($info['zyzgz'])
        {
            $info['zyzgz'] = explode(',',$info['zyzgz']);
        }

        if($info['grjsimg'])
        {
            $info['grjsimg'] = explode(',',$info['grjsimg']);
        }

        $this->info = $info;
//        var_dump($info);
        $this->display();

    }

    //绑定手机
    public function bindPhone()
    {

        if(IS_POST)
        {

            $data = I('');
            //验证码
            $verify = M('Verify')->where(array('phone'=>$data['phone'],'type'=>4,'issy'=>0))->find();
            if(!$verify)
            {
                $this->error('验证码不存在');
            }
            if($verify['ext_time'] <= time())
            {
                $this->error('验证码已过期');
            }
            if($verify['code'] !=  md5($data['code']))
            {
                $this->error('验证码错误');
            }

            $row['mobile'] = $data['phone'];

            $resA = M('member')->where(array('mobile'=>$data['phone'],'is_delete'=>0))->getField('is_bind');
//            $resA = M('member')->where(array('mobile'=>$data['phone']))->getField('id');
            if($resA == 1)  //判断是否绑定
            {
                $this->error('该手机号已绑定');
            }else
            {

                $resIS = M('member')->where(array('mobile'=>$data['phone'],'is_delete'=>0))->getField('id');


                if($resIS)
                {
                    $app_openid = M('member')->where(array('id'=>$this->member['member_id']))->getField('app_openid');

                    $newRO['app_openid'] = $app_openid;
                    $newRO['is_bind'] = 1;

                    $res = M('member')->where(array('id'=>$resIS))->save($newRO);

                    if($res)
                    {

                        M('member')->where(array('id'=>$this->member['member_id']))->delete();

                        $this->member['member_id'] = $resIS;
                        $this->success('操作已成功');
                    }else
                    {
                        $this->error('操作失败');
                    }


                }else
                {
                    $res = M('member')->where(array('id'=>$this->member['member_id']))->save($row);

                    if($res)
                    {
                        $this->success('操作已成功');
                    }else
                    {
                        $this->error('操作失败');
                    }
                }

            }




        }else
        {
            $site_options = getOptions('site_options');
            $this->content = $site_options['content'];
            $this->display();
        }

    }





    //我的资料
    public function memberInfo()
    {
        if(IS_POST)
        {
            $nickname = I('nickname');
            if(!$nickname)
            {
                $this->error('名称不能为空');
            }

            if(I('types') == 2)  //医生
            {
                $row['nickname'] = $nickname;
                $row['avatar'] = I('avatar');
                $res = M('Member')->where(array('id'=>$this->member['member_id']))->save($row);
                $resU = M('MemberIntro')->where(array('pid'=>$this->member['member_id']))->save(array('grjs'=>I('intro')));

                if($res || $resU)
                {
                    $this->success('操作已成功');
                }else
                {
                    $this->error('操作失败');
                }

            }else   //会员
            {
                $row['nickname'] = $nickname;
                $row['avatar'] = I('avatar');
                $res = M('Member')->where(array('id'=>$this->member['member_id']))->save($row);
                if($res)
                {
                    $this->success('操作已成功');
                }else
                {
                    $this->error('操作失败');
                }
            }

        }else
        {
            $this->info = M('Member')->where(array('id'=>$this->member['member_id']))->find();

            $this->display();
        }

    }

    public function memberInfoData()
    {
        if(IS_AJAX)
        {
            $id = $this->member['member_id'];
//            $info = M('Member')->field('types,zy,iszx,nickname,mobile,avatar')->where(array('id'=>$this->member['member_id']))->find();

            $info = M('Member')->alias('M')
                ->field("M.types,M.zy,M.iszx,M.nickname,M.truename,M.mobile,M.avatar,MI.zw,MI.hosp,M.avatar,MI.intro,MI.grjs")
                ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                ->where(array('M.id'=>$id,'M.is_delete'=>0))
                ->find();
            $info['zy'] = M('Label')->where(array('id'=>$info['zy']))->getField('name');
            $info['iszx'] = $info['iszx'] == 1 ? '未开启' : '已开启';
//          var_dump($info);
            $this->success($info);
        }


    }


    public function memberInte()
    {
        $mid = $this->member['member_id'];
        if(IS_POST)
        {
//            变动方式  1. 评论，2  点赞，3分享，4，发布动态，5 回复评论，6，转发文章，7 发布文章

            $changeTypeArray = array(1=>'评论',2=>'点赞',3=>'分享',4=>'发布动态',5=>'回复评论',6=>'转发文章',7=>'发布文章');
            $where['member_id'] = $mid;
            $pagesize = 10;
            $start = (I('p')-1) * $pagesize;
            $memberInte = M('IntegralLog')->field('create_time,change,change_status,change_type')->order('id desc')->limit($start,$pagesize)->where($where)->select();
//            var_dump($memberInte);
            $str = '';

            if($memberInte)
            {
                foreach ($memberInte as $item)
                {
                    $str .='<div class="jifen_item flex_dom flex_item_between flex_item_mid">';
                    $str .='<span><em>'.$changeTypeArray[$item['change_type']].'</em><i class="m-l-15">+'.$item['change'].'</i></span>';
                    $str .='<b>'.date('Y.m.d H:i:s',$item['create_time']).'</b>';
                    $str .=' </div>';
                }
            }
            echo $str;
        }else
        {
            $info['inte'] = M('Member')->where(array('id'=>$mid))->getField('integral');
            $this->info = $info;
            $this->display();
        }

    }

    public function getCode()
    {
        $phone = I('post.phone');
        if(strlen($phone) != 11)
        {
            $this->error('请输入正确手机号');
        }else
        {
            $res = $this->sendCode($phone);
            if($res == 1)
            {
                $this->success("发送成功！");
            }elseif ($res == '-15')
            {
                $this->error('同一手机号码一天只能发送15条!');
            }else{
                $this->error($res);
            }
        }
    }
    public function sendCode($mobile)
    {
        $ip = get_client_ip();
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d') . ' 23:59:59');
        $where = array();
        $where['phone'] = $mobile;
        $where['addtime'] = array(array('gt', $start_time), array('lt', $end_time));
        $total = M('verify')->where($where)->count();
        if ($total > 15)
        {
            return '-15';
        }
        session('SMS_' . $mobile, time());
        $code = rand(100000, 999999);
        $md5_code = md5($code);
        $ext_time = strtotime("+300 second");//有效期5分钟
        $data = array(
            'phone' => $mobile,
            'code' => $md5_code,
            'ext_time' => $ext_time,
            'addtime' => time(),
            'ip' => $ip,
            'type' => 4
        );
        if (M('Verify')->add($data))
        {
            $msg = send_sms('验证码为：' . $code , $mobile);
//            $msg = send_sms(array('code'=>$code),$mobile,'SMS_120115624');
            if ($msg) {
                M('Verify')->where(array('phone' => $mobile, 'code' => array('neq', $md5_code)))->save(array('issy' => 1));
                return 1;
            } else {
                return $msg;
            }
        }
    }

    //医生认证资料修改
    public function dusr_edit(){
        $data=I();
        $Member = M('MemberIntro');

        $Member->startTrans();
        try{
            $re=$Member->where(array('id'=>$data['id']))->save(array('hosp'=>$data['hosp'],'ks'=>$data['ks'],'zw'=>$data['zw'],'intro'=>$data['intro'],'grjsimg'=>$data['grjsimg']));
//            $this->error($Member->_sql());
//            exit;
            if($re==0){
                E("您没有修改任何内容,不能保存!");
            }else{
                $Member->commit();
                $this->success("保存成功");
            }

        }catch(\Exception $e){
            $Member->rollback();
            $this->error($e->getMessage());
        }


    }
}