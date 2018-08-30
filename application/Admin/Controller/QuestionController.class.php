<?php
// +----------------------------------------------------------------------
// | ThinkCMF 答题管理
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 业务爱好者 <649180397@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class QuestionController extends AdminbaseController {
    protected $model;
    function _initialize()
    {
        parent::_initialize();
        $this->model = D("Answermanage");
    }
    public function index(){                
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
    // 问题详情
    public function detail()
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
}