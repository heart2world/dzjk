<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 *      会员类
 * Date: 2016/6/5 0005
 * Time: 上午 10:19
 */

namespace Common\Model;

use Common\Model\CommonModel;
use Common\Model\MemberAddrModel;

class MemberModel extends CommonModel
{

    protected $_validate = array(
        array('name','require','客户名称必须填写！',1),
        array('name','1,30','客户名称长度1-30字',1,'length'),
        array('cate_id','require','所属商品分类必须选择！',1),
        array('salesman_id','require','所属业务员必须选择！',1),
        array('province','require','省份必须选择！',1),
        array('city','require','城市必须选择！',1),
        array('area','require','区域必须选择！',1),
        array('address','require','地址必须输入！',1),
        array('address','1,60','地址长度1-60字',1,'length'),

        array('contact_name','require','客户联系人必须输入！',1),
        array('contact_name','1,15','客户联系人长度1-15字',1,'length'),
        array('contact_tel','require','联系电话必须输入！',1),
        array('contact_tel','1,20','联系人电话长度1-20字',1,'length'),
        array('status','require','客户状态必须选择！',1),

        array('have_car_number','require','拥有配送车辆数量必须输入！',1),
        array('have_salesman_number','require','终端业务员数量必须输入！',1),
        array('content','require','主营产品及品牌必须输入！',1),
        array('content','1,60','主营产品及品牌长度1-60字',1,'length'),
        array('year_total','require','年营业额必须输入！',1),
    );


    protected $_auto = array (
        array('create_time','time',1,'function'),
    );

    //验证手机唯一
    public function check_unique_mobile($mobile){
        if($this->where(array('status'=>array('neq',-1),'mobile'=>$mobile))->count()>0){
            return false;
        }else{
            return true;
        }
    }



    public  function getList($search,$p,$mid,$pagesize = 10,$order='create_time desc')
    {

        if($search['types'] == 'ygz')  //已关注的
        {
            if($search['type']  && $search['type'] != 'all')  //医生专业
            {
                $whereA['M.zy'] = $search['type'];
            }
            $whereA['MF.member_id'] = $mid;

            $count =  M('MemberFollo')->alias('MF')
                     ->where($whereA)
                     ->join('LEFT JOIN __MEMBER__ M on M.id = MF.to_id')
                     ->count();

            $info = M('MemberFollo')->alias('MF')
                ->where($whereA)
                ->join('LEFT JOIN __MEMBER__ M on M.id = MF.to_id')
                ->page($p,$pagesize)
                ->select();

            if($info)
            {
                foreach ($info as &$items)
                {

                    $minfo = array();
                    $minfo = M('MemberIntro')->where(array('pid'=>$items['to_id']))->field('zw,grjs,hosp')->find();
                    if($minfo)
                    {
                        $items['zw'] = $minfo['zw'];
                        if($items['to_id'] == 0)
                        {
                            $opt = getOptions('site_options');
                            $items['grjs'] = $opt['intro'];
                            $items['avatar'] = '/logo.png';
                            $items['hosp'] = '番茄医学官方平台';

                        }else
                        {
                            $items['grjs'] = $minfo['grjs'];
                            $items['hosp'] = $minfo['hosp'];
                        }



                    }
                }
            }
        }else
        {

        if($search['type']  && $search['type'] != 'all')  //医生专业
        {
            $where['M.zy'] = $search['type'];
        }
        $where['M.types'] = 2;
        $where['M.is_ok'] = 1;
        $where['M.is_delete'] = 0;
        $where['M.isshow_lab'] = 0;

            $count =  M('Member')->alias('M')
                ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                ->join('LEFT JOIN __LABEL__ L on M.zy = L.id')->count();

        $info = M('Member')->alias('M')
            ->field("M.id,M.zy,M.nickname,L.name as labname,M.truename,MI.pid,M.avatar,MI.zw,MI.grjs,MI.hosp,MI.intro")
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->join('LEFT JOIN __LABEL__ L on M.zy = L.id')
            ->where($where)
            ->order($order)
            ->page($p,$pagesize)
            ->select();

        if($info)
        {
            foreach ($info as &$itema) {
//                echo $itema['id'];
                if($itema['id'] == 0)
                {
                     $opt = getOptions('site_options');
                    $itema['grjs'] = $opt['intro'];
                    $itema['avatar'] = $opt['pic'];
                    $itema['hosp'] = '番茄医学官方平台';

                }
            }
        }
        }




        if($info)
        {
            foreach ($info as &$item)
            {
                $item['fss'] = M('MemberFollo')->where(array('to_id'=>$item['id']))->count();
                $res = M('MemberFollo')->where(array('member_id'=>$mid,'to_id'=>$item['id']))->find();
                $item['isgz'] = $res > 0 ? 1 : 0;
                $item['isshow'] = 1;
                if($mid == $item['id'])
                {
                    $item['isshow'] = 0;
                }
            }
        }

        $total_page = ceil($count / $pagesize);
        $result['list'] = $info;
        $result['p'] = $p;
        $result['total'] = $count;
        $result['pagesize'] = $pagesize;
        $result['total_page'] = $total_page;
        return $result;
    }







    //会员详情
    public function detail($id){

        $where['m.id'] = $id;
        $where['m.is_delete'] = 0;
        $info = $this->alias('m')
            ->field('m.*,MI.*,r.region_name as priovince,r1.region_name as city1')
            ->join('LEFT JOIN __REGION__ r on r.region_id=m.prov')
            ->join('LEFT JOIN __REGION__ r1 on r1.region_id=m.city')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on m.id = MI.pid')
            ->where($where)
            ->find();
        if($info)
        {
            $info['statusText'] = $info['status'] == 0 ? '冻结' : '正常';


            if($info['is_ok'] == 2)
            {
                $info['is_ok_text'] = '已拒绝';
            }else if($info['is_ok'] == 1)
            {
                $info['is_ok_text'] = '已通过';
            }else if($info['is_ok'] == 0)
            {
                $info['is_ok_text'] = '待审核';
            }

            $info['create_time'] = date('Y-m-d H:i:s',$info['sq_t']);
            $info['last_login_time'] = date('Y-m-d H:i:s',$info['last_login_time']);
            $info['prov'] = $info['priovince'].$info['city1'];
            $info['iszx'] = $info['iszx'] == 0 ? '开启' : '未开启';

            $info['sfz'] = explode(',',$info['sfz']);
            $info['zyzgz'] = explode(',',$info['zyzgz']);
        }

        return $info;
    }

    public  function get_list($search=array(),$type,$page=1,$pageSize=10){

        if(empty($order))
        {
            $order = 'MI.sq_t desc';
        }
        $where = array();

        if(2 == $type)
        {
            if($search['nickname']){
                $where['M.truename|M.nickname|M.mobile'] = array('like',"%{$search['nickname']}%");
            }
        }else
        {
            if($search['nickname']){
                $where['M.nickname|M.mobile|MI.hosp'] = array('like',"%{$search['nickname']}%");
            }
        }


        if($search['status']!=100){
            $where['M.status'] = $search['status'];
        }
        if($type == 2)
        {
            if($search['zy'] && $search['zy'] != 'all')
            {
                $where['M.zy'] = $search['zy'];
            }
            if($search['iszx'] == 1)
            {
                $where['M.iszx'] = 0;
            }
            if($search['iszx'] == 2)
            {
                $where['M.iszx'] = 1;
            }

            if($search['is_ok'] >= 0 && $search['is_ok'] != 100)
            {
                $where['M.is_ok'] = $search['is_ok'];
            }
        }
        if($search['city'] && $search['city'] != 'all')
        {
            $where['M.city'] = $search['city'];
        }
        if($search['prov'] && $search['prov'] != 'all')
        {
            $where['M.prov'] = $search['prov'];
        }

        $where['M.is_delete'] = 0;

        if($type == 1 )
        {
            $where['types'] = array('in','1,2');
            $where['is_ok'] = array('in','0,2');

        }
        if($type == 2)
        {
            $where['types'] = intval(2);
            $where['is_ok'] = array('in','0,1,2');
//            $where['types'] = array('in','1,2');
//            $where['is_ok'] = array('in','0,2');

        }



//        $where['M.types'] = $type;
//        var_dump($where);
        $count = $this->alias('M')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where($where)->count();

        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $list = $this->alias('M')
            ->field('M.*,MI.sq_t')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')

            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();



        foreach($list as $key=>&$vo){
            $vo['first'] = date('Y-m-d H:i:s',$vo['create_time']);
            $vo['create_time'] = date('Y-m-d H:i:s',$vo['sq_t']);
            if($vo['last_login_time'])
            {
                $vo['last_login_time'] = date('Y-m-d H:i:s',$vo['last_login_time']);
            }

//            if($vo['id'] == 0)
//            {
//                $opti = getOptions('site_options');
//
//            }



            $uslogin = M('Users')->where(array('mid'=>$vo['id']))->getField('last_login_time');
            if($vo['last_login_time'] > $uslogin)
            {
                $vo['last_login_time'] = $vo['last_login_time'];
            }else
            {
                $vo['last_login_time'] = $uslogin;
            }
            if($vo['last_login_time'] == 0)
            {
                $vo['last_login_time'] = '';
            }

            $vo['status_text'] = $vo['status'] == 0?'冻结':'正常';
            $vo['parent_time'] = date('Y-m-d H:i:s',$vo['parent_time']);
            if($type == 2)
            {
                $vo['iszx_text'] = $vo['iszx'] == 1 ? '否' : '是';
                if($vo['is_ok'] == 2)
                {
                    $vo['is_ok_text'] = '已拒绝';
                }else if($vo['is_ok'] == 1)
                {
                    $vo['is_ok_text'] = '已通过';
                }else if($vo['is_ok'] == 0)
                {
                    $vo['is_ok_text'] = '待审核';
                }


                $vo['zy'] = D('Label')->where(array('id'=>$vo['zy']))->getField('name');
            }

        }

        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    public  function get_list1($search=array(),$type,$page=1,$pageSize=10){

        if(empty($order))
        {
            $order = 'M.id desc';
        }
        $where = array();
        if($search['nickname']){
            $where['M.nickname|M.mobile'] = array('like',"%{$search['nickname']}%");
        }

        if($search['status']!=100){
            $where['M.status'] = $search['status'];
        }
        if($type == 2)
        {
            if($search['zy'] && $search['zy'] != 'all')
            {
                $where['M.zy'] = $search['zy'];
            }
            if($search['iszx'] && $search['iszx'] != 'all')
            {
                $where['M.iszx'] = $search['iszx'];
            }
            if($search['is_ok'] >= 0 && $search['is_ok'] != 100)
            {
                $where['M.is_ok'] = $search['is_ok'];
            }
        }
        if($search['city'] && $search['city'] != 'all')
        {
            $where['M.city'] = $search['city'];
        }
        if($search['prov'] && $search['prov'] != 'all')
        {
            $where['M.prov'] = $search['prov'];
        }

        $where['M.is_delete'] = 0;
        $where['M.types'] = $type;
//        var_dump($where);
        $count = $this->alias('M')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where($where)->count();

        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $list = $this->alias('M')
            ->field('*')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')

            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();



        foreach($list as $key=>&$vo){
            $vo['create_time'] = date('Y-m-d H:i:s',$vo['create_time']);
            $vo['last_login_time'] = date('Y-m-d H:i:s',$vo['last_login_time']);
            $vo['status_text'] = $vo['status'] == 0?'冻结':'正常';
            $vo['parent_time'] = date('Y-m-d H:i:s',$vo['parent_time']);
            if($type == 2)
            {
                $vo['iszx_text'] = $vo['iszx'] == 1 ? '否' : '是';
                if($vo['is_ok'] == 2)
                {
                    $vo['is_ok_text'] = '已拒绝';
                }else if($vo['is_ok'] == 1)
                {
                    $vo['is_ok_text'] = '已通过';
                }else if($vo['is_ok'] == 0)
                {
                    $vo['is_ok_text'] = '待审核';
                }


                $vo['zy'] = D('Label')->where(array('id'=>$vo['zy']))->getField('name');
            }

        }

        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    //冻结会员
    public function close_member($ids){
        $ids=ids_to_ids($ids);
        if($ids){
            $res=$this->where(array('id'=>array('in',$ids)))->setField('status',0);
            if($res!==false){
                return true;
            }else{
                $this->error='冻结失败';
                return false;
            }
        }else{
            $this->error='参数错误';
            return false;
        }
    }

    //启用会员
    public function open_member($ids){
        $ids=ids_to_ids($ids);
        if($ids){
            $res=$this->where(array('id'=>array('in',$ids)))->setField('status',1);
            if($res!==false){
                return true;
            }else{
                $this->error='冻结失败';
                return false;
            }
        }else{
            $this->error='参数错误';
            return false;
        }
    }




    public function login($username,$password,$is_auto_login=0)
    {
        if (empty($username)) {
            $this->error="请输入手机号码";
            return false;
        }
        if (!isMobile($username)) {
            $this->error="手机号码格式错误";
            return false;
        }
        if (empty($password)) {
            $this->error="请输入密码";
            return false;
        }

        $member = $this->field("id as member_id,status,login_pwd,mobile")->where(array('mobile' => $username, 'is_delete' =>0))->find();
        if ($member) {

            if($member['login_pwd']!=sp_password($password)&&$is_auto_login==0){
                $this->error="密码错误";
                return false;
            }
            else if($member['login_pwd']!=$password&&$is_auto_login==1){
                $this->error="密码错误";
                return false;
            }
            else if ($member['status'] == 0) {
                $this->error="帐号被冻结";
                return false;
            }
            session("user",$member);
            $update['last_login_time']=time();
            $update['last_login_ip']=get_client_ip();
            $token=sp_random_string(10);
            $update['token']=md5($token.'_'.$member['member_id']);
            try{
                $this->startTrans();
                $res=$this->where(array('id'=>$member['member_id']))->save($update);
                if($res!==false){
                    $this->commit();
                    //$this->change_path($member,$p_code);
                    $time=20*24*3600;//自动登录20天
                    if(C('LOGIN_SAVE_STATUS')){
                        cookie('member_login_token',get_token($member['member_id'],'Member',$token,$time),$time);
                    }else{
                        unset($member['pwd']);
                        session('user',$member);
                        cookie('user',$member,$time);
                    }
                    return true;
                }else{
                    E('更新用户数据失败');
                }
            }catch (\Exception $e){
                $message="用户 $username 登录失败,message:".$e->getMessage();
                \Think\Log::write($message,'ERR');
                $this->error="登录失败";
                $this->rollback();
                return false;
            }
        } else {
            $this->error="账号不存在";
            return false;
        }
    }


    public function register($data,$wx_openid='')
    {
        if (empty($data['mobile'])) {
            $this->error="请输入手机号码";
            return false;
        }
        if (!isMobile($data['mobile'])) {
            $this->error="手机号格式不正确";
            //$this->setErrorCode(2002);//手机号格式不正确
            return false;
        }
        if (empty($data['sms_verify'])) {
            $this->error="请输入手机短信验证码";
            return false;
        }

        if (empty($data['password'])) {
            $this->error="请输入密码";
            return false;
        }
        if (mb_str_len($data['password'])<6||mb_str_len($data['password'])>20) {
            $this->error="密码长度6-20个字符";
            return false;
        }
        if (empty($data['password_r'])) {
            $this->error="请输入确认密码";
            return false;
        }
        if (mb_str_len($data['password_r'])<6||mb_str_len($data['password_r'])>20) {
            $this->error="确认密码长度6-20个字符";
            return false;
        }
        if ($data['password']!=$data['password_r']) {
            $this->error="两次输入密码不一致";
            return false;
        }

        if ($this->memberCheck($data['mobile'])) {
            $this->error="该手机号码已被注册";
            //$this->setErrorCode(2013);//该手机号码已被注册
            return false;
        }
        if($data['is_app']==1){
            if($data['unionid']){
                $wx_count=$this->where(array('unionid'=>$data['unionid'],'is_delete'=>0))->count();
                if($wx_count>0){
                    $this->error="该微信已经绑定其他账户了";
                    return false;
                }else{
                    $data['wx_nickname']=$data['nickname'];
                    if(empty($data['nickname'])){
                        $data['nickname']=$data['mobile'];
                    }
                    if(empty($data['headimg'])){
                        $data['headimg']='/themes/TPL/Public/Mobile/image/personHeader.png';
                    }
                    $data['app_openid']=$wx_openid;
                }
            }else{
                $this->error="unionid不能为空";
                return false;
            }
        }else{
            if($wx_openid&&$data['unionid']){
                $wx_count=$this->where(array('unionid'=>$data['unionid'],'is_delete'=>0))->count();
                if($wx_count>0){
                    $this->error="该微信已经绑定其他账户了";
                    return false;
                }else{
                    $wx_info=M('Wxinfo')->where(array('openid'=>$wx_openid))->find();
                    $data['nickname']=$wx_info['nickname'];
                    $data['headimg']=$wx_info['headimgurl'];
                    $data['wx_nickname']=$wx_info['nickname'];
                    $data['openid']=$wx_openid;
                }
            }else{
                $data['nickname']=$data['mobile'];
                $data['headimg']='/themes/TPL/Public/Mobile/image/personHeader.png';
            }
        }


        $ver = $this->check_mobile_verify(array('code' => $data['sms_verify'], 'mobile' => $data['mobile']), 0);
        if (!$ver) {
            $this->error="短信验证码错误";
            //$this->setErrorCode(2014);//短信验证失败
            return false;
        }



        $data['login_pwd'] = sp_password($data['password']);
        //$data['update_time'] = time();
        $data['create_time'] = time();
        $data['token'] = md5(uniqid());
        //$data['token_expire_time'] = strtotime("+7200 second");//2小时过期
        $data['last_login_time'] = time();
        $data['last_login_ip'] = get_client_ip();
        $p_code=(int)session("p_code");
        try {
            $this->startTrans();
            $res = $this->add($data);
            if ($res !== false) {
                $token=sp_random_string(10);
                $token=md5($token.'_'.$res);
                //$res1 = M('MemberTokenLog')->add(array('mobile' => $data['mobile'], 'create_time' => time()));
                $res2 = M('Verify')->where(array('phone'=>$data['mobile'],'type'=>0,'code'=>md5($data['sms_verify'])))->setField('issy',1);
                if ($res2) {
                    $this->where(array('id'=>$res))->save(array('token'=>$token));
                    $this->commit();
                    $member=$this->field("*,id as member_id")->where(array('id'=>$res))->find();
                    $time=20*24*3600;//自动登录20天
                    if(C('LOGIN_SAVE_STATUS')){
                        cookie('member_login_token',get_token($res,'Member',$token,$time),$time);
                    }else{
                        session('user',$member);
                        cookie('user',$member,$time);
                    }
                    $this->change_path($member,$p_code,1);
                    return true;
                    //return array('token'=>$data['token'],'token_expire_time'=>$data['token_expire_time']);
                } else {
                    E('更新短信验证码失败');
                }
            } else {
                E('添加用户数据失败'.json_encode($data));
            }
        } catch (\Exception $e) {
            $message = "用户{$data['mobile']}注册失败,message:" . $e->getMessage();
            \Think\Log::write($message, 'ERR');
            //$this->setErrorCode(2015);//注册失败
            $this->error=$e->getMessage();
            $this->rollback();
            return false;
        }

    }

    /**
     * [check_verify 短信验证]
     * @return [bool] [验证结果]
     */
    public function check_mobile_verify($data,$type=0){
        $code = md5($data['code']);
        $where=array();
        $where['issy']=0;
        $where['type']=$type;
        $where['code']=$code;
        $where['phone']=$data['mobile'];
        $where['ext_time']=array('egt',time());
        $total=M('verify')->where($where)->count();
        if ($total == 0) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * [memberCheck 检测用户是否存在
     * @param  [string] 用户注册的mobile
     * @return [bool]    [存在返回true,不存在返回false]
     */
    public function memberCheck($mobile){
        $res=$this->where(array('mobile'=>$mobile,'is_delete'=>0))->count();
        if ($res>0){
            return true;
        }else{
            return false;
        }
    }


    public function forget_pwd($data)
    {
        if (empty($data['mobile'])) {
            $this->error="请输入手机号码";
            return false;
        }
        if (!isMobile($data['mobile'])) {
            $this->error="手机号格式不正确";
            return false;
        }
        if (empty($data['sms_verify'])) {
            $this->error="请输入手机短信验证码";
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入新密码";
            return false;
        }
        if(mb_str_len($data['password'])<6||mb_str_len($data['password'])>20){
            $this->error="密码6-20个字符";
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入重复密码";
            return false;
        }
        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            return false;
        }

//        if (empty($data['verify'])) {
//            $this->error="请输入图形验证码";
//            return false;
//        }


        if (!$this->memberCheck($data['mobile'])) {
            $this->error="手机号码错误";
            return false;
        }

        $ver = $this->check_mobile_verify(array('code' => $data['sms_verify'], 'mobile' => $data['mobile']), 1);
        if (!$ver) {
            $this->error="短信验证码错误";
            return false;
        }
//        if (!sp_check_verify_code()) {
//            $this->error="图形验证码错误";
//            return false;
//        }
        $res1=M('Verify')->where(array('phone'=>$data['mobile'],'type'=>1,'code'=>md5($data['sms_verify'])))->setField('issy',1);
        $password=sp_password($data['password']);
        $res2=$this->where(array('mobile'=>$data['mobile'],'is_delete'=>0))->setField('login_pwd',$password);
        if($res1&&$res2!==false){
            return true;
        }else{
            $this->error="重置失败";
            return false;
        }
    }

    //修改登录密码
    public function update_login_pwd($data){
        if(empty($data['mobile'])){
            $this->error="请通过验证后再设置密码";
            //$this->setErrorCode(2033);//请输入新登录密码
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入新密码";
            //$this->setErrorCode(2033);//请输入新登录密码
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入重复密码";
            //$this->setErrorCode(2033);//请输入新登录密码
            return false;
        }

        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            //$this->setErrorCode(2035);//两次输入密码不一致
            return false;
        }
        $password=sp_password($data['password']);
        $res=$this->where(array('mobile'=>$data['mobile'],'is_delete'=>0))->setField('login_pwd',$password);
        if($res!==false){
            return true;
        }else{
            $this->error="重置失败";
            return false;
        }
    }


    //修改登录密码
    public function set_login_pwd($data){
        if(empty($data['member_id'])){
            $this->error="请登录后操作";
            return false;
        }
        if(empty($data['sms_verify'])){
            $this->error="请输入短信验证码";
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入新密码";
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入重复密码";
            return false;
        }

        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            return false;
        }
        $ver = $this->check_mobile_verify(array('code' => $data['sms_verify'], 'mobile' => $data['mobile']), 3);
        if (!$ver) {
            $this->error="手机短信验证码错误";
            return false;
        }

        $password=sp_password($data['password']);
        $res=$this->where(array('id'=>$data['member_id']))->setField('login_pwd',$password);
        if($res!==false){
            M('Verify')->where(array('phone'=>$data['mobile'],'type'=>3,'code'=>md5($data['sms_verify'])))->setField('issy',1);
            return true;
        }else{
            $this->error="设置失败";
            return false;
        }
    }


    //修改支付密码
    public function update_pay_pwd($data,$pwd){
        if(empty($data['member_id'])){
            $this->error="请登录后操作";
            return false;
        }
        if(empty($data['old_password'])){
            $this->error="请输入原支付密码";
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入新支付密码";
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入确认支付密码";
            return false;
        }

        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            return false;
        }

        if(empty($pwd)){
            $this->error="请设置支付密码后再修改";
            return false;
        }
        $password=sp_password($data['old_password']);
        if($password!=$pwd){
            $this->error="原支付密码错误";
            return false;
        }else{
            $new_password=sp_password($data['password']);
            $res=$this->where(array('id'=>$data['member_id']))->setField('pwd',$new_password);
            if($res!==false){
                return true;
            }else{
                $this->error="修改失败";
                return false;
            }
        }

    }


    //设置支付密码
    public function set_pay_pwd($data){
        if(empty($data['member_id'])){
            $this->error="请登录后操作";
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入支付密码";
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入确认支付密码";
            return false;
        }

        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            return false;
        }
        $password=sp_password($data['password']);
        $res=$this->where(array('id'=>$data['member_id']))->setField('pwd',$password);
        if($res!==false){
            return true;
        }else{
            $this->error="设置失败";
            return false;
        }
    }

    //找回支付密码
    public function forget_pay_pwd($data){
        if(empty($data['mobile'])){
            $this->error="请通过验证后再设置密码";
            return false;
        }
        if(empty($data['sms_verify'])){
            $this->error="请输入短信验证码";
            return false;
        }
        if(empty($data['password'])){
            $this->error="请输入新密码";
            //$this->setErrorCode(2033);//请输入新登录密码
            return false;
        }
        if(empty($data['password_r'])){
            $this->error="请输入重复密码";
            //$this->setErrorCode(2033);//请输入新登录密码
            return false;
        }

        if($data['password']!=$data['password_r']){
            $this->error="两次输入密码不一致";
            //$this->setErrorCode(2035);//两次输入密码不一致
            return false;
        }
        $ver = $this->check_mobile_verify(array('code' => $data['sms_verify'], 'mobile' => $data['mobile']), 3);
        if (!$ver) {
            $this->error="手机短信验证码错误";
            return false;
        }

        $password=sp_password($data['password']);
        $res=$this->where(array('mobile'=>$data['mobile'],'is_delete'=>0))->setField('pwd',$password);
        if($res!==false){
            M('Verify')->where(array('phone'=>$data['mobile'],'type'=>3,'code'=>md5($data['sms_verify'])))->setField('issy',1);
            return true;
        }else{
            $this->error="重置失败";
            return false;
        }
    }

    //获取所有下线
    public  function get_child($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order='m.parent_time, m.id desc';
        }
        $where=array();
        $where['m.path']=array('like',"{$search['path']}-%");
        $where['m.is_delete']=0;

        $count = $this->alias('m')
            ->where($where)->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field="m.*,getOrderPayPrice(m.id) as buy_amount,getOrderNum(m.id) as buy_num";
        $list = $this->alias('m')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
//        echo $this->getLastSql();
//        exit;
        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $list[$key]['dengji']=get_child_type($search['path'],$vo['path']);
        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    //修改绑定手机
    public function edit_mobile($data)
    {
        if (empty($data['mobile'])) {
            $this->error="请输入手机号码";
            return false;
        }
        if (!isMobile($data['mobile'])) {
            $this->error="手机号格式不正确";
            //$this->setErrorCode(2002);//手机号格式不正确
            return false;
        }
        if (empty($data['sms_verify1'])) {
            $this->error="请输入手机短信验证码";
            return false;
        }
        if (empty($data['sms_verify2'])) {
            $this->error="请输入手机短信验证码";
            return false;
        }


        if ($this->memberCheck($data['mobile'])) {
            $this->error="该手机号码已被注册";
            //$this->setErrorCode(2013);//该手机号码已被注册
            return false;
        }
        $ver = $this->check_mobile_verify(array('code' => $data['sms_verify1'], 'mobile' => $data['old_mobile']), 3);
        $ver1 = $this->check_mobile_verify(array('code' => $data['sms_verify2'], 'mobile' => $data['mobile']), 2);

        if (!$ver) {
            $this->error="原手机号短信验证码错误";
            //$this->setErrorCode(2014);//短信验证失败
            return false;
        }
        if (!$ver1) {
            $this->error="新手机号短信验证码错误";
            //$this->setErrorCode(2014);//短信验证失败
            return false;
        }

        try {
            M('Verify')->where(array('phone'=>$data['old_mobile'],'type'=>3,'code'=>md5($data['sms_verify1'])))->setField('issy',1);
            M('Verify')->where(array('phone'=>$data['mobile'],'type'=>2,'code'=>md5($data['sms_verify2'])))->setField('issy',1);
            $res = $this->where(array('id'=>$data['member_id']))->setField("mobile",$data['mobile']);
            if ($res !== false) {
                return true;
            } else {
                E('更新手机号失败'.json_encode($data));
            }
        } catch (\Exception $e) {
            $message = "用户{$data['mobile']}更新手机号,message:" . $e->getMessage();
            \Think\Log::write($message, 'ERR');
            $this->error=$e->getMessage();
            return false;
        }

    }

    //改变登录会员层级关系
    public function change_path($member,$p_code,$is_reg=0){
        $p_code=intval($p_code);
        if($p_code==0&&$is_reg==1){
            //没有父ID且是注册
            $data['parent_id']=0;//父ID
            $data['path']='0-'.$member['member_id'];//组合父路径
            if($this->where(array('id'=>$member['member_id'],'is_delete'=>0))->save($data)!==false){
                //修改路径成功
                return true;
            }else{
                //修改路径失败
                return false;
            }
        }else{

            $parent=$this->where(array('id'=>$p_code,'is_delete'=>0))->find();
            if(!$member || $p_code<=0 || $parent['is_tuan']==0 || $member['is_delete']==1 || $member['parent_id']>0 || $member['member_id']==$p_code || !$parent || $parent['is_delete']==1){
                //登录会员被删除，登录会员已经有上级，推广人和登录人不能是同一个账号
                $data['parent_id']=0;//父ID
                $data['path']='0-'.$member['member_id'];//组合父路径
                $data['parent_time']=0;//成为下线时间
            }else{
                $data['parent_id']=$p_code;//父ID
                $data['path']=$parent['path'].'-'.$member['member_id'];//组合父路径
                $data['parent_time']=time();//成为下线时间
            }
            if($this->where(array('id'=>$member['member_id'],'is_delete'=>0))->save($data)!==false){
                //修改路径成功
                return true;
            }else{
                //修改路径失败
                return false;
            }
        }

    }

    //设置孩子信息
    public function set_child_profile($data){
        if(empty($data['member_id'])){
            $this->error="请登录后操作";
            return false;
        }
        if(empty($data['child_name'])){
            $this->error="请输入孩子的姓名";
            return false;
        }
        if(mb_str_len($data['child_name'])<2||mb_str_len($data['child_name'])>20){
            $this->error="孩子姓名2-20个字符";
            return false;
        }
        if(empty($data['child_birthday'])){
            $this->error="请选择孩子生日";
            return false;
        }
        $data['child_birthday']=strtotime($data['child_birthday']);
        if(!$data['child_birthday']){
            $this->error="生日格式错误";
            return false;
        }

        $res=M('MemberChild')->add($data);
        if($res!==false){
            return true;
        }else{
            $this->error="设置失败";
            return false;
        }
    }

    //申请成为团主
    public function set_tuan($member_id){
        $member_id=intval($member_id);
        if(!$member_id){
            $this->error="请登录后操作";
            return false;
        }
        $is_tuan=$this->where(array('id'=>$member_id))->getField('is_tuan');
        if($is_tuan==1){
            $this->error="已经是团主了不能重复申请";
            return false;
        }
        $res=$this->where(array('id'=>$member_id))->setField('is_tuan',1);
        if($res!==false){
            return true;
        }else{
            $this->error="申请失败";
            return false;
        }

    }

    //获取用户下线，可分别获取1级，2级，3级
    public  function get_child_list($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order='m.parent_time, m.id desc';
        }
        $where=array();

        if($search['type']==0){
            $where['m.path']=array('like',"{$search['path']}-%");
        }else{
            $where['m.path']=array('like',"{$search['path']}-%");
            $where['_string']="getChildByPath(m.path,'" . $search['path'] . "-')={$search['type']}";
        }
        $where['m.is_delete']=0;

        $count = $this->alias('m')
            ->where($where)->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        //$field="m.*,getOrderPayPrice(m.id) as buy_amount,getOrderNum(m.id) as buy_num";
        $field="m.*,getOrderPayPrice(m.id) as buy_amount,getCommissionTotalPriceMember(".$search['member_id'].",m.id) as commission_price";
        $list = $this->alias('m')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
//        echo $this->getLastSql();
//        exit;
        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $list[$key]['dengji']=get_child_type($search['path'],$vo['path']);
        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }
}