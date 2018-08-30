<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 15:11
 */

namespace Common\Model;


class InterlocutionModel extends CommonModel
{
    protected $_validate = array(
        array('title','require','标题必须填写！',1),
        array('title','1,20','标题长度1-20字',1,'length'),
        array('title','check_title','标题已经存在！',0,'callback'),
        array('type','require','请勾选悬赏方式！',1),
        array('city','require','请选择目的地！',1),
        array('content','require','请填问题内容！',1),
    );

    public function check_title($title){
        $where=array();
        $where['is_delete']=array('eq',0);
        $where['title']=$title;
        $total=$this->where($where)->count();
        if($total){
            return false;
        }else{
            return true;
        }
    }
    public function add_inter_comment($data,$member){
        $where['i_id'] = $data['parent_id'];
        $where['member_id'] = $member;
        $where['parent_id'] = 0;
        $is_comment = D('InterlocutionComment')->where($where)->find();
        if($is_comment){
            $this->error = "您已回答过该问题,不能重复回答!";
            return false;
        }
        $arr['create_time'] = time();
        $arr['i_id'] = intval($data['parent_id']);
        $arr['member_id'] = $member;
        if($data['content']){
            $content="";
            $logo="";
            $is_img_index=0;
            foreach($data['content'] as $key=>$vo){
                if(preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $vo)){
                    $content.="<img src='".$vo."' /><br/>";
                    if($is_img_index==0){
                        $logo=$vo;
                    }
                    $is_img_index++;
                }else{
                    $content.=$vo."<br/>";
                }
            }
            $arr['content'] = $content;
            $arr['content_img'] = $logo;
        }else{
            $arr['content']="";
            $arr['content_img']="";
        }
        if(empty($arr['content'])){
            $this->error="请添加点文字或者内容吧";
            return false;
        }
        $id = D('InterlocutionComment')->add($arr);
        if($id){
            return true;
        }else{
            $this->error="提交失败";
            return false;
        }

    }



    //发表提问
    public function add_inter($data,$member){

        $arr['type'] = $data['type'];
        $arr['title'] = $data['title'];
        $arr['content'] = $data['content'];
        $arr['city'] = $data['city'];
        $arr['province'] = D('Destination')->where(array('id'=>$arr['city']))->getField('pid');
        $arr['create_time'] = time();
        $arr['member_id'] = $member;

        $config=getOptions();
        $integral=intval($config['questions_integral']);

        if($arr['type']==1){                        //积分提问添加问题信息
            $member_pwd = M('Member')->where(array('id'=>$member))->getField('pwd');
            if($member_pwd!=sp_password($data['password'])){
                $this->error = "支付密码错误";
                return false;
            }

            $arr['pay_integral'] = (int)$data['pay_money'];
            $arr['pay_type'] = 3;
            $arr['pay_status'] = 1;
            $this->startTrans();
            $res = $this->add($arr);

            $now_integral = M('Member')->where(array('id'=>$member))->getField('integral');

            $arr1['member_id'] = $member;
            $arr1['change'] = $arr['pay_integral'];
            $arr1['change_type'] = 20;
            $arr1['change_status'] = 2;
            $arr1['after'] = (int)$now_integral-(int)$arr['pay_integral'];
            $arr1['create_time'] = time();
            $arr1['to_id'] = $res;
            $res_member = M('Member')->where(array('id'=>$member))->setField('integral',$arr1['after']);  //减去用户积分
            $res_log = M('IntegralLog')->add($arr1);       //积分变动日志

            if($integral>0){
                $integral_model=D('Integral');
                $integral_model->add_integral($member,$integral,9,0,$res);
            }

            if($res && $res_member && $res_log){
                $this->commit();
                $list = $this->field('id,city,title,create_time,pay_integral,pay_type')->where(array('id'=>$res))->find();
                $list['create_time'] = date('Y-m-d H:i:s',$list['create_time']);
                return $list;
            }else{
                $this->rollback();
                return false;
            }

        }elseif($arr['type']==2){                    //金额提问添加问题信息
            $arr['pay_money'] = $data['pay_money'];
            $this->startTrans();
            $res = $this->add($arr);
            $arr2['pay_sn'] ='ANS'.time().rand(100000,999999);
            $arr2['create_time'] = time();
            $arr2['pay_price'] = $data['pay_money'];
            $arr2['order_type'] = 2;
            $arr2['order_id'] = $res;
            $payM = M('Pay')->add($arr2);
            if($res && $payM){
                $this->commit();
                return $arr2['pay_sn'];
            }else{
                $this->rollback();
                return false;
            }
        }elseif($arr['type']==0){
            $arr['pay_money']=0;
            $arr['pay_integral'] =0;
            $arr['pay_status'] = 1;
            $res = $this->add($arr);            //普通提问添加问题信息

            if($integral>0){
                $integral_model=D('Integral');
                $integral_model->add_integral($member,$integral,9,0,$res);
            }
            if($res){
                return true;
            }else{
                return false;
            }
        }

    }


    //回调  $pay_type  0微信支付1支付宝支付2余额支付
    public function call_fun($id,$pay_type){
        $type = 0;
        switch($pay_type){
            case 0:$type = 1;break;
            case 1:$type = 4;break;
            case 2:$type = 2;break;
        }
        $get_pay_type = $this->where(array('id'=>$id))->setField('pay_type',$type);       //1.微信支付 2.余额支付 3.积分支付 4.支付宝支付

        $get_member = $this->where(array('id'=>$id))->getField('member_id');
        $config=getOptions();
        $integral=intval($config['questions_integral']);
        if($integral>0){
            $integral_model=D('Integral');
            $integral_model->add_integral($get_member,$integral,9,0,$id);
        }
        $res_status = $this->where(array('id'=>$id))->setField('pay_status',1);
        if($res_status!==false && $get_pay_type!==false){
            return true;
        }else{
            return false;
        }
    }

    public function get_list($data){
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?20:$data['pageNum'];

        $data['start_time'] = empty($data['start_time'])?strtotime('1990-01-01'):strtotime($data['start_time']);
        $data['end_time'] = empty($data['end_time'])?time():strtotime($data['end_time'])+86399;
        $where['i.create_time'] = array('between',array($data['start_time'],$data['end_time']));
        $where['i.is_delete'] = 0;

        if(!empty($data['id'])){
            $where['i.id'] = $data['id'];
        }
        if(!empty($data['title'])){
            $where['i.title'] = array('like','%'.$data['title'].'%');
        }
        if(!empty($data['nickname'])){
            $where['m.nickname'] = $data['nickname'];
        }
        if(!empty($data['province'])){
            $where['i.province'] = $data['province'];
        }
        if(!empty($data['city'])){
            $where['i.city'] = $data['city'];
        }
        if($data['type'] !='' && $data['type'] >=0){
            $where['i.type'] = $data['type'];
        }
        $where['i.pay_status'] = 1;
        $count =  $this
            ->alias('i')
            ->field('i.*,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,r.name as province_name,r1.name as city_name')
            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __DESTINATION__ r on r.id = i.province')
            ->join('LEFT JOIN __DESTINATION__ r1 on r1.id = i.city')
            ->where($where)
            ->count();

        $list = $this
            ->alias('i')
            ->field('i.*,ic.content_img as ic_img,
            (select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,r.name as province_name,r1.name as city_name,(select count(\'id\') from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count,
             (select count(tl.id) from ehecd_interlocution_comment ic left join ehecd_top_log tl  on tl.to_id = ic.id where ic.i_id = i.id and ic.is_delete = 0 ) as  top_num')

            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __DESTINATION__ r on r.id = i.province')
            ->join('LEFT JOIN __DESTINATION__ r1 on r1.id = i.city')
            ->join('LEFT JOIN __INTERLOCUTION_COMMENT__ ic on ic.id=i.adopt_id')
            ->where($where)
            ->order('i.sort asc,i.create_time desc')
            ->page($data['p'],$data['pageNum'])
            ->select();
        //echo $this->getLastSql();
        foreach($list as $k=>$y){
            if($y['content_img']){
                $list[$k]['content_img'] = explode(',',$y['content_img']);
            }
            $list[$k]['ic_img'] = explode(',',$y['ic_img']);
            switch($y['type']){
                case 0 :$list[$k]['type_name'] = "普通提问";break;
                case 1 :$list[$k]['type_name'] = "积分悬赏";break;
                case 2 :$list[$k]['type_name'] = "金额悬赏";break;
            }
        }

        $Result['p'] = $data['p'];
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$data['pageNum']);
        return  $Result;
    }
    //首页提问
    public function get_index_list($data,$order,$p=1,$page=0)
    {
        $where['i.is_delete'] = 0;
        $where['i.pay_status'] = 1;
        if (!$order) {
            $order = 'i.create_time asc';
        }
        if ($data['city']) {
            $where['i.city'] = $data['city'];
        }
        if($data['key_words']){
            $where['i.title'] = array('like','%'.$data['key_words'].'%');
        }
        if($data['member_id']){
            $where['i.member_id'] = $data['member_id'];
        }
        if($data['have_img']){
            $where['_string']="ic.content_img is not null AND ic.content_img !=''";
        }
        $count = $this
            ->alias('i')
            ->field('i.id,i.title,i.top_num,i.browsing_count,ic.content_img as ic_img,m.nickname,m.mobile,m.headimg,
            m1.nickname as ci_nickname,m1.mobile as ci_mobile,m1.headimg as ci_headimg,
            d1.name as city_name,ic.content as ci_content,get_gratuity_num(2,i.id) as gratuity_num')
            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __DESTINATION__ d1 on d1.id = i.city')
            ->join('LEFT JOIN __INTERLOCUTION_COMMENT__ ic on ic.id=i.adopt_id')
            ->join('LEFT JOIN __MEMBER__ m1 on m1.id = ic.member_id')
            ->where($where)
            ->count();
        $list = $this
            ->alias('i')
            ->field('i.id,i.title,i.top_num,i.browsing_count,ic.content_img as ic_img,m.nickname,m.mobile,m.headimg,
            m1.nickname as ci_nickname,m1.mobile as ci_mobile,m1.headimg as ci_headimg,
            d1.name as city_name,ic.content as ci_content,get_gratuity_num(2,i.id) as gratuity_num')
            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __DESTINATION__ d1 on d1.id = i.city')
            ->join('LEFT JOIN __INTERLOCUTION_COMMENT__ ic on ic.id=i.adopt_id')
            ->join('LEFT JOIN __MEMBER__ m1 on m1.id = ic.member_id')
            ->where($where)
            ->order($order)
            ->page($p, $page)
            ->select();
        foreach($list as $k=>$y){
            $list[$k]['ic_img'] = explode(',',$y['ic_img']);
            $list[$k]['comment_count']=M('InterlocutionComment')->where(array('i_id'=>$y['id']))->count();
        }
        $result['p'] = $p;
        $result['totalPage'] = ceil($count / $page);
        $result['total_page']= ceil($count / $page);
        $result['list']=$list;
        return $result;
    }
    //获取问答的回复前端   $member==是否收藏
    public function get_i_comment_f($data,$member){
        $member = empty($member)?0:$member;
        $data['order'] = empty($data['order'])?1:$data['order'];
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?10:$data['pageNum'];
        $where['ic.is_delete'] = 0 ;
        $where['ic.i_id'] = $data['id'];            //问答ID
        $where['ic.parent_id'] = 0;
        if(!empty($data['c_id'])){                  //回答ID
            $where['ic.id'] = $data['c_id'];
        }
        $order = 'is_adopt desc';
        if($data['order']==1){
            $order .= ' ,ic.create_time desc';
        }else{
            $order .= ' ,top_total desc';
        }
        $count = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.*,(select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->where($where)
            ->count();

        $list = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.*,
            (select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,
            (select count(\'id\') from ehecd_interlocution_comment ic1 where ic1.parent_id = ic.id and ic1.is_delete = 0 and ic.i_id = ic1.i_id) as child_comment,
            (select count(\'id\') from ehecd_gratuity g where  g.to_id = ic.id and g.status = 1) as gratuity_count,
            (select count(mc.id) from ehecd_member_collection mc where mc.type=5 and mc.is_delete = 0 and mc.to_id = ic.id and mc.member_id = '.$member.') as is_collection,
            (select count(i.id) from ehecd_interlocution i where i.id = ic.i_id and i.adopt_id = ic.id) as is_adopt,
            (select count(mc.id) from ehecd_member_collection mc where mc.to_id = ic.id and mc.type=5 and mc.is_delete = 0) as collection,
            (select count(tl.id) from ehecd_top_log tl where tl.type=1 and tl.to_id = ic.id ) as top_total
            ')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->page($data['p'],$data['pageNum'])
            ->where($where)
            ->order($order)
            ->select();
        //var_dump($list);
       // echo M('InterlocutionComment')->getLastSql();
        foreach($list as $k=>$y){
            if($y['parent_id']){
                $to_member = M('InterlocutionComment')->where(array('id'=>$y['parent_id']))->getField('member_id');
                $to_member_name = M('Member')->where(array('id'=>$to_member))->getField('nickname');
                $list[$k]['to_member'] = $to_member_name;
            }
        }

        $Result['p'] = $data['p'];
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$data['pageNum']);
        return  $Result;

    }


    //获取问答的回复
    public function get_i_comment($data,$parent_id){
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?10:$data['pageNum'];

        $data['start_time'] = empty($data['start_time'])?strtotime('1990-01-01'):strtotime($data['start_time']);
        $data['end_time'] = empty($data['end_time'])?time():strtotime($data['end_time'])+86399;
        $where['ic.create_time'] = array('between',array($data['start_time'],$data['end_time']));


        $where['ic.is_delete'] = 0 ;
        $where['ic.i_id'] = $data['id'];
        if(!empty($parent_id)){
            $where['ic.parent_id'] = $parent_id;
        }else{
            $where['ic.parent_id'] = 0;
        }

        if(!empty($data['nickname'])){
            $where['m.nickname'] = array('like','%'.$data['nickname'].'%');
        }
        $count = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.*,(select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->where($where)
            ->count();


        $list = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.*,
            (select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,
            (select count(\'id\') from ehecd_interlocution_comment ic1 where ic1.parent_id = ic.id and ic1.is_delete = 0 and ic.i_id = ic1.i_id) as child_comment,(select count(\'id\') from ehecd_gratuity g where  g.to_id = ic.id ) as gratuity_count,
            (select count(mc.id) from ehecd_member_collection mc where mc.type = 5 and ic.id = mc.to_id and mc.is_delete = 0) as collection_num,
            (select count(tl.id) from ehecd_top_log tl where tl.type = 1 and ic.id = tl.to_id ) as top_num
            ')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->page($data['p'],$data['pageNum'])
            ->where($where)
            ->select();
        foreach($list as $k=>$y){
            if($y['content_img']){
                $list[$k]['content_img'] = explode(',',$y['content_img']);
            }
            if($y['parent_id']){
                $list[$k]['to_name'] = M('Member')->where(array('id'=>$y['parent_id']))->getField('nickname');
            }
        }

        $Result['p'] = $data['p'];
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$data['pageNum']);
        return  $Result;
    }
    //排序
    public function sort_update($data){
        $count=0;
        foreach($data as $key=>$vo){
            $id=intval($vo['id']);
            $sort=intval($vo['sort']);
            $res = $this->where(array('id'=>$id))->setField('sort',$sort);
            if($res!==false){
                $count+=1;
            }
        }
        if($count==count($data)){
            return true;
        }else{
            $this->error="设置失败";
            return false;
        }

    }
    //发布问题的用户
    public function get_user($nickname){
        $where['t.is_delete'] = 0;
        $where['m.is_delete'] = 0;
        if(!empty($nickname)){
            $where['m.nickname'] = array('like','%'.$nickname.'%');
        }
        $list = $this
            ->alias('t')
            ->field('t.member_id,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = t.member_id')
            ->where($where)
            ->group('t.member_id')
            ->select();
        return $list;
    }
    //回复问答用户
    public function get_comment_user($nickname,$id){
        $where['ic.is_delete'] = 0;
        $where['ic.parent_id'] = 0;
        $where['ic.i_id'] = $id;
        if(!empty($nickname)){
            $where['m.nickname'] = array('like','%'.$nickname.'%');
        }
        $list = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.member_id,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->where($where)
            ->group('ic.member_id')
            ->select();
        return $list;
    }
   //问答回复打赏列表
    public function get_gratuity($data)
    {
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?10:$data['pageNum'];

        $where['to_id'] = $data['id'];
        $where['type'] = 2;


        $count =  M('Gratuity')
            ->alias('g')
            ->field('g.*,(select FROM_UNIXTIME(g.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = g.member_id')
            ->where($where)
            ->count();

        $list = M('Gratuity')
            ->alias('g')
            ->field('g.*,(select FROM_UNIXTIME(g.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = g.member_id')
            ->where($where)
            ->select();
        foreach($list as $y=>$k){
            switch($k['money_type']){
                case 0:$list[$y]['pay_type'] = "金额打赏";break;
                case 1:$list[$y]['pay_type'] = "积分打赏";break;
            }
        }

        $Result['p'] = $data['p'];
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$data['pageNum']);
        return  $Result;

    }

    //获取回复问答指定ID的详情
    public function get_comment_info($id){
        $where['ic.is_delete'] = 0;
        $where['ic.id'] = $id;
        $list = M('InterlocutionComment')
            ->alias('ic')
            ->field('ic.*,m.nickname,m.mobile,m.headimg,(select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,(select count(\'id\') from ehecd_interlocution_comment ic1 where ic1.parent_id = ic.id and ic1.is_delete = 0 and ic.i_id = ic1.i_id) as child_comment,(select count(g.id) from ehecd_gratuity g where  g.to_id = ic.id and g.type=2) as gratuity_count')
            ->join('LEFT JOIN __MEMBER__ m on m.id = ic.member_id')
            ->where($where)
            ->group('ic.member_id')
            ->select();
        foreach($list as $k=>$y){
            if($y['content_img']){
                $list[$k]['content_img'] = explode(',',$y['content_img']);
            }
        }
        return $list;
    }
    //问答浏览量
    public function add_browsing_count($id){
        $browsing_count = $this->where(array('id'=>$id))->getField('browsing_count');
        $this->where(array('id'=>$id))->setField('browsing_count',$browsing_count+1);
    }

    //前端获取问答列表
    public function get_f_list($search=array(),$p=1,$pageNum=0){


        if($search['show_status'] >0 ){
            if($search['show_status']==1){
                $order = 'i.create_time  desc';
            }
            if($search['show_status']==2){
                $order = 'comment_count asc';
            }
            if($search['show_status']==3){
                $order = 'total_top desc';
                $where['i.type'] = array('neq',0);
            }
        }else{
            $order = 'total_top desc';
        }
        $where['i.pay_status'] = 1;
        if(!empty($search['city'])){
            $where['i.city'] = $search['city'];
        }

        if(!empty($search['id'])){
            $where['i.id'] = $search['id'];
        }
        $count =  $this
            ->alias('i')
            ->field('i.*,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,r.region_name as province_name,r1.region_name as city_name')
            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __REGION__ r on r.region_id = i.province')
            ->join('LEFT JOIN __REGION__ r1 on r1.region_id = i.city')
            ->join('LEFT JOIN __INTERLOCUTION_COMMENT__ ic on ic.id=i.adopt_id')
            ->where($where)
            ->count();



        $list = $this
            ->alias('i')
            ->field('i.*,ic.content_img as ic_img,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,m.nickname,m.mobile,m.headimg,r.name as province_name,r1.name as city_name,(select count(ic.id) from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count,(select count(tl.id) from ehecd_interlocution_comment ic left join ehecd_top_log tl  on tl.to_id = ic.id where ic.i_id = i.id and ic.is_delete = 0 ) as total_top')
            ->join('LEFT JOIN __MEMBER__ m on m.id = i.member_id')
            ->join('LEFT JOIN __DESTINATION__ r on r.id = i.province')
            ->join('LEFT JOIN __DESTINATION__ r1 on r1.id = i.city')
            ->join('LEFT JOIN __INTERLOCUTION_COMMENT__ ic on ic.id=i.adopt_id')
            ->where($where)
            ->order($order)
            ->page($p,$pageNum)
            ->select();

        //echo $this->getLastSql();
        foreach($list as $k=>$y){
            if($y['content_img']){
                $list[$k]['content_img'] = explode(',',$y['content_img']);
            }
            switch($y['type']){
                case 0 :$list[$k]['type_name'] = "普通提问";break;
                case 1 :$list[$k]['type_name'] = "积分悬赏";break;
                case 2 :$list[$k]['type_name'] = "金额悬赏";break;
            }
            if($y['adopt_id']!=0){
                $list[$k]['comment_list'] = M('InterlocutionComment')->where(array('id'=>$y['adopt_id']))->find();
            }else{
                $where1['_string'] = "content_img is not null";
                $list1 = M('InterlocutionComment')->where(array('i_id'=>$y['id']))->where($where1)->select();
                if($list){
                    $list[$k]['comment_list'] = $list1[0];
                }else{
                    $list2 = M('InterlocutionComment')->where(array('i_id'=>$y['id']))->select();
                    if($list2){
                        $list[$k]['comment_list'] = $list2[0];
                    }else{
                        $list[$k]['comment_list'] = null;
                    }
                }
            }
        }
        foreach($list as $k=>$y){
            if($y['comment_list']){
                $content_01 = $y["comment_list"]['content'];//从数据库获取富文本content
                $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
                $content_03 = str_replace("&nbsp;","",$content_02);//将空格替换成空
                $list[$k]['comment_list']['content'] = strip_tags($content_03,'<br/><img/><p>');
                $child = M('Member')->where(array('id'=>$y['comment_list']['member_id']))->find();
                $list[$k]['comment_list']['nickname'] = $child['nickname'];
                $list[$k]['comment_list']['headimg'] = $child['headimg'];
                $list[$k]['comment_list']['mobile'] = $child['mobile'];
            }
        }
        $Result['p'] = $p;
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$pageNum);
        return  $Result;
    }

    //前端BANNER 5条数据
    public function get_banner_five($city){
        $where['is_delete'] = 0;
        $where['type'] = 7;
        $where['city'] = $city;
        $list = D('Banner')
            ->where($where)
            ->order('sort asc')
            ->limit(5)
            ->select();
        //echo M('InterlocutionComment')->getLastSql();
        return $list;

    }

    //前端问题总顶数  $id:Interlocution表
    public function get_top_total($id){
        $list = M('InterlocutionComment')
            ->field('id')
            ->where(array('i_id'=>$id))
            ->select();
        $arr = array();
        if($list){
            foreach($list as $k=>$y){
                if($y['id']){
                    $arr[] = $y['id'];
                }
            }
            $total = M('TopLog')->where(array('to_id'=>array('in',$arr)))->count();
        }else{
            $total = 0;
        }
        return $total;
    }

    //前端我的提问
    public function get_my_locution($member,$p,$pageNum){
        $p = empty($p)?1:$p;
        $where['i.pay_status'] = 1;
        $where['i.member_id'] = $member;
        $count = $this
            ->alias('i')
            ->field('i.id,i.title,d.name,i.city,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.browsing_count,(select count(tl.id) from ehecd_interlocution_comment ic left join ehecd_top_log tl  on tl.to_id = ic.id where ic.i_id = i.id and ic.is_delete = 0 ) as total_top,(select count(g.id) from ehecd_interlocution_comment ic left join ehecd_gratuity g on ic.id = g.to_id where  i.id = ic.i_id and g.type=2 ) as gratuity_count,(select count(\'id\') from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->count();

        $list = $this
            ->alias('i')
            ->field('i.id,i.title,d.name,i.city,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.browsing_count,(select count(tl.id) from ehecd_interlocution_comment ic left join ehecd_top_log tl  on tl.to_id = ic.id where ic.i_id = i.id and ic.is_delete = 0 ) as total_top,(select count(g.id) from ehecd_interlocution_comment ic left join ehecd_gratuity g on ic.id = g.to_id where  i.id = ic.i_id and g.type=2 ) as gratuity_count,(select count(\'id\') from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count,(select count(ic.id) from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_see = 0 and ic.parent_id = 0 and ic.is_delete = 0) as not_see')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->page($p,$pageNum)
            ->select();

        $Result['p'] = $p;
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$pageNum);
        return  $Result;
    }

    //前端我来解答
    public function get_come_answer($city,$p,$pageNum){
        $p = empty($p)?1:$p;
        $city = empty($city)?7:$city;
        $where['i.city'] = $city;
        //$where['i.adopt_id'] = 0;
        $where['i.pay_status'] = 1;

        $count = $this
            ->alias('i')
            ->field('i.title,i.city,d.name,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.id,(select count(\'id\') from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->count();

        $list = $this
            ->alias('i')
            ->field('i.title,i.city,d.name,i.type,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.id,(select count(\'id\') from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->page($p,$pageNum)
            ->order('comment_count asc')
            ->select();

        $Result['p'] = $p;
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$pageNum);
        return  $Result;
    }


    //前端采纳
    public function go_adopt($c_id){
        if(empty($c_id)){
            $this->error = "系统错误";
            return false;
        }
        $c_info = M('InterlocutionComment')->where(array('id'=>$c_id))->select();         //回答

        if(!$c_info){
            $this->error = "此条回复不存在";
            return false;
        }
        $i_info = M('Interlocution')->where(array('id'=>$c_info['i_id']))->find();           //问题
        if(!$i_info){
            $this->error = "此条评论不存在";
            return false;
        }

        if($i_info['adopt_id'] != 0){
            $this->error = "此提问已采纳解答或已过期";
            return false;
        }

        if($i_info['type']==1){                                     //积分
            $member_integral = M('Member')->where(array('id'=>$c_info['member_id']))->getField('integral');             //回答用户积分
            $integral = (int)$member_integral + (int)$i_info['pay_integral'];                       //回答人 增加积分
            M('Member')->startTrans();
            $res_member = M('Member')->where(array('id'=>$c_info['member_id']))->setField('integral',$integral);

            $arr1['member_id'] = $c_info['member_id'];
            $arr1['change'] = (int)$i_info['pay_integral'];
            $arr1['change_type'] = 17;
            $arr1['change_status'] = 1;
            $arr1['after'] = $integral;
            $arr1['create_time'] = time();
            $res_log = M('IntegralLog')->add($arr1);

            $res_i = M('Interlocution')->where(array('id'=>$i_info['id']))->setField('adopt_id',$c_id);

            if($res_member && $res_log && $res_i){
                M('Member')->commit();
                return true;
            }else{
                M('Member')->rollback();
                $this->error = "采纳失败";
                return false;
            }
        }
        if($i_info['type']==2){                                                 //金额
            $member_money = M('Member')->where(array('id'=>$c_info['member_id']))->getField('money');             //回答用户金额
            $money = $member_money + $i_info['pay_money'];
            M('Member')->startTrans();
            $res_member = M('Member')->where(array('id'=>$c_info['member_id']))->setField('money',$money);

            $arr1['member_id'] = $c_info['member_id'];
            $arr1['price'] = $i_info['pay_money'];
            $arr1['type'] = 1;
            $arr1['after_price'] = 1;
            $arr1['after'] = $money;
            $arr1['before_price'] = $member_money;
            $arr1['create_time'] = time();
            $arr1['note'] = "问题采纳";
            $arr1['change_type'] = 12;
            $res_log = M('MemberMoney')->add($arr1);

            $res_i = M('Interlocution')->where(array('id'=>$i_info['id']))->setField('adopt_id',$c_id);
            if($res_member && $res_log && $res_i){
                M('Member')->commit();
                return true;
            }else{
                M('Member')->rollback();
                $this->error = "采纳失败";
                return false;
            }
        }
    }

    //前端我的回答
    public function my_answer($member,$p,$pageNum){
        $where['ic.member_id'] = $member;
        $where['ic.parent_id'] = 0;
        $count = M('InterlocutionComment')
            ->alias('ic')
            ->field('i.title,i.city,d.name,i.type,(select FROM_UNIXTIME(i.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.id')
            ->join('LEFT JOIN __INTERLOCUTION__ i on i.id = ic.i_id')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->count();

        $list = M('InterlocutionComment')
            ->alias('ic')
            ->field('i.title,i.city,d.name,i.type,
            (select FROM_UNIXTIME(ic.create_time,"%Y-%m-%d %H:%i:%s")) as create_times,i.id,ic.content,
            (select count(tl.id) from ehecd_interlocution_comment ic left join ehecd_top_log tl  on tl.to_id = ic.id where ic.i_id = i.id and ic.is_delete = 0 ) as total_top,(select count(ic.id) from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and parent_id = 0) as comment_count,
            (select count(g.id) from ehecd_gratuity g where  g.to_id = ic.id and g.type=2) as gratuity_count,
            (select count(mc.id) from ehecd_member_collection mc where mc.to_id = ic.id and mc.type=5 and mc.is_delete = 0) as collection,ic.id as c_id')
            ->join('LEFT JOIN __INTERLOCUTION__ i on i.id = ic.i_id')
            ->join('LEFT JOIN __DESTINATION__ d on d.id = i.city')
            ->where($where)
            ->page($p,$pageNum)
            ->order('i.create_time desc')
            ->select();
        foreach($list as $y=>$k){
            if($k['content']){
                $content_01 = $k['content'];//从数据库获取富文本content
                $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
                $content_03 = str_replace("&nbsp;","",$content_02);//将空格替换成空
                $list[$y]['content'] = strip_tags($content_03,'<br/><img/><p>');
            }
        }

        $Result['p'] = $p;
        $Result['list'] = $list;
        $Result['totalPage'] = ceil($count/$pageNum);
        return  $Result;
    }

    // 问答采纳计划任务
    public function plan_adopt(){

        $config = getOptions('site_options');
        $adopt_day = $config['reward_day'];             //采纳设置的天数
        $adopt_p_count = $config['reward_p_num'];         //采纳设置人数
        $now_time = time();
        $adopt_a_time = 86400*(int)$adopt_day;            //目标时间
        $where['i.pay_status'] = 1;
        //$where['i.type'] = array('neq',0);
        $where['i.adopt_id'] = 0;
        $list = $this
            ->alias('i')
            ->field('i.id,i.create_time,i.type,i.pay_money,i.pay_integral,i.member_id,(select count("ic.id") from ehecd_interlocution_comment ic where ic.i_id = i.id and ic.is_delete = 0 and ic.parent_id = 0) as c_count')
            ->where($where)
            ->select();

        foreach($list as $k=>$y){
            $adopt_time = (int)$y['create_time'] + (int)$adopt_a_time;
            //在N天内，如果回答人数小于X人，那这部分钱退回到发布者账户余额
            $this->startTrans();
            if( $adopt_time <= $now_time && $y['c_count'] < $adopt_p_count){
                $res_i = $this->where(array('id'=>$y['id']))->setField('adopt_id',-1);
                if($y['type']==1){          //积分
                    //$res_m = M('Member')->where(array('id'=>$y['member_id']))->setInc('integral',$y['pay_integral']);
                    $res_log = D('Integral')->add_integral($y['member_id'],$y['pay_integral'],19,"提问过期，系统返还",$y['i_id']);
                    if($res_i!==false  && $res_log){
                        $this->commit();
                        return true;
                    }else{
                        $this->rollback();
                        return false;
                    }
                }
                if($y['type']==2){          //金额
                    //$res_m = M('Member')->where(array('id'=>$y['member_id']))->setInc('money',$y['pay_money']);
                    $res_log = D('MemberMoney')->add_money($y['member_id'],$y['pay_money'],16,"提问过期，系统返还");
                    if($res_i!==false && $res_log){
                        $this->commit();
                        return true;
                    }else{
                        $this->rollback();
                        return false;
                    }
                }
            }else
            //在N天内，回答人数够了，但发布者没有选择满意回答时候，系统自动把这部分赏金转给回答列表中置顶数高的，置顶数相同的，选择收藏量高的
            if($adopt_time <= $now_time && $y['c_count'] >= $adopt_p_count){
                $list_child = M('InterlocutionComment')
                    ->alias('ic')
                    ->field('ic.member_id,ic.id,ic.i_id,(select count(tl.id) from ehecd_top_log tl where tl.type = 1 and tl.to_id = ic.id) as top_total,
                    (select count(mc.id) from ehecd_member_collection mc where mc.type = 5 and mc.to_id = ic.id ) as collection')
                    ->where(array('ic.i_id'=>$y['id'],'ic.parent_id'=>0))
                    ->order('top_total desc,collection desc')
                    ->limit(1)
                    ->select();

                if($list_child){

                    $adopt_member = $list_child[0]['member_id'];
                    $adopt_c_id = $list_child[0]['id'];

                    $res_i = $this->where(array('id'=>$y['id']))->setField('adopt_id',$adopt_c_id);
                    if($y['type']==1){          //积分
                        //$res_m = M('Member')->where(array('id'=>$adopt_member))->setInc('integral',$y['pay_integral']);
                        $res_log = D('Integral')->add_integral($adopt_member,$y['pay_integral'],17,"问题回答被采纳",$y['id']);
                        if($res_i!==false  && $res_log){
                            $this->commit();
                            return true;
                        }else{
                            $this->rollback();
                            return false;
                        }
                    }
                    if($y['type']==2){          //金额
                        //$res_m = M('Member')->where(array('id'=>$adopt_member))->setInc('money',$y['pay_money']);
                        $res_log = D('MemberMoney')->add_money($adopt_member,$y['pay_money'],12,"问题回答被采纳");
                        if($res_i!==false  && $res_log){
                            $this->commit();
                            return true;
                        }else{
                            $this->rollback();
                            return false;
                        }
                    }
                }else{
                    $this->where(array('id'=>$y['id']))->setField('adopt_id',-1);
                }
            }
        }
    }
}