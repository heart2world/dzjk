<?php
/**
 * 站内信管理
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\MessageModel;

class MessageController extends AdminbaseController
{

    protected $message_model;

    function _initialize()
    {
        parent::_initialize();
        $this->message_model = new MessageModel();
    }
    public function detalis()
    {
        $id = I('get.id');
        $data = M('message')->where(array('id'=>$id))->find();

        if($data['receive_member_type'] == 3){
            $mobile = explode(',',$data['receive_member_id']);
            if($mobile){
                $str = array();
                $arr =  M('Member')->field('mobile')->where(array('id'=>array('in',$mobile)))->select();
                foreach($arr as $k=>$y){
                    if($y['mobile']){
                        $str[] =  $y['mobile'];
                    }
                }
                $str = join(',',$str);
                $data['receive_member_type'] = $str;
            }
        }else{
            $data['receive_member_type'] = '全部';
        }
        $data['content'] = html_entity_decode($data['content']);
        $this->data = $data;
        $this->display('detalis');
    }

    public function index()
    {
        if(IS_AJAX){
            $st_time=I('st_time',0,"strtotime");
            $search['st_time']=$st_time;
            $end_time=I('end_time',0,"strtotime");
            $search['end_time']=$end_time;
            $search['keyword'] = I('keyword');
            $p=I('p',1,'intval');
            $data=$this->message_model->get_list($search,'',$p,$this->pageNum);

            $this->success($data);
        }else{
            $this->display();
        }
    }

    //新增站内信
    public function create(){
        if(IS_AJAX){
            if($data=$this->message_model->create()){
                $this->message_model->send_users_id=(int)session('ADMIN_ID');
                $title=$this->message_model->title;
                if($data['receive_member_type'] == 3 && empty($data['receive_member_id'])){
                    $this->error("请选择指定人");
                }else{
                    if($this->message_model->add()){
                        write_log('发送站内信:'.$title,'站内信管理');
                        $this->success('添加成功');
                    }else{
                        $this->error($this->message_model->getError());
                    }
                }
            }else{
                $this->error($this->message_model->getError());
            }
        }else{
            $this->display();
        }
    }

    //编辑站内信
    public function edit(){
        if(IS_AJAX){
            if($data=$this->message_model->create()){
                $this->message_model->send_users_id=(int)session('ADMIN_ID');
                $title=$this->message_model->title;
                if($this->message_model->save()!==false){
                    write_log('编辑消息:'.$title,'消息管理');
                    $this->success('编辑成功');
                }else{
                    $this->error($this->message_model->getError());
                }
            }else{
                $this->error($this->message_model->getError());
            }
        }else{
            $id = I('id',0,"intval");
            $data = M('Message')->where(array('id'=>$id))->find();
            switch ($data['receive_member_type']){
//                case '2':$data['receive_member_type'] = '公司';break;
//                case '1':$data['receive_member_type'] = '学生';break;
                case '0':$data['receive_member_type'] = '全部';break;
            }
            $data['content'] = html_entity_decode($data['content']);
            $this->assign('info',$data);
            $this->display('create');
        }
    }

    //删除
    public function delete(){
        if (IS_AJAX) {
            $ids=I('id');
            $res=$this->message_model->deleteBatch($ids);
            if ($res===false) {
                $this->error($this->message_model->getError());
            }else{
                write_log('删除站内信:'.$res,'站内信管理');
                $this->success("删除成功");
            }
        }else{
            $this->error("参数错误");
        }
    }

    public function get_all_user(){
        $data = I();
        $list = $this->message_model->get_user($data);
        $this->ajaxReturn($list);
    }
}