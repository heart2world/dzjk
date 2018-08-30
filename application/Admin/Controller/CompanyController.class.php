<?php
// +----------------------------------------------------------------------
// | ThinkCMF 检测公司管理
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CompanyController extends AdminbaseController {
    protected $companymodel;
    function _initialize()
    {
        parent::_initialize();
        $this->companymodel = D("company");
    }
    public function index(){                
        $where=array(); 
        $keyword=I('request.userlogin');
        if(!empty($keyword)){
            $where['userlogin']=array('like',"%$keyword%");
        }
        if(!empty(I('request.companyname'))){
            $where['companyname']=array('like',"%".I('request.companyname')."%");
        }
        if(!empty(I('request.linkname'))){
            $where['linkname']=array('like',"%".I('request.linkname')."%");
        }
        if(!empty(I('request.mobile'))){
            $where['mobile']=array('like',"%".I('request.mobile')."%");
        }
        $count=$this->companymodel->where($where)->count();            
        $page = $this->page($count, 20);            
        $posts=$this->companymodel
            ->where($where)
            ->limit($page->firstRow,$page->listRows)
            ->order("createtime desc")
            ->select(); 

        $this->assign("page", $page->show('Admin'));
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("company",$posts);
        $this->display();
    }
    // 公司添加提交
    public function add_post(){
        if (IS_POST) {
            $pdata =I('post.');
            
            if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $pdata['companyname']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $pdata['linkname']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^\d{11}$/', $pdata['mobile']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/',$pdata['userlogin']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            $count =$this->companymodel->where("userlogin='".$pdata['userlogin']."'")->count();
            if($count>0)
            {
                $this->ajaxReturn(array('msg'=>"公司账号已存在",'status'=>1));
            }
            $pdata['createtime']=time();
            $pdata['userpass'] =sp_password($pdata['userpass']);
            $result=$this->companymodel->add($pdata);
            if ($result) {              
                $this->ajaxReturn(array('msg'=>"添加成功！",'status'=>0));
            } else {
                
                $this->ajaxReturn(array('msg'=>"添加失败！",'status'=>1));
            }
             
        }
    }

    //编辑公司
    public function edit()
    {
        $id = I("post.id",0,'intval');
        $data = $this->companymodel->where("id='$id'")->find();
        if (!$data) {
            $this->ajaxReturn(array('status'=>1,'msg'=>"信息不存在！"));
        }
        $this->ajaxReturn(array('status'=>0,'data'=>$data));
    }

    
    // 编辑提交
    public function edit_post(){
        if (IS_POST) {
            
            $pdata=I("post."); 
            if(empty($pdata['userpass']))
            {
                unset($pdata['userpass']);
            }else{
                $pdata['userpass'] =sp_password($pdata['userpass']);
            }         
            if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $pdata['companyname']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $pdata['linkname']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^\d{11}$/', $pdata['mobile']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if(!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/',$pdata['userlogin']))
            {
                $this->ajaxReturn(array('msg'=>"信息格式有误，请确认后提交",'status'=>1));
            }
            if($pdata['userlogin'] !=$pdata['olduserlogin'])
            {
                $count =$this->companymodel->where("userlogin='".$pdata['userlogin']."'")->count();
                if($count>0)
                {
                    $this->ajaxReturn(array('msg'=>"公司账号已存在",'status'=>1));
                }
            }
            $result=$this->companymodel->where("id='".$pdata['id']."'")->save($pdata);            
            if ($result!==false) {  
                $this->ajaxReturn(array('msg'=>'保存成功！','status'=>0));
            } else {
                $this->ajaxReturn(array('msg'=>'保存失败！','status'=>1));
            }
        }
    }
    // 解冻/冻结操作
    public function changestatus(){
        $id = I('post.id',0,'intval');
        if (!empty($id)) {
            $result = $this->companymodel->where(array("id"=>$id))->setField('status',I('post.status'));
            if ($result!==false) {
                $this->ajaxReturn(array('status'=>0));               
            } else {
                $this->ajaxReturn(array('status'=>1,'msg'=>'操作失败'));   
            }
        } else {
            $this->ajaxReturn(array('status'=>1,'msg'=>'参数有误'));   
        }
    }
    
}