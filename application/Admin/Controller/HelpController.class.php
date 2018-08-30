<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\HelpModel;

class HelpController extends AdminbaseController
{

    protected $article_model;
    function _initialize()
    {
        parent::_initialize();
        $this->article_model = new HelpModel();
    }

    public function delbtn()
    {
        $id = I('id');
        if($id)
        {
            $res = M('Help')->where(array('id'=>array('in',$id)))->save(array('is_delete'=>1));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }

    //内容详情
    public function detail(){
        $id = I('id',0,'intval');
        $info = M('Help')->where(array('id'=>$id))->find();
        if($info)
        {
            $info['content'] = html_entity_decode($info['content']);
            if($info['opt_id'] == 0)
            {
                $info['authorname'] = '平台';
            }else
            {
                 $info['authorname'] = D('Users')->where(array('id'=>$info['opt_id']))->getField('user_nicename');
            }
            $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);
        }

        $this->assign('info',$info);
        $this->display();
    }

    public function add()
    {
        if(IS_POST)
        {
            $data = I('post.');
            $row['title'] = $data['title'];

            if(!trim($data['title']))
            {
                $this->error('标题不能为空');
            }
            if(!trim($data['contents']))
            {
                $this->error('内容不能为空');
            }
            $row['create_time'] = time();
            $row['content'] = $data['contents'];
            $row['is_delete'] = 0;
            $row['opt_id'] = $_SESSION['ADMIN_ID'];
            $id = M('Help')->add($row);
            if($id)
            {
                $this->success('操作成功');
            }else
            {
                $this->error('no');
            }
        }else
        {
            $lablist = M('label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();
            if($lablist)
            {
                $this->lab = $lablist;
            }else
            {
                $this->lab = array();
            }
            $this->display();
        }
    }
    public function index()
    {
        if(IS_AJAX){
            $search['keywords'] = I('keywords');
            $p = I('p',1,'intval');
            $search['st_time'] = I('st_time',0,'strtotime');
            $search['end_time'] = I('end_time',0,'strtotime');
            $data = $this->article_model->get_list($search,'',$p,$this->pageNum);
            $this->success($data);
        }else{

            $this->display();
        }
    }

    //新增文章
    public function create(){
        if(IS_AJAX){
            if($data=$this->article_model->create()){
                $title=$this->article_model->title;
                if($this->article_model->add()){
                    write_log('添加文章:'.$title,'帮助中心管理');
                    $this->success('添加成功');
                }else{
                    $this->error($this->article_model->getError());
                }
            }else{
                $this->error($this->article_model->getError());
            }
        }else{
            $this->display();
        }
    }


    //删除
    public function delete(){
        if (IS_AJAX) {
            $ids=I('id');
            $res=$this->article_model->deleteBatch($ids);
            if ($res===false) {
                $this->error($this->article_model->getError());
            }else{
                write_log('删除文章:'.$res,'帮助中心管理');
                $this->success("删除成功");
            }
        }else{
            $this->error("参数错误");
        }
    }





}