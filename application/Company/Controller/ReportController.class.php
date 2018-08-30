<?php
// +----------------------------------------------------------------------
// | ThinkCMF 报告管理
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 业务爱好者 <649180397@qq.com>
// +----------------------------------------------------------------------
namespace Company\Controller;
use Think\Controller;
class ReportController extends BaseController {
    protected $model;
    function _initialize()
    {
        parent::_initialize();
        $this->model = D("Report");
    }
    public function index(){                
        $where=array(); 
        $keyword=I('request.reportname');
        if(!empty($keyword)){
            $where['reportname']=array('like',"%$keyword%");
        }
        if(!empty(I('request.nicename'))){
            $where['nicename']=array('like',"%".I('request.nicename')."%");
        }
        if(!empty(I('request.companyname'))){
            $where['companyname']=array('like',"%".I('request.companyname')."%");
        }
        if(!empty(I('request.mobile'))){
            $where['mobile']=array('like',"%".I('request.mobile')."%");
        }
        if(!empty(I('request.type'))){
            $where['type']=I('request.type');
        }
        if(!empty(I('request.status'))){
            $where['status']=I('request.status');
        }
        if(!empty(I('request.st_time')))
        {
            $where['checktime']=array(
                array('EGT',strtotime(I('request.st_time')))
            );
        }
        if(!empty(I('request.ed_time')))
        {
            array_push($where['checktime'], array('ELT',strtotime(I('request.ed_time').' 23:59:59')));
        }
        if(!empty(I('request.crst_time')))
        {
            $where['createtime']=array(
                array('EGT',strtotime(I('request.st_time')))
            );
        }
        if(!empty(I('request.cred_time')))
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
           
            switch ($value['type']) {
                case '1':
                    $posts[$key]['typename'] ='平台';
                    break;
                case '2':
                    $posts[$key]['typename'] ='检测机构';
                    break;
                case '3':
                    $posts[$key]['typename'] ='用户';
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
    // 添加单个报告
    public function add_post()
    {
        if(IS_POST)
        {
            $pdata =I('post.');
            $minfo =M('member')->where("mobile='".$pdata['mobile']."'")->find();
            if(empty($minfo))
            {
                $this->ajaxReturn(array('status'=>1,'msg'=>'手机号用户不存在'));
            }else
            {
                $pdata['nicename'] =$minfo['nickname'];
                $pdata['type'] =2;
                $pdata['companyname'] =M('company')->where("id='".session('COMP_ID')."'")->getField('companyname');
                $pdata['filename'] =$pdata['filenamestr'];
                $pdata['status'] =1;
                $pdata['createtime']=time();
                $pdata['checktime'] =strtotime($pdata['checktime']);
                unset($pdata['filenamestr']);
                $lastid=$this->model->add($pdata);
                if($lastid)
                {
                    $this->ajaxReturn(array('status'=>0,'msg'=>'保存成功'));
                }else
                {
                    $this->ajaxReturn(array('status'=>1,'msg'=>'保存失败'));
                }
            }
        }
    }
    // 批量上传多个文件
    public function pitch_post()
    {
         if(IS_POST)
        {
            $pdata =I('post.');
            $filenamestr =explode(',',$pdata['filenamestr2']);
            foreach ($filenamestr as $key => $value) {
                $mobile= substr($value,0,-4);
                $minfo= M('member')->where("mobile='".$mobile."'")->find();
                if($minfo)
                {
                    $data['nicename'] =$minfo['nickname'];
                    $data['companyname'] =M('company')->where("id='".session('COMP_ID')."'")->getField('companyname');
                    $data['type']=2;
                    $data['status']=1;
                    $data['mobile'] =$mobile;
                    $data['file_hz'] =1;
                    $data['fileurl'] =$pdata['fileurl'][$key];
                    $data['filename'] =$value;
                    $data['createtime']=time();
                    $data['checktime'] =strtotime($pdata['checktime2']);
                    $data['reportname']=$pdata['reportname2'];
                    $this->model->add($data);
                }
            }

            $this->ajaxReturn(array('status'=>0,'msg'=>'保存成功'));
        }
    }
    // 删除报告
    public function delete()
    {
        if(IS_POST)
        {
            $id=I('id');
            $info =$this->model->find($id);
            if($this->model->where("id='$id'")->delete())
            {
                // 删除报告文件
                if($info['file_hz'] ==1)
                {
                    unlink(SITE_PATH .$info['fileurl']);
                }else
                {
                    // 删除图片
                    $imgurl =explode('|',$info['fileurl']);
                    foreach ($imgurl as $key => $value) {
                        unlink(SITE_PATH .$value);
                    }
                }
                $this->ajaxReturn(array('status'=>0));
            }else
            {
                $this->ajaxReturn(array('status'=>1,'msg'=>'删除失败'));
            }
        }
    }
    //下载报告文件
    public function download()
    {
        $id=I('get.id');
        $info=$this->model->where("id='$id'")->find();
        $url_file = SITE_PATH . '/' . $info['fileurl'];
        if (file_exists($url_file))
        {
            header('Content-type: application/unknown');
            header('Content-Disposition: attachment; filename="'.$info['filename'].'"');
            header("Content-Length: " . filesize($url_file) . "; ");
            readfile($url_file);
        }else{
            $this->error('下载出错');
        }
    }
}