<?php
/**
 * 会员管理处理器
 * Date: 2016/6/5 0005
 * Time: 上午 10:26
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Exception;
use Common\Model\WeChatModel;

class MemberController extends AdminbaseController
{

    protected $member_model;

    function _initialize()
    {
        parent::_initialize();
        $this->member_model = D("Member");
        $this->model = D("Answermanage");
    }
    public function doctorinfo()
    {


    }
    public function doctorarti()
    {
        $this->display();

    }


    public function index()
    {

        if(IS_AJAX){
            $search['nickname'] = I('nickname');
            $search['status'] = I('status',100,'intval');
            $search['prov'] = I('prov',100,'intval');
            $search['city'] = I('city',100,'intval');
            $p=I('p',1,'intval');

            $data=$this->member_model->get_list($search,1,$p,10);

            if($data['list'])
            {
                foreach ($data['list'] as &$datums) {
                    $datums['prov'] = M('Region')->where(array('region_type'=>1,'region_id'=>intval($datums['prov'])))->getField('region_name');
                    $datums['city'] = M('Region')->where(array('region_type'=>2,'region_id'=>$datums['city']))->getField('region_name');
                    // 会员的提问数量
                    $datums['number'] =M('answermanage')->where("mid='".$datums['id']."'")->count();
                }
            }


            $this->success($data);
        }else{
            $list=M('Region')->where(array('parent_id'=>0))->order('first_pinyin asc')->select();
            $this->assign('province',$list);
            $this->display();
        }
    }

    // 新增会员查看提问记录
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
        $mid =I('request.mid');
        if($mid)
        {
            $where['mid'] =$mid;
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

    //冻结用户
    public function frozenaction1()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数不存在');
        }else
        {
            $res = M('Member')->where(array('id'=>$id))->save(array('status'=>0));
            if($res){
                write_log('冻结用户账号','用户管理');
                $this->success('冻结成功');
            }else{
                $this->error('冻结失败');
            }
        }
    }

    //冻结用户
    public function frozenAction()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数不存在');
        }else
        {
            $res = M('Member')->where(array('id'=>$id))->save(array('status'=>0));
            if($res){
                write_log('冻结用户账号','用户管理');
                $this->success('冻结成功');
            }else{
                $this->error('冻结失败');
            }
        }
    }
    //删除用户
    public function deldoctor()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数不存在');
        }else
        {
            $member=M('Member')->where(array('id'=>$id))->find();
            M('users')->where(array('mid'=>$id))->delete();
            M('users')->where(array('user_login'=>$member['mobile']))->delete();
            $res = M('Member')->where(array('id'=>$id))->save(array('is_delete'=>1));
            if($res){
                write_log('删除用户账号','用户管理');
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }
    }
    //详情
    public function detail(){
        $member_id=I('member_id',0,'intval');
        $info = $this->member_model->detail($member_id);
        if (!$info) {
            $this->error("未找到该条信息");
        }
        $this->assign('info',$info);
        $this->display();
    }


    public function open_member(){
        $ids=I('id');
        if($this->member_model->open_member($ids)){
            write_log('开启用户账号','用户管理');
            $this->success('开启成功');
        }else{
            $this->error('开启失败');
        }
    }
    public function open_member1(){
        $ids=I('id');
        if($this->member_model->open_member($ids)){
            write_log('开启用户账号','用户管理');
            $this->success('开启成功');
        }else{
            $this->error('开启失败');
        }
    }

    //导出
    public function export(){
        $search['nickname']=I('nickname');
        $search['mobile']=I('mobile');
        $search['status']=I('status',100,'intval');
        $search['st_time']=I('st_time',0,'strtotime');
        $search['end_time']=I('end_time',0,'strtotime');
        $order=I('order');
        $data=$this->member_model->get_list($search,$order,0,0);
        $xlsCell  = array(
            array('nickname','用户昵称'),
            array('mobile','账号','string'),
            array('total_dp','累计佣金'),
            array('child_num','下线数量'),
            array('buy_amount','购买金额'),
            array('status_text','状态'),
            array('create_time','注册时间'),
        );
        $xlsName='会员列表';
        $this->exportExcel($xlsName,$xlsCell,$data['list']);
    }

    //
    public function child_list(){
        $member_id = I('member_id');
        $p = I('p');
        $pageNum = $this->pageNum;
        $member_path = M('Member')->where(array('id'=>$member_id))->getField('path');
        $where['m.path'] = array('like',$member_path.'-'.'%');;

     /*   $count =  M('Member')
            ->alias('m')
            ->field('m.*,(select sum(o.pay_price) from ehecd_order as o where o.member_id=m.id and o.status>100) as buy_amount,
            getCommissionTotalPrice(m.id) as total_dp')
            ->where($where)
            ->count();*/

        $list = M('Member')
            ->alias('m')
            ->field('m.*,(select sum(o.pay_price) from ehecd_order as o where o.member_id=m.id and o.status >99) as buy_amount

            ')
            ->where($where)
            ->select();
        foreach($list as $k=>$y){
            //$list[$k]['parent_time'] = date('Y-m-d H:i:s ',$y['parent_time']);
            if($member_id){
                $list[$k]['parent_num']=get_child_type($member_path,$y['path']);
            }
            if(!$y['buy_amount']){
                $list[$k]['buy_amount'] = 0;
            }
        }
        foreach($list as $k=> $y){
            if($y['parent_num'] >3){
                unset($list[$k]);
            }
            //$parent_id = M('Member')->where(array('id'=>$y['id']))->getField('parent_id');
            $commission_list = M('MemberCommission')->where(array('member_id'=>$member_id,'buy_member_id'=>$y['id']))->select();
            if($commission_list){
                $count_total = 0;
                foreach($commission_list as $n=>$j){
                    $count_total += $j['price'];
                }
                $list[$k]['total_dp'] = $count_total;
            }else{
                $list[$k]['total_dp'] = 0;
            }
        }

        $totals = count($list);

        $total_page = ceil($totals / $pageNum);
        $start = ($p-1)*$pageNum;  //开始
        $pageData = array_slice($list,$start,$pageNum);

        $result['list']=$pageData;
        $result['p']=$p;
        $result['total']=$totals;

        $result['total_page']=$total_page;
       /* $list = D('MemberCommission')
            ->alias('mc')
            ->field('mc.*,m.nickname,m.mobile')
            ->join('LEFT JOIN __MEMBER__ m on m.id = mc.buy_member_id')
            ->where(array('member_id'=>$member_id))
            ->select();*/
       /* $search['parent_id']=I('member_id',0,"intval");
        $order=I('order');
        $p=I('p',1,'intval');
        $search['status']=I('status',100,'intval');
        $data=$this->member_model->get_list($search,$order,$p,$this->pageNum);*/
        $this->success($result);
    }

    public function get_order_list(){
        $search['member_id']=I('member_id',0,"intval");
        $p=I('p',1,'intval');
        $pageNum = $this->pageNum;
        //$where['status'] = 1;
        $where['(
            (CASE WHEN p.order_type = 0 THEN
            (SELECT g.member_id FROM ehecd_gratuity g  WHERE g.id=p.order_id)
                      WHEN p.order_type = 1 THEN
            (SELECT o.member_id FROM ehecd_order o  WHERE o.id=p.order_id)
                      WHEN p.order_type = 2 THEN
            (SELECT i.member_id FROM ehecd_interlocution i  WHERE i.id=p.order_id)
                      WHEN p.order_type = 3 THEN
            (SELECT ai.member_id FROM ehecd_a_item ai  WHERE ai.id= p.order_id)
            ELSE "" END
             )
        )'] = $search['member_id'];

        $count = D('Pay')
            ->alias('p')
            ->field('p.*,(
            (CASE WHEN p.order_type = 0 THEN
            (SELECT g.member_id FROM ehecd_gratuity g  WHERE g.id=p.order_id)
                      WHEN p.order_type = 1 THEN
            (SELECT o.member_id FROM ehecd_order o  WHERE o.id=p.order_id)
                      WHEN p.order_type = 2 THEN
            (SELECT i.member_id FROM ehecd_interlocution i  WHERE i.id=p.order_id)
                      WHEN p.order_type = 3 THEN
            (SELECT ai.member_id FROM ehecd_a_item ai  WHERE ai.id= p.order_id)
            ELSE "" END
             )
        ) as member_id')
            ->where($where)
            ->count();



        $list = D('Pay')
            ->alias('p')
            ->field('p.*,(
            (CASE WHEN p.order_type = 0 THEN
            (SELECT g.member_id FROM ehecd_gratuity g  WHERE g.id=p.order_id)
                      WHEN p.order_type = 1 THEN
            (SELECT o.member_id FROM ehecd_order o  WHERE o.id=p.order_id)
                      WHEN p.order_type = 2 THEN
            (SELECT i.member_id FROM ehecd_interlocution i  WHERE i.id=p.order_id)
                      WHEN p.order_type = 3 THEN
            (SELECT ai.member_id FROM ehecd_a_item ai  WHERE ai.id= p.order_id)
            ELSE "" END
             )
        ) as member_id')
            ->where($where)
            ->page($p,$pageNum)
            ->select();

        foreach($list as  $k=>$y){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$y['create_time']);
            switch($list[$k]['pay_type']){
                case 0:$list[$k]['pay_type'] = "微信支付";break;
                case 1:$list[$k]['pay_type'] = "支付宝支付";break;
                case 2:$list[$k]['pay_type'] = "余额支付";break;
            }
            switch($list[$k]['order_type']){
                case 0:$list[$k]['order_type_'] = "打赏";break;
                case 1:$list[$k]['order_type_'] = "商品订单";break;
                case 2:$list[$k]['order_type_'] = "问答";break;
                case 3:$list[$k]['order_type_'] = "报名组队";break;
            }
            switch($list[$k]['status']){
                case 0:$list[$k]['status'] = "未支付";break;
                case 1:$list[$k]['status'] = "已支付";break;
            }
            if($y['order_type']==1){
                $data = D('order')->where(array('id'=>$y['order_id']))->find();
                switch($data['status']){
                    case 0:$list[$k]['status'] = "新建订单";break;
                    case 1:$list[$k]['status'] = "已取消";break;
                    case 2:$list[$k]['status'] = "待付款";break;
                    case 3:$list[$k]['status'] = "待出行";break;
                    case 101:$list[$k]['status'] = "已评价";break;
                    case 6:$list[$k]['status'] = "已支付";break;
                    case 7:$list[$k]['status'] = "待发货";break;
                    case 8:$list[$k]['status'] = "待收货 ";break;
                    case 13:$list[$k]['status'] = "已退款 ";break;
                    case 14:$list[$k]['status'] = "已过期 ";break;
                    case 100:$list[$k]['status'] = "已完成 ";break;
                }
                $list[$k]['url_type'] = $data['order_type'];
                switch($data['order_type']){
                    case 1:$list[$k]['order_type_'] = "门票订单";break;
                    case 2:$list[$k]['order_type_'] = "积分订单";break;
                    case 3:$list[$k]['order_type_'] = "自营活动订单";break;
                    case 4:$list[$k]['order_type_'] = "家活动订单";break;
                    case 5:$list[$k]['order_type_'] = "商品订单";break;
                }
            }
        }

        $total_page = ceil($count / $pageNum);
        $result['list']=$list;
        $result['p']=$p;
        $result['total']=$count;
        $result['pagesize']=$pageNum;
        $result['total_page']=$total_page;
        $this->success($result);
    }
}