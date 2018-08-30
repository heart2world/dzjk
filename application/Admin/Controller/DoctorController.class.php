<?php
/**
 * 医生管理处理器
 * Date: 2016/6/5 0005
 * Time: 上午 10:26
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Exception;
use Common\Model\WeChatModel;

class DoctorController extends AdminbaseController
{

    protected $member_model;

    function _initialize()
    {
        parent::_initialize();
        $this->member_model = D("Member");
        $this->model = D("Answermanage");
    }

    //医生添加
    public function add()
    {
        if(IS_AJAX)
        {
            $data = I('');

            if(count($data['goods']['photos']) == 0)
            {
                $this->error('身份证不能为空!');
            }
            if(count($data['goods']['zyzgz']) == 0)
            {
                $this->error('职业资格证不能为空!');
            }
            $id = M('Member')->where(array('mobile'=>$data['mobile']))->getField('id');
            if($id)
            {
                $this->error('该手机号已绑定!');
            }else
            {
                $MembData['create_time'] = time();
                $MembData['nickname'] = $data['nickname'];
                $MembData['avatar'] = $data['avatar'];
                $MembData['mobile'] = $data['mobile'];
                $MembData['login_pwd'] = '123456';
                $MembData['types'] = 2;
                $MembData['zy'] = $data['zy'];
                $MembData['is_ok'] = 1;
                $MembData['iszx'] = intval($data['iszx']);
                $MembData['truename'] = $data['truename'];
                $MembData['prov'] = $data['prov'] != 'all' ? $data['prov'] : '';
                $MembData['city'] = $data['city'] != 'all' ? $data['city'] : '';
                try{
                    M('Member')->startTrans();
                    $res = M('Member')->add($MembData);
                    if($res)
                    {
                        $introData['pid'] = $res;
                        $introData['sfz'] = implode(',',$data['goods']['photos']);
                        $introData['zyzgz'] = implode(',',$data['goods']['zyzgz']);
                        $introData['grjsimg'] = implode(',',$data['goods']['grjsimg']);
                        $introData['grjs'] = $data['intro'];
                        $introData['intro'] = $data['grjs'];
                        $introData['zw'] = $data['zw'];
                        $introData['ks'] = $data['ks'];
                        $introData['hosp'] = $data['hosp'];
                        $introData['sq_t'] = time();
                        $resIn = M('MemberIntro')->add($introData);
                        if($resIn && $res)
                        {


                            //后台用户数据
                            $row['user_login'] = $data['mobile'];
                            $row['user_pass'] = '###fb947d45f98c36457dc549b68ac42386';
                            $row['user_nicename'] = $data['nickname'];
                            $row['mobile'] = $data['mobile'];
                            $row['mid'] = $res;
                            $row['user_type'] = 2;

                            $reid = M('Users')->add($row);

                            $AG['uid'] = $reid;
                            $AG['group_id'] = 23;
                            $AG['type'] = 1;
                            $areid = M('AuthGroupAccess')->add($AG);

                            M('Member')->commit();
                            write_log('添加医生'.$data['truename'],'医生管理');
                            $this->success('操作成功');
                        }else
                        {
                            $this->error('操作失败');
                        }
                    }else{
                        $this->error('操作失败');
                    }
                }catch (\Exception $e){
                    M('Member')->rollback();
                    return false;
                }
            }

        }else
        {
            $this->label = M('Label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();
            $list = M('Region')->where(array('parent_id'=>0))->order('first_pinyin asc')->select();
            $this->assign('province',$list);
            $this->display();
        }
    }

    //医生申请
    public function apply()
    {

        if(IS_AJAX){
            $search['nickname'] = I('nickname');
            $search['status'] = I('status',100,'intval');
            $search['zy'] = I('zy',100,'intval');
            $p = I('p',1,'intval');
            $search['is_ok'] = I('isok',100,'intval');
            $data = $this->member_model->get_list($search,2,$p,$this->pageNum);
            if($data['list'])
            {
                foreach ($data['list'] as &$datum)
                {
                    $min = array();
                    $min = M('MemberIntro')->where(array('pid'=>$datum['id']))->field('hosp,ks')->find();
                    $datum['hosp'] = $min['hosp'];
                    $datum['ks'] = $min['ks'];
                }
            }

            $this->success($data);
        }else{
            $this->label = M('Label')->field('id,name')->where(array('is_del'=>0))->select();
            $this->display();
        }
    }
    public function optionBtn()
    {
        $type = I('type');
        //申请  0  申请， 1 通过，2拒绝
        if($type == 3)  //删除
        {
            $res = M('Member')->where(array('id'=>I('id')))->save(array('types'=>1));
            $mres = M('MemberIntro')->where(array('pid'=>I('id')))->delete();

            if($mres && $res)
            {
                $this->success('操作成功');
            }else
            {
                $this->error('操作失败');
            }

        }elseif ($type == 2 || $type == 1)  //2 拒绝   // 1 通过
        {

            $Mdata = M('Member')->field('mobile,nickname,last_login_time')->where(array('id'=>I('id')))->find();

            if($type == 1)// 1 通过
            {

                $row['user_login'] = $Mdata['mobile'];
                $row['user_pass'] = '###fb947d45f98c36457dc549b68ac42386';
                $row['user_nicename'] = $Mdata['nickname'];
                $row['mobile'] = $Mdata['mobile'];
                $row['last_login_time'] = $Mdata['last_login_time'];
                $row['mid'] = I('id');
                $row['user_type'] = 2;

                $reid = M('Users')->add($row);
                $AG['uid'] = $reid;
                $AG['group_id'] = 23;
                $AG['type'] = 1;
                $areid = M('AuthGroupAccess')->add($AG);
            }


            $resI = M('MemberIntro')->where(array('pid'=>I('id')))->save(array('opt_t'=>time()));
            $res = M('Member')->where(array('id'=>I('id')))->save(array('is_ok'=>intval($type)));

            if($type == 1)// 1 通过
            {
                $url = $_SERVER['HTTP_HOST'].'/admin';
                $res = send_sms('您可使用手机号+123456登录医生后台',$Mdata['mobile']);

            }else  if($type == 2)// 2不通过
            {
                $opt = getOptions('site_options');
                $res = send_sms('医生审核未通过',$Mdata['mobile']);
            }

            if($res && $resI)
            {

                $this->success('操作成功');
            }else
            {
                $this->error('操作失败');
            }
        }
    }
    //更改状态
    public function modifyStatus()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数错误');
        }
        $status = intval(I('status'));
        if($status == 1)
        {
            $news = 0;
        }else if($status == 0)
        {
            $news = 1;
        }
        $res = M('Member')->where(array('id'=>$id))->save(array('status'=>$news));
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->error('操作失败');
        }
    }

    //医生列表
    public function index()
    {
        if(IS_AJAX){
            $search['nickname'] = I('nickname');
            $search['status'] = I('status',100,'intval');
            $search['prov'] = I('prov',100,'intval');
            $search['city'] = I('city',100,'intval');
            $search['zy'] = I('zy',100,'intval');
            $search['iszx'] = I('iszx',100,'intval');
            $search['is_ok'] = 1;
            $p=I('p',1,'intval');
            $data=$this->member_model->get_list($search,2,$p,10);
            if($data['list'])
            {
                foreach ($data['list'] as &$datums) {
                    $datums['prov'] = M('Region')->where(array('region_type'=>1,'region_id'=>intval($datums['prov'])))->getField('region_name');
                    $datums['city'] = M('Region')->where(array('region_type'=>2,'region_id'=>$datums['city']))->getField('region_name');
                    $datums['number'] = M('answerlog')->where("senduserid='".$datums['id']."'")->count();
                }
            }
            $this->success($data);
        }else{
            $list=M('Region')->where(array('parent_id'=>0))->order('first_pinyin asc')->select();
            $this->assign('province',$list);

            $this->label = M('Label')->field('id,name')->where(array('is_del'=>0))->select();
            $this->display();
        }
    }
    // 新增医生查看答题记录
    public function  answerlist()
    {
        $where=array(); 
        $keyword=I('request.questionname');
        if(!empty($keyword)){
            $where['questionname']=array('like',"%$keyword%");
        }
        if(!empty(I('request.mname'))){
            $where['mname']=array('like',"%".I('request.mname')."%");
        }
        if(!empty(I('request.doctornicename'))){
            $where['doctornicename']=array('like',"%".I('request.doctornicename')."%");
        }
        if(!empty(I('request.status'))){
            $where['status']=I('request.status');
        }
        if(!empty(I('request.st_time')))
        {
            $where['questiontime']=array(
                array('EGT',strtotime(I('request.st_time')))
            );
        }
        if(!empty(I('request.ed_time')))
        {
            array_push($where['questiontime'], array('ELT',strtotime(I('request.ed_time').' 23:59:59')));
        }
        // 医生参数
        $doctorid =I('request.doctorid');
        if($doctorid)
        {
            $where['doctorid'] =$doctorid;
        }
        $count=$this->model->where($where)->count();            
        $page = $this->page($count, 20);            
        $posts=$this->model
            ->where($where)
            ->limit($page->firstRow,$page->listRows)
            ->order("questiontime desc")
            ->select(); 
        //echo $this->model->getLastSql();
        foreach ($posts as $key => $value) {
            $posts[$key]['questionname'] =mb_substr(strip_tags($value['questionname']),0,30,'utf-8');
            switch ($value['status']) {
                case '1':
                    $posts[$key]['statusname'] ='待回应';
                    break;
                case '2':
                    $posts[$key]['statusname'] ='咨询中';
                    break;
                case '3':
                    $posts[$key]['statusname'] ='已取消';
                    break;
                case '4':
                    $posts[$key]['statusname'] ='已结束';
                    break;
                default:
                    # code...
                    break;
            }
        }
        $this->assign("page", $page->show('Admin'));
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("list",$posts);
        $this->display();
    }
    // 查看答题详情
    public function  answerdetail()
    {
        $id=I('get.id');
        $info =$this->model->find($id);
        switch ($info['status']) {
                case '1':
                    $info['statusname'] ='待回应';
                    break;
                case '2':
                    $info['statusname'] ='咨询中';
                    break;
                case '3':
                    $info['statusname'] ='已取消';
                    break;
                case '4':
                    $info['statusname'] ='已结束';
                    break;
                default:
                    # code...
                    break;
            }
        // 上传提问是图片
        if($info['filetype'] ==1)
        {
            $imgurl =explode('|', $info['fileurl']);
            foreach ($imgurl as $key => $value) {
               if($value)
               {
                    $imgurl[$key] ='http://'.$_SERVER['HTTP_HOST'].'/'.$value;
               }
            }
            $this->assign('imgurl',$imgurl);
        }
        // 是否上传录音
        if($info['questionradio'])
        {
            $radio =json_decode($info['questionradio'],true);
            $this->assign('radio',$radio);
        }
        //  沟通记录
        $gglist =M('answerlog')->where("parentid='$id'")->order('createtime desc')->select();
        foreach ($gglist as $key => $value) {
            switch ($value['atype']) {
                case '1':
                    $gglist[$key]['content'] ='http://'.$_SERVER['HTTP_HOST'].'/'.$value['content'];
                    break;
                case '2':
                    $gglist[$key]['content'] =json_decode($value['content'],true);
                    break;
                default:
                    # code...
                    break;
            }
            # code...
        }
        $this->assign('gglist',$gglist);
        $this->assign('info',$info);
        $this->display();
    }
    //获取城市
    public function get_city(){
        if(IS_AJAX){
            $parent_id = I('parent_id');
            if($parent_id != 'all')
            {
                $this->ajaxReturn(array('status'=>1,'list'=>M('Region')->where(array('parent_id'=>$parent_id))->select()));
            }else
            {
                $this->ajaxReturn(array('status'=>1,'list'=>array()));
            }
        }
    }

    //详情
    public function detail_sh(){
        $member_id = I('member_id',0,'intval');

        $info = $this->member_model->detail($member_id);

        if (!$info){
            $this->error("未找到该条信息");
        }
//        foreach ($info as &$item) {
//            $item['sq_t'] = date('Y-m-d H:i:s',$item['sq_t']);
//        }
        $info['sq_t'] = date('Y-m-d H:i:s',$info['create_time']);
        if($info['opt_t'])
        {
            $info['opt_t'] = date('Y-m-d H:i:s',$info['opt_t']);
        }

        $grjsimglist = explode(',',$info['grjsimg']);



        $this->assign('info',$info);
        $this->assign('grjsimg',$grjsimglist);
        $this->display();
    }
    //详情
    public function detail(){
        $member_id = I('member_id',0,'intval');
        $info = $this->member_model->detail($member_id);
        if (!$info){
            $this->error("未找到该条信息");
        }

        if($info['last_login_time'])
        {
            $info['last_login_time'] = date('Y-m-d H:i:s',$info['last_login_time']);
        }
       $info['grjsimg'] = explode(',', $info['grjsimg']);

        $info['zy'] = M('Label')->where(array('id'=>$info['zy']))->getField('name');

//        $mid = M('Users')->where(array('id'=>$member_id))->getField('mid');
        $last_login_time = M('Member')->where(array('id'=>$member_id))->getField('last_login_time');

        $info['last_login_time'] = date('Y-m-d H:i:s',$last_login_time);



        $info['gzs'] = M('MemberFollo')->where(array('member_id'=>$member_id))->count();
        $info['dts'] = M('Article')->where(array('author'=>$member_id,'type'=>1,'is_delete'=>0))->count();       //动态数
        $info['wzs'] = M('Article')->where(array('author'=>$member_id,'type'=>0,'is_delete'=>0))->count();      //文章数


        $this->assign('info',$info);
        $this->display();
    }

}