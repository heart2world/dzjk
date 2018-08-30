<?php

namespace Mobile\Controller;

use Common\Model\KfLogModel;
use Mobile\Controller\MemberbaseController;
use Common\Model\MessageModel;
//私信控制器
class KflogController extends MemberbaseController
{

    protected $kf_log_model;
    function _initialize()
    {
        parent::_initialize();
        $this->kf_log_model = new KfLogModel();
    }


    //站内信列表页
    public function index()
    {
        if(IS_AJAX){
            $p=I('p',1,'intval');
            $page_size=$this->pageSize;
            $search['member_id']=$this->member['member_id'];
            $list=$this->kf_log_model->get_list_all_member($search,'',$p,$page_size);
            $this->success($list);
        }else{

            $this->display();
        }
    }

    //详情
    public function detail(){
        $kf_id=I('kf_id',0,'intval');
        $type=I('type',0,'intval');
        $bid=I('bid',0,'intval');
        $mid=I('mid',0,'intval');
        if($kf_id==0){
            $member_id=$this->member['member_id'];
            //如果没得会话ID
            if($type==1){
                //直接平台会话
                $kf_id=$this->kf_log_model->where(array('member_id'=>$member_id,'to_uid'=>0))->getField('id');
                if($kf_id){
                    $this->kf_log_model->where(array('member_id'=>$member_id,'to_uid'=>0))->setField('update_time',time());
                }else{
                    $kf_id=$this->kf_log_model->add(array('member_id'=>$member_id,'to_uid'=>0,'update_time'=>time(),'create_time'=>time(),'content'=>'您好！'));
                }
            }elseif($type==2){
                if(!$bid){
                    //$this->error("你必须选择要咨询的商家");
                    $this->assign("msg","你必须选择要咨询的商家");
                    $this->display('/Public/error');
                    exit;
                }
                //商家
                $kf_id=$this->kf_log_model->where(array('member_id'=>$member_id,'to_uid'=>$bid))->getField('id');
                if($kf_id){
                    $this->kf_log_model->where(array('member_id'=>$member_id,'to_uid'=>$bid))->setField('update_time',time());
                }else{
                    $kf_id=$this->kf_log_model->add(array('member_id'=>$member_id,'to_uid'=>$bid,'update_time'=>time(),'create_time'=>time(),'content'=>'您好！'));
                }
            }elseif($type==3){
                //会员与会员之间的对话
                if(!$mid){
                    //$this->error("你必须选择要对话的好友");
                    $this->assign("msg","你必须选择要对话的好友");
                    $this->display('/Public/error');
                    exit;
                }
                if($mid==$this->member['member_id']){
                    //$this->error("自己不能和自己对话");
                    $this->assign("msg","自己不能和自己对话");
                    $this->display('/Public/error');
                    exit;
                }
                $sql="(( member_id=".$this->member['member_id']." and to_member_id=$mid ) or (member_id=$mid and to_member_id=".$this->member['member_id']."))";
                $kf_id=$this->kf_log_model->where(array('_string'=>$sql,'to_uid'=>-1))->getField('id');
                if($kf_id){
                    $this->kf_log_model->where(array('_string'=>$sql,'to_uid'=>-1))->setField('update_time',time());
                }else{
                    $kf_id=$this->kf_log_model->add(array('member_id'=>$member_id,'to_member_id'=>$mid,'to_uid'=>-1,'update_time'=>time(),'create_time'=>time(),'content'=>'您好！'));
                }
            }
        }
        if(IS_AJAX){
            $search['kf_id']=$kf_id;
            $p=I('p',1,'intval');
            $data=$this->kf_log_model->get_list_child_member($search,'',$p,10);
            foreach($data['list'] as $key=>$vo){
                if($vo['is_member']==1){
//                    $data['list'][$key]['nickname']=$vo['to_nickname'];
//                    $data['list'][$key]['headimg']=$vo['to_headimg'];
                }else{
                    if($vo['member_id']==0){
                        $data['list'][$key]['nickname']='平台';
                        $data['list'][$key]['headimg']='/themes/xiaoshutong/Public/Mobile/image/chatHeader2.png';
                    }else{
                        $data['list'][$key]['nickname']=$vo['business_name'];
                        $data['list'][$key]['headimg']=$vo['business_headimg'];
                    }
                }
            }
            if($data['p']>=$data['total_page']){
                $kf_first=$this->kf_log_model->alias('kl')
                    ->field('kl.*,m.headimg,m1.headimg as to_headimg')
                    ->join("LEFT JOIN __MEMBER__ m on kl.member_id = m.id")
                    ->join("LEFT JOIN __MEMBER__ m1 on kl.to_member_id = m1.id")
                    ->where(array('kl.id'=>$kf_id))->find();
                if($kf_first){
                    if($kf_first['to_uid']==-1){
                        if($kf_first['member_id']!=$this->member['member_id']){
                            $kf_first['uid']=$kf_first['to_member_id'];
                        }else{
                            $kf_first['uid']=$kf_first['member_id'];
                            $kf_first['member_id']=$kf_first['to_member_id'];
                            $kf_first['headimg']=$kf_first['to_headimg'];
                        }
                    }
                    $kf_first['create_time']=date('Y-m-d H:i:s',$kf_first['create_time']);
                    $data['list'][]=$kf_first;
                }
            }
            $list=$data['list'];
            $list=array_reverse($list);
            $data['list']=$list;
            $this->success($data);
        }else{
            M('KfLogChild')->where(array('kf_id'=>$kf_id,'uid'=>$this->member['member_id'],'is_read'=>0))->setField('is_read',1);
            $info=$this->kf_log_model
                ->where(array('id'=>$kf_id))
                ->find();

            if($info['to_uid']==0){
                $info['name']="平台";
            }elseif($info['to_uid']>0){
                $info['name']=M('Business')->where(array('uid'=>$info['to_uid']))->getField('name');
            }elseif($info['to_uid']==-1){
                if($info['member_id']=$this->member['member_id']){
                    $info['name']=M('Member')->where(array('id'=>$info['to_member_id']))->getField('nickname');
                }else{
                    $info['name']=M('Member')->where(array('id'=>$info['member_id']))->getField('nickname');
                }

            }
            $this->assign('name',$info['name']);
            $this->assign('kf_id',$kf_id);
            $this->display();
        }
    }

    //获取最后一条数据
    public function get_last(){
        if(IS_AJAX){
            $last_id=I('last_id',0,"intval");
            $kf_id=I('kf_id',0,"intval");
            $member_id=$this->member['member_id'];
            if($last_id>0){
                $info=M('KfLogChild')->alias('klc')
                    ->field('klc.*,m.nickname,m.headimg,b.name as business_name,b.headimg as business_headimg')
                    ->join("LEFT JOIN __MEMBER__ m on m.id=klc.member_id")
                    ->join('LEFT JOIN __BUSINESS__ b on klc.member_id=b.uid')
                    ->where(array('klc.kf_id'=>$kf_id,'klc.id'=>array('gt',$last_id),'klc.uid'=>$member_id))->find();
                if($info['create_time']){
                    $info['create_time']=date('Y-m-d H:i:s',$info['create_time']);
                }
            }else{
                $list=M('KfLogChild')->alias('klc')
                    ->field('klc.*,m.nickname,m.headimg,b.name as business_name,b.headimg as business_headimg')
                    ->join("LEFT JOIN __MEMBER__ m on m.id=klc.member_id")
                    ->join('LEFT JOIN __BUSINESS__ b on klc.member_id=b.uid')
                    ->where(array('klc.kf_id'=>$kf_id,'klc.uid'=>$member_id))->order('klc.id desc')->limit(1)->select();
                $info=$list[0];
                if($info['create_time']){
                    $info['create_time']=date('Y-m-d H:i:s',$info['create_time']);
                }
            }
            if($info){
                if($info['is_member']==0&&$info['member_id']>0){
                    $info['nickname']=$info['business_name'];
                    $info['headimg']=$info['business_headimg'];
                }
                elseif($info['is_member']==0&&$info['member_id']==0){
                    $info['nickname']='平台';
                    $info['headimg']='/themes/xiaoshutong/Public/Mobile/image/chatHeader2.png';
                }
                M('KfLogChild')->where(array('id'=>$info['id']))->setField('is_read',1);
            }

            $this->success($info);
        }
    }


    //发送文本信息
    public function send_text(){
        $kf_id=I('kf_id',0,'intval');
        $mid=I('mid',0,'intval');
        if($kf_id){
            if(IS_AJAX){
                $content=I('content');
                if(empty($content)){
                    $this->error("请输入留言内容");
                }
                if(mb_str_len($content)>250){
                    $this->error("留言内容最多250个字");
                }
                $member_id=$this->member['member_id'];
                $kf_log_model=new KfLogModel();
                $info=$this->kf_log_model->where(array('id'=>$kf_id))->find();
                if($info['member_id']!=$member_id){
                    $info['to_member_id']=$info['member_id'];
                }
                if($kf_log_model->send_child_kf_message($kf_id,$content,$member_id,$info['to_uid'],'',$info['to_member_id'])){
                    $this->success("发送成功");
                }else{
                    $this->error($kf_log_model->getError());
                }
            }else{
                $member_id=$this->member['member_id'];
                if($mid>0&&$kf_id==0){
                    //会员中心直接发私信文本，
                    if($mid==$member_id){
                        $this->assign("msg","不能与自己对话");
                        $this->display("/Public/error");
                        exit;
                    }
                    $info=$this->kf_log_model->where(array('to_uid'=>-1,'_string'=>" ((member_id=$member_id and to_member_id=$mid ) or (to_member_id=$member_id and member_id=$mid))"))
                        ->find();
                    //查找与该会员的对话
                    if(!$info){
                        //会话不存在
                        $info['member_id']=$member_id;
                        $info['to_uid']=-1;
                        $info['create_time']=time();
                        $info['update_time']=time();
                        $info['content']='你好';
                        $info['pic_list']='';
                        $info['to_member_id']=$mid;
                        $kf_id=$this->kf_log_model->add($info);
                        $info['id']=$kf_id;
                    }
//                    else{
//                        //更新最后会话时间
//                        $this->kf_log_model->where(array('to_uid'=>-1,'_string'=>" ((member_id=$member_id and to_member_id=$mid ) or (to_member_id=$member_id and member_id=$mid))"))
//                                ->setField('create_time',time());
//                    }
                }elseif($kf_id){
                    $info=$this->kf_log_model->where(array('id'=>$kf_id))->find();
                }else{
                    $this->assign("msg","参数错误");
                    $this->display("/Public/error");
                    exit;
                }
                if($info['to_uid']==0){
                    $info['name']="平台";
                }elseif($info['to_uid']>0){
                    $info['name']=M('Business')->where(array('uid'=>$info['to_uid']))->getField('name');
                }elseif($info['to_uid']==-1){
                    $name1=M('Member')->where(array('id'=>$info['to_member_id']))->getField('nickname');
                    $name2=M('Member')->where(array('id'=>$info['member_id']))->getField('nickname');

                    if($info['member_id']==$member_id){
                        $info['name']=$name1;
                    }else{
                        $info['name']=$name2;
                    }
                }
                $this->assign('name',$info['name']);
                $this->assign('kf_id',$kf_id);
                $this->display();
            }

        }else{
            $this->error('参数错误');
        }
    }

    //发送图片信息
    public function send_pic(){
        $kf_id=I('kf_id',0,'intval');
        if($kf_id){
            $info=$this->kf_log_model
                ->where(array('id'=>$kf_id))
                ->find();
            if(IS_AJAX){
                $pic_list=I('pic_list');
                $member_id=$this->member['member_id'];
                $kf_log_model=new KfLogModel();
                if(empty($pic_list)){
                    $this->error('请选择图片');
                }
                if($info['member_id']!=$member_id){
                    $info['to_member_id']=$info['member_id'];
                }
                if($kf_log_model->send_child_kf_message($kf_id,'',$member_id,$info['to_uid'],$pic_list,$info['to_member_id'])){
                    $this->success("发送成功");
                }else{
                    $this->error($kf_log_model->getError());
                }
            }

        }else{
            $this->error('参数错误');
        }
    }

    //站内信删除
    public function delete(){
        $id=I('id',0,'intval');
        if($id){
            $info=$this->message_model->member_delete($id,$this->member['member_id']);
            if($info){
                $this->success('删除成功');
            }else{
                $this->error($this->message_model->getError());
            }
        }else{
            $this->error('参数错误');
        }
    }

    //获取未读数量
    public function get_no_read_count()
    {
        $count=$this->message_model->list_member_count($this->member['member_id']);
        $this->success($count);
    }

}