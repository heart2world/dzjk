<?php

namespace Mobile\Controller;

use Mobile\Controller\MemberbaseController;
use Common\Model\MessageModel;
//站内信控制器
class MessageController extends MemberbaseController
{

    protected $message_model;
    function _initialize()
    {
        parent::_initialize();
        $this->message_model = new MessageModel();
        $this->pageSize = 20;
    }


    //站内信列表页
    public function index()
    {
        if(IS_AJAX)
        {
            $type = I('type',1,'intval');
            $p = I('p',1,'intval');
            if($type == 2)   //站内信
            {
                $page_size = $this->pageSize;
                $search['member_id'] = $this->member['member_id'];
                $search['reg_time'] = $this->member['create_time'];

//                $search['member_type']==2  //医生
//                $search['member_type']==3    //普通用户

                $member = M('Member')->where(array('id'=>$search['member_id']))->field('types,is_ok')->find();

                if($member['types'] == 2 && $member['is_ok'] == 1)
                {
                    $search['member_type'] = 2;
                }else
                {
                    $search['member_type'] = 3;
                }
//                var_dump($search);

                $list = $this->message_model->list_member($search,'',$p,$page_size);  //获取系统消息

                $ress = M('MemberMessage')->field('mes_id')->where(array('member_id'=>$this->member['member_id']))->select();
                $newRow = array();
                if($ress)
                {
                    foreach ($ress as $ress) {
                        $newRow[] = $ress['mes_id'];
                    }
                }


                $str = '';
                if($list['list'])
                {
                    foreach ($list['list'] as $item) {
                        $item['content'] = strip_tags(html_entity_decode($item['content']));
                        if($item['content'])
                        {
                            $item['content'] = mb_substr($item['content'], 0, 100, "utf-8");
                        }

                        if(in_array($item['id'],$newRow))
                        {
                            $str .= '<a href="/Message/detail/id/'.$item['id'].'" class="hui msg_item">';
                            $str .= '<h4 class="flex_dom"><span class="flex_1">'.$item['title'].'</span><em>'.$item['create_time'].'</em></h4>';
                            $str .= '<p>'.$item['content'].'</p>';
                        }else
                        {
                            $str .= '<a href="/Message/detail/id/'.$item['id'].'" class="msg_item">';
                            $str .= '<h4 class="flex_dom"><span class="flex_1">'.$item['title'].'</span><em>'.$item['create_time'].'</em></h4>';
                            $str .= '<p>'.$item['content'].'</p>';
                        }


                    }
                }
                if(strlen($str) > 30)
                {
                    $rows['list'] = $str;
                    $this->success($rows);
                }else
                {
                    $this->error('nodata');
                }
            }elseif ($type == 1)  //评论消息
            {
                $rows = array();
                $str = $this->getCommMsg($p);
                if(strlen($str) > 30)
                {
                    $rows['list'] = $str;
                    $this->success($rows);
                }else
                {
                    $this->error('nodata');
                }
            }
        }else{

            $this->display();
        }
    }

    public function getCommMsg($p,$pagesize = 10)
    {
        $p = $p ? $p : 1;
        $start = ($p-1) * $pagesize;
        $mid = $this->member['member_id'];
        $where['receive_member_id'] = $mid;
        $where['is_delete'] = 0;
        $where['is_read'] = 0;
        $where['type'] = array('in','8,9');
        $meData = M('Message')
            ->limit($start,$pagesize)
            ->order('id desc')->where($where)->select();

        if($meData)
        {
            $str = '';
            foreach ($meData as $meDatum)
            {

            //加阅读数据
                $rows['mes_id'] = $meDatum['id'];
                $rows['member_id'] = $mid;
                $rows['is_read'] = 0;
                $rows['is_delete'] = 0;
                M('MemberMessage')->add($rows);

                //操作都头像和昵称
                $minfo = M('Member')->where(array('id'=>$meDatum['send_users_id']))->field('avatar,nickname')->find();
                if($meDatum['types'] == 1)  //文章
                {
                    $titledata = M('Article')->field('title,content,type')->where(array('id'=>$meDatum['oid']))->find();
                    $title = $titledata['title'] ? $titledata['title'] : mb_substr(strip_tags(html_entity_decode($titledata['content'])),0,20,'utf-8');
                }else if($meDatum['types'] == 0)   //评论
                {
                    $title = M('ArticleComment')->where(array('id'=>$meDatum['oid']))->getField('content');
                }

                $str .= '<div class="pingjia_item flex_dom p-v-15">';
                $str .= '<div class="left">';
                $str .= '<a href="javascript:;"><img style="width: 50px;height: 50px;border-radius: 50px;" src="'.$minfo['avatar'].'"/></a>';
                $str .= '    </div>';
                $str .= '   <div class="pingjia_con flex_1">';
                $str .= '     <h4 class="flex_dom flex_item_between flex_item_mid"><span><i class="nicknameLab">'.$minfo['nickname'].'</i><em></em>';

                if($meDatum['type'] == 8)
                {
                    $str .= '<b class="font12 color-999">赞了我</b></span></h4>';
                }else if($meDatum['type'] == 9)
                {

                    if($meDatum['types'] == 1)  //文章
                    {
                        $str .= '<b class="font12 color-999">评论了我</b></span>';
                        $str .= ' <a href="/Article/commentInfo/id/'.$meDatum['arti_comm_id'].'/aid/'.$meDatum['oid'].'">回复</a></h4>';
                    }else if($meDatum['types'] == 0)   //评论
                    {
                        $str .= '<b class="font12 color-999">回复了我</b></span>';
                        $str .= '<a href="/Article/commentCom/id/'.$meDatum['arti_comm_id'].'">回复</a></h4>';
                    }
                    $str .= '<div class="text_con">';
                    $str .= $meDatum['content'];
                    $str .= '  </div>';
                }


                $str .= '<div class="huifu_list">';
                $str .= '<div class="huifu_item">';
                $str .= '<span>我:</span>'.$title.'';
                $str .= '</div>';
                $str .= '</div>';
                $str .= '<div class="time flex_dom flex_item_between flex_item_mid">';
                $str .= '<span class="color-999">'.formatTime($meDatum['create_time']).'</span> ';


                //点赞数
                if($meDatum['types'] == 1)  //文章
                {

                    if($meDatum['type'] == 9)
                    {
                        $dzs = M('ArticleComment')->where(array('a_id'=>$meDatum['oid'],'parent_id'=>0))->getField('dzs');

                    }else
                    {
                        $dzs = M('Article')->where(array('id'=>$meDatum['oid']))->getField('dzs');
                    }
//
                }else
                {
                    $dzs = M('ArticleComment')->where(array('id'=>$meDatum['oid']))->getField('dzs');
//                    $dzs = M('ArticleComment')->where(array('id'=>$meDatum['arti_comm_id']))->getField('dzs');
                }

                //当前用户是否点赞过
                if($meDatum['types'] == 1)  //文章
                {

                    $ra = M('ArticleGive')->where(array('member_id'=>$this->member['member_id'],'a_id'=>$meDatum['oid'],'parent_id'=>$meDatum['arti_comm_id']))->find();

                }else
                {
                    $ra = M('ArticleGive')->where(array('member_id'=>$this->member['member_id'],'parent_id'=>$meDatum['arti_comm_id']))->find();
                }

                if(!$ra)
                {
                    if($meDatum['types'] == 1)  //文章
                    {

                        if($meDatum['type'] == 9) {

//                            $aid = M('ArticleComment')->where(array('id'=>$meDatum['oid']))->getField('a_id');
                            //227

                            $str .= '<label><input onclick="zanerji(' .$meDatum['oid'] . ','.$meDatum['arti_comm_id'].','.'this'.')" type="checkbox" /><span>';
                        }else
                        {
                            $str .= '<label><input disabled type="checkbox" /><span>';
                        }

                    }
                    else if($meDatum['types'] == 0)   //评论
                    {
                        if($meDatum['type'] == 9)
                        {
                            $aid = M('ArticleComment')->where(array('id'=>$meDatum['oid']))->getField('a_id');
                            $str .= '<label><input onclick="zanersanji('.$meDatum['arti_comm_id'].',this'.','. $aid .')" type="checkbox" /><span>';
                        }else
                       {
                           $str .= '<label><input disabled type="checkbox" /><span>';
                       }



                    }
                }else
                {
                    $str .= '<label><input type="checkbox" disabled checked id="zan2"/><span>';
                }
                $str .= '<img src="/themes/Public/Mobile/image/slice/dianzan1.png"/><img src="/themes/Public/Mobile/image/slice/dianzan2.png"/></span>';
                if($dzs > 0)
                {
                    $str .= '<em data-id="'.$dzs.'">'.$dzs.'</em>';
                }else
                {
                    $str .= '<em></em>';
                }
                $str .= '</label>';
                $str .= '</div>';
                $str .= '</div>';
                $str .= '</div>';
            }
        }
        return $str;
    }


    //站内信详情
    public function detail(){
        $id=I('id',0,'intval');
        if($id){
            $this->member['member_id'];



//            $info = $this->message_model->get_message_info($id,$this->member['member_id']);
//            $this->message_model->member_read($id,$this->member['member_id']);

            $info = M('Message')->where(array('id'=>$id))->find();
            $row['mes_id'] = $id;
            $row['member_id'] = $this->member['member_id'];
            $row['is_read'] = 0;
            $row['is_delete'] = 0;
            if(!M('MemberMessage')->where($row)->find())
            {
                $s = M('MemberMessage')->add($row);
            }



            if($info){
                $info['content'] = htmlspecialchars_decode($info['content']);
                $info['create_time'] = date('Y.m.d H:i:s',$info['create_time']);
                $this->assign('info',$info);
                $this->display();
            }else{
                $this->error('没有找到该消息');
            }
        }else
        {
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
        $count=$this->message_model->list_member_count($this->member['member_id'],$this->member['create_time']);
        $kf_count=M('KfLogChild')->where(array('is_read'=>0,'uid'=>$this->member['member_id']))->count();
        $count=$count+$kf_count;
        $this->success($count);
    }
    //获取未读数量
    public function get_no_read_count2()
    {
        $count=$this->message_model->list_member_count($this->member['member_id'],$this->member['create_time']);
        //$kf_count=M('KfLogChild')->where(array('is_read'=>0,'uid'=>$this->member['member_id']))->count();
        //$count=$count+$kf_count;
        $this->success(array('msg'=>$count));
    }

}