<?php
// +----------------------------------------------------------------------
// | ThinkCMF 财务管理
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 业务爱好者 <649180397@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FinanceController extends AdminbaseController {
    protected $model;
    function _initialize()
    {
        parent::_initialize();
        $this->model = D("Financelog");
    }
    // 财务列表
    public function index(){                
        $where=array('status !=0'); 
        $keyword=I('request.username');
        if(!empty($keyword)){
            $where['nicename']=array('like',"%$keyword%");
        }
        if(!empty(I('request.mobile'))){
            $where['mobile']=array('like',"%".I('request.mobile')."%");
        }
        if(!empty(I('request.type'))){
            $where['type']=I('request.type');
        }
        if(!empty(I('request.handeltype'))){
            $where['handeltype']=I('request.handeltype');
        }
        if(!empty(I('request.st_time')))
        {
            $where['createtime']=array(
                array('EGT',strtotime(I('request.st_time')))
            );
        }
        if(!empty(I('request.ed_time')))
        {
            array_push($where['createtime'], array('ELT',strtotime(I('request.ed_time').' 23:59:59')));
        }
        $count=$this->model->where($where)->count();            
        $page = $this->page($count, 20);            
        $posts=$this->model
            ->where($where)
            ->limit($page->firstRow,$page->listRows)
            ->order("createtime desc")
            ->select(); 
        $total  = array('tiwen' =>0.00 ,'tixian'=>0.00 ,'tuikuan'=>0.00);
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
            switch ($value['handeltype']) {
                case '提问':
                    $total['tiwen'] += $value['coin'];
                    break;
                case '提现':
                    $total['tixian'] += $value['coin'];
                    break;
                case '退款':
                    $total['tuikuan'] += $value['coin'];
                    break;
                default:
                    # code...
                    break;
            }
        }
        $total  = array('tiwen' =>number_format($total['tiwen'],2) ,'tixian'=>number_format($total['tixian'],2) ,'tuikuan'=>number_format($total['tuikuan'],2));
        $this->assign("page", $page->show('Admin'));
        $this->assign("total", $total);
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("list",$posts);
        $this->display();
    }
    



    // 提现申请列表
    public function cash(){                
        $where=array('handeltype'=>'提现'); 
        $keyword=I('request.username');
        if(!empty($keyword)){
            $where['username']=array('like',"%$keyword%");
        }
        if(!empty(I('request.nicename'))){
            $where['nicename']=array('like',"%".I('request.nicename')."%");
        }
        if(!empty(I('request.mobile'))){
            $where['mobile']=array('like',"%".I('request.mobile')."%");
        }
        $status =I('status','-1','intval');
        if($status !='-1'){
            $where['status']=$status;
        }
        if(!empty(I('request.st_time')))
        {
            $where['createtime']=array(
                array('EGT',strtotime(I('request.st_time')))
            );
        }
        if(!empty(I('request.ed_time')))
        {
            array_push($where['createtime'], array('ELT',strtotime(I('request.ed_time').' 23:59:59')));
        }
        $count=$this->model->where($where)->count();            
        $page = $this->page($count, 20);            
        $posts=$this->model
            ->where($where)
            ->limit($page->firstRow,$page->listRows)
            ->order("createtime desc")
            ->select(); 
        
        foreach ($posts as $key => $value) {
            if($value['adminid'])
            {
                $posts[$key]['adminname'] =M('users')->where("id='".$value['adminid']."'")->getField('user_login');
            }
            switch ($value['status']) {
                case '1':
                    $posts[$key]['statusname'] ='已通过';
                    break;
                case '2':
                    $posts[$key]['statusname'] ='已拒绝';
                    break;
                default:
                    $posts[$key]['statusname'] ='待审核';
                    break;
            }
        }
       
        $this->assign("page", $page->show('Admin'));
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("list",$posts);
        $this->display();
    }
    //提现申请操作
    public function  changestatus()
    {
        if(IS_POST)
        {
            $id=I('post.id');
            $adminid =session('ADMIN_ID');
            $this->model->where("id='$id'")->save(array('status' =>I('post.status'),'adminid'=>$adminid));
            $this->ajaxReturn(array('status'=>0));
        }
    }
}