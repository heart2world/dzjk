<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 16:33
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;
use Common\Model\MessageModel;
class ContController extends AdminbaseController
{
    protected $message_model;
    function _initialize()
    {
        parent::_initialize();
        $this->message_model = new MessageModel();
        $this->pageNum = 10;
    }

    public function info()
    {
        $data = M('Message')->where(array('id'=>I('id')))->find();
        $data['content'] = htmlspecialchars_decode($data['content']);
        $data['create_time'] = date('Y-m-d H:i:s',$data['create_time']);
//        $data['jss'] =  M('MemberMessage')->where(array('mes_id'=>$data['id']))->count();
        $data['admi'] = M('Users')->where(array('id'=>$data['send_users_id']))->getField('user_login');
        $this->data = $data;
        $this->display();
    }

    public function index()
    {

        if(IS_AJAX){
            $search['st_time'] = I('st_time',0,"strtotime");
            $search['end_time'] = I('end_time',0,"strtotime");
            $search['keyword'] = I('keyword');
            $p = I('p',1,'intval');
            $data=$this->message_model->get_list($search,'',$p,$this->pageNum);
            $this->success($data);
        }else
        {
            $this->display();
        }
    }

    public function get_all_user()
    {
        $nickname = I('nickname');
        $type_s = I('type_s');  //100 全部   1用户     2医生
        $page = I('p')?I('p'):1;
        $data = $this->message_model->getUserByType($type_s,$nickname,$page,$this->pageNum);

//        }
        $this->success($data);
    }

    public function get_info_user()
    {
        $nickname = I('nickname');
        $id = I('id');
        $type_s = I('type_s');  //100 全部   1用户     2医生
        $page = I('p')?I('p'):1;
        $data = $this->message_model->getUserByMid($type_s,$nickname,$id,$page,$this->pageNum);
        $this->success($data);
    }


    public function sendMsg()
    {
        $data = I('post.');

        if(strlen($data['title']) == 0 ||  strlen($data['title']) > 60)
        {
            $this->error('标题只能20字以内');
        }
        $newRow['send_users_id'] = $_SESSION['ADMIN_ID'];
        $newRow['create_time'] = time();
        $newRow['title'] = $data['title'];
        $newRow['content'] = $data['content'];
        if($data['sendobj'] == 'all')//所有
        {
            if(strlen($data['id']) >= 1)   //指定用户发
            {
                $newRow['reads'] = count(explode(',',$data['id']));
                $newRow['receive_member_id'] = $data['id'];
                $newRow['receive_member_type'] = 1;
            }else  //所有用户发送
            {
                $newRow['reads'] = M('Member')->count();
                $newRow['receive_member_type'] = 0;
            }
        }else if($data['sendobj'] == 1)  //用户
        {

            $newRow['reads'] = M('Member')->where(array('types'=>array('in','1,2'),'is_ok'=>array('in','0,2')))->count();

            $newRow['receive_member_id'] = 0;
            $newRow['receive_member_type'] = 3;
        }else if($data['sendobj'] == 2)  //医生
        {

            $newRow['reads'] = M('Member')->where(array('types'=>2,'is_ok'=>1))->count();

            $newRow['receive_member_id'] = 0;
            $newRow['receive_member_type'] = 2;
        }
        $res = M('Message')->add($newRow);
        if($res)
        {
            $this->success('发送成功');
        }else
        {
            $this->error('操作失败');
        }


    }


    public function del_log()
    {
        if(I('id'))
        {
            $res = M('Message')->where(array('id'=>array('in',I('id'))))->save(array('is_delete'=>1));
            if($res) {
                $this->success('操作成功');
            }else
            {
                $this->error('操作失败');
            }
        }else
        {
            $this->error('操作失败');
        }


    }
    public function add()
    {
        $this->mcoun = M('Member')->where(array('is_delete'=>0))->count();
        $this->display();
    }
    /**
     * 注册协议
     * cate ：3注册协议  4预定须知 5 申请入驻流程
     */
    public function sub_content(){
        $res = M('Article')->where(array('cate_id'=>3))->find();
        if($res){
            $arr['content'] = I('content');
            $arr['update_time'] = time();
            $result = M('Article')->where(array('id'=>$res['id']))->save($arr);
            if($result){
                $this->ajaxReturn(array('status'=>1,'info'=>"修改成功"));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"您未做任何修改"));
            }
        }else{
            $arr['content'] = I('content');
            $arr['create_time'] = time();
            $arr['title'] = "注册协议";
            $arr['cate_id'] = 3;
            $result = M('Article')->add($arr);
            if($result){
                $this->ajaxReturn(array('status'=>1,'info'=>"添加成功"));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"添加失败"));
            }
        }
    }



}