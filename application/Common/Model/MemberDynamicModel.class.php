<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/30
 * Time: 16:00
 */

namespace Common\Model;


class MemberDynamicModel extends CommonModel
{
    protected $tableName = 'options';
    public function add_label($name){

        if(empty($name)){
            $this->error = "标签名字不能为空";
            return false;
        }
        $name_len = mb_strlen($name,'UTF8');
        if($name_len > 6){
            $this->error = "标签名字不能超过6个字符";
            return false;
        }
        $res = $this->where(array('option_name'=>'label_name'))->find();
        if($res){
            $arr = json_decode($res['option_value'],true);
            $is_in = in_array($name,$arr);
            if($is_in === true){
                $this->error = "标签名字已存在";
                return false;
            }
            Array_push($arr,$name);
            $label = json_encode($arr);
            $arr['option_value'] = $label;
            $result = $this->where(array('option_name'=>"label_name"))->save($arr);
            if($result){
               return true;
            }else{
                $this->error = "参数错误";
                return false;
            }
        }else{
            $label = array();
            $label[] = $name;
            $label = json_encode($label);
            $arr['option_name'] = "label_name";
            $arr['option_value'] = $label;
            $result =$this->where(array('option_name'=>"label_name"))->add($arr);
            if($result){
                return true;
            }else{
                $this->error = "参数错误";
                return false;
            }
        }

    }
    //标签列表
    public function get_list(){
        $res = $this->where(array('option_name'=>'label_name'))->find();
        if($res){
            $list = json_decode($res['option_value'],true);
            return $list;
        }
    }

    public function delete_label($id){
        if(empty($id)){
            $this->error = "请勾选要删除的标签";
            return false;
        }
        $res = $this->where(array('option_name'=>'label_name'))->find();
        if($res){
            $arr = json_decode($res['option_value'],true);
            foreach($arr as $k=>$y){
                unset($arr[$id[$k]]);
            }
            $label = json_encode($arr);
            $arr['option_value'] = $label;
            $result = $this->where(array('option_name'=>"label_name"))->save($arr);
            if($result){
                return true;
            }else{
                $this->error = "参数错误";
                return false;
            }
        }
    }

    //前台首页数据3条动态
    public function get_first_list($city){
        $d_city = D('Destination')->get_info($city);
        $where['md.city'] = $d_city['city'];
        $where['md.is_delete'] = 0;
        $where['_string'] = "md.img is not null and md.img <> '' ";
        $list = M('MemberDynamic')
            ->alias('md')
            ->field('md.img,m.headimg,m.id as member_id,md.id,md.create_time')
            ->join('LEFT JOIN __MEMBER__ m on m.id = md.member_id')
            ->where($where)
            ->order('md.create_time desc')
            ->limit(3)
            ->select();
        foreach($list as $k=>$y){
            $list[$k]['time_'] = $this->Sec2Time(time() - $y['create_time']);
            if($y['img']){
                $img =  explode(',',$y['img']);
                $list[$k]['img'] = $img[0];
            }
        }
        return $list;
    }
    //前台当前地址动态列表
    public function get_curr_dynamic($search=array(),$order='',$page=1,$pageSize=10){
        if(!empty($search['city'])){
            $where['mf.city'] = $search['city'];
        }
;        if(empty($order)){
            $order=' mf.create_time desc,mf.id desc';
        }
        $where['mf.is_delete']=0;
        $where['mf.is_see'] = 1;
        $field="mf.*,m.nickname,m.headimg,get_gratuity_num(3,mf.id) as gratuity_num,(select count(mdc.id) from ehecd_member_dynamic_comment mdc where mdc.dynamic_id = mf.id) as comment_count";
        $count = M('MemberDynamic')
            ->alias('mf')
            ->where($where)
            ->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $list = M('MemberDynamic')
            ->alias('mf')
            ->field($field)
            ->join('LEFT JOIN __MEMBER__ m on m.id = mf.member_id')
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        $zan_id=M('ZanLog')->where(array('member_id'=>$search['member_id'],'type'=>0))->getField('to_id',true);

        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            if($vo['img']){
                $list[$key]['img']=explode(',',$vo['img']);
            }

            if(in_array($vo['id'],$zan_id)){
                $list[$key]['is_zan']=1;
            }else{
                $list[$key]['is_zan']=0;
            }

        }
//
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    //动态列表
    public function get_dynamic_list($data){
        $where['md.is_delete'] = 0;

        $data['start_time'] = empty($data['start_time'])?strtotime('1990-01-01'):strtotime($data['start_time']);

        $data['end_time'] = empty($data['end_time'])?time():strtotime($data['end_time']);


        $where['md.create_time'] = array('between',array($data['start_time'],$data['end_time']+86400));
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?15:$data['pageNum'];
        if(!empty($data['title'])){
            $where['md.title'] = array('like','%'.$data['title'].'%');
        }

        if(!empty($data['keyword'])){
            $where['m.mobile | m.nickname'] = array('like','%'.$data['keyword'].'%');
        }

        if(!empty($data['label'])){
            $where['md.label'] = $data['label'];
        }
        if(isset($data['is_see']) && $data['is_see'] >= '0' ){
            $where['md.is_see'] = $data['is_see'];
        }

        if(!empty($data['id'])){
            $where['md.id'] = $data['id'];
        }
        $count = M('MemberDynamic')
            ->alias('md')
            ->field('md.*,m.mobile,m.nickname')
            ->join('LEFT JOIN __MEMBER__ m on m.id = md.member_id')

            ->where($where)
            ->count();


        $list = M('MemberDynamic')
            ->alias('md')
            ->field('md.*,m.mobile,m.nickname,(select count(mdc.id) from ehecd_member_dynamic_comment mdc where mdc.dynamic_id = md.id and mdc.is_delete = 0) as comment_count,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = md.member_id')
            ->page($data['p'],$data['pageNum'])
            ->where($where)
            ->order('md.create_time asc')
            ->select();
        //echo M('MemberDynamic')->getLastSql();
        foreach($list as  $k=>$y){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$y['create_time']);
            if($y['img']){
                $list[$k]['img'] = explode(',',$y['img']);
            }
        }

        $result['list'] = $list;
        $result['totalPag'] = ceil($count/$data['pageNum']);
        return $result;
    }


    //动态评论列表
    public function get_comment_list($data){
        $where['mdc.is_delete'] = 0;

        if(!empty($data['dynamic_id'])){
            $where['mdc.dynamic_id'] = $data['dynamic_id'];
        }
        if(!empty($data['member_id'])){
            $where['mdc.member_id | mdc.to_member_id'] = $data['member_id'];
        }


        $list = M('MemberDynamicComment')
            ->alias('mdc')
            ->field('mdc.*,m.nickname,m1.nickname as to_name,m.headimg')
            ->join('LEFT JOIN __MEMBER__ m on m.id = mdc.member_id')
            ->join('LEFT JOIN __MEMBER__ m1 on m1.id = mdc.to_member_id')
            ->where($where)
            ->select();
        foreach($list as $k=>$y){
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$y['create_time']);
        }

        if(!empty($data['member_id'])){
            $arr = array();
            foreach($list as $y){
                if($y['member_id'] && $y['to_member_id']){
                    Array_push($arr,$y);
                }
            }
            return $arr;
        }else{
            return $list;
        }
        //return $list;
        //echo M('MemberDynamicComment')->getLastSql();
    }

    public function add_dynamic($data){
        if(empty($data['member_id'])){
            $this->error="请登录后操作";
            return false;
        }
        if(empty($data['title'])){
            $this->error="请输入标题";
            return false;
        }
        if(empty($data['content'])){
            $this->error="请输入内容";
            return false;
        }
        if(mb_str_len($data['title'])<2 || mb_str_len($data['title'])>30){
            $this->error="标题2-30个字符";
            return false;
        }
        if($data['img']){
            if(count($data['img'])>9){
                $this->error="最多上传9张图片";
                return false;
            }
            $data['img']=implode(',',$data['img']);
        }
        if(empty($data['label'])){
            $this->error="请选择标签";
            return false;
        }
        if(empty($data['lng'])||empty($data['lat'])||empty($data['province'])||empty($data['city'])){
            $this->error="请选择定位";
            return false;
        }
        if($data['is_see']==6){
            $this->error="请选择隐私权限";
            return false;
        }
        $data['create_time']=time();
        if(M('MemberDynamic')->add($data)){
            return true;
        }else{
            $this->error="发表失败";
            return false;
        }

    }

    //前台读取列表
    public  function get_list_mobile($search=array(),$order=0,$page=1,$pageSize=10){
        if($order){
            $order=" md.city = $order desc,md.create_time desc,md.id desc";
        }else{
            $order=' md.create_time desc,md.id desc';
        }
        $where=array();
        $where['md.is_delete']=0;
        if($search['label']){
            $where['md.label']=$search['label'];
        }
        if($search['member_id']){
            $sql="(select count(mf1.id) from ehecd_member_friends as mf1 where mf1.member_id=mf.to_member_id and mf1.to_member_id=mf.member_id) >0";
            $friend_member=M('MemberFriends')->alias('mf')
                ->field("mf.to_member_id")
                ->join("LEFT JOIN __MEMBER__ m on mf.to_member_id=m.id")
                ->where(array('m.is_delete'=>0,'member_id'=>$search['member_id'],'_string'=>$sql))
                ->select();
            foreach($friend_member as $key=>$vo){
                $friend_member_id[]=$vo['to_member_id'];
            }
            if($friend_member_id){
                $friend_member_id=implode(',',$friend_member_id);
                $where['_string']=" (md.is_see=1 or md.member_id={$search['member_id']} or (md.is_see=2 and md.member_id in (".$friend_member_id.") )  )";
                //有好友，显示完全公开和好友可见和自己发布的
            }else{
                $where['_string']=" (md.is_see=1 or md.member_id={$search['member_id']})";
                //没有好友，显示自己发布的和完全公开的
            }

        }else{
            $where['md.is_see']=1;//没有登录则只显示完全公开的动态
        }
        $count = $this->alias('md')
            ->table("ehecd_member_dynamic")
            //->join('LEFT JOIN __A_ITEM__ t on mlt.team_id=t.id')
            ->where($where)->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        //$field="t.id as team_id,t.title,r.region_name as destination_city_name,t.create_time,t.pay_money,t.cover_img,t.travel_time_s,mlt.id,mlt.team_id,m.headimg,t.member_id as send_member_id,m.nickname";
        //$field.=",get_like_team_count(mlt.team_id) as like_team_count,get_team_people_count(mlt.team_id) as people_count";
        $field="md.*,mm.headimg,mm.nickname,r.region_name as city_name,get_gratuity_num(3,md.id) as gratuity_num,";
        $field.="get_dynamic_comment_num(md.id) as comment_num";
        $list = $this->alias('md')
            ->table("ehecd_member_dynamic")
            ->field($field)
//            ->join('LEFT JOIN __A_ITEM__ t on mlt.team_id=t.id')
            ->join('LEFT JOIN __MEMBER__ mm on md.member_id=mm.id')
            ->join('LEFT JOIN __REGION__ r on r.region_id=md.city')
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();

        $zan_id=M('ZanLog')->where(array('member_id'=>$search['member_id'],'type'=>0))->getField('to_id',true);

        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            if($vo['img']){
                $list[$key]['img']=explode(',',$vo['img']);
            }

            if(in_array($vo['id'],$zan_id)){
                $list[$key]['is_zan']=1;
            }else{
                $list[$key]['is_zan']=0;
            }

        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    //获取动态详情
    public function detail($id){
        //        $field = "t.*,b.introduction,b.name as business_name,b.headimg as business_headimg,b.is_bond,d.name as destination_1_name,d1.name as destination_2_name";
//        $field .= ",get_gratuity_num(0,s.id) as gratuity_num,get_strategy_comment_num(s.id) as comments_count";
//        $field .=",get_collection_num(0,s.id) as collection_num";
        $field="md.*,m.nickname,m.headimg,r.region_name as city_name,get_gratuity_num(3,md.id) as gratuity_num";
//        $field.=",get_friends_count_by_member_id(t.member_id) as friends_count,get_gratuity_num(1,t.id) as gratuity_num";
//        $field.=",get_travels_comment_num(t.id) as comment_num";
        $where['md.id'] = $id;
        $info = $this->alias('md')
            ->table("__MEMBER_DYNAMIC__")
            ->field($field)
            ->join('LEFT JOIN __MEMBER__ m on md.member_id=m.id')
////                ->join('LEFT JOIN __DESTINATION__ d on s.destination_1=d.id')
            ->join('LEFT JOIN __REGION__ r on r.region_id=md.city')
            ->where($where)
            ->find();
        if($info['img']){
            $info['img']=explode(',',$info['img']);
        }
        return $info;
    }


    //回复评论
    public function add_comment($member_id,$id,$content,$to_member_id,$pid=0){
        if(!$id){
            $this->error="请选择动态文章";
            return false;
        }
        if(empty($content)){
            $this->error="请输入评论";
            return false;
        }
        if(mb_str_len($content)>200){
            $this->error="评论1-200个字";
            return false;
        }
        $data['member_id']=$member_id;
        $data['dynamic_id']=$id;
        $data['content']=$content;
        $data['to_member_id']=$to_member_id;
        $data['create_time']=time();
        $data['pid']=$pid;
        if(M('MemberDynamicComment')->add($data)){
            return true;
        }else{
            $this->error="评论失败";
            return false;
        }
    }


    //前台读取评论列表
    public  function get_list_comment($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order=' mdc.create_time desc,mdc.id desc';
        }
        $where=array();
        $where['mdc.is_delete']=0;
        $field="mdc.*,m.nickname,m.headimg,m.nickname,m.headimg";
        $where['mdc.to_member_id']=0;
        $where['mdc.dynamic_id']=$search['dynamic_id'];
        $count = $this->alias('mdc')
            ->table('ehecd_member_dynamic_comment')
            ->where($where)->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $list = $this->alias('mdc')
            ->table('ehecd_member_dynamic_comment')
            ->field($field)
            ->join("LEFT JOIN __MEMBER__ m on mdc.member_id=m.id")
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
//        echo $this->getLastSql();
//        exit;
        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $child=$this->alias('d')
                ->table('ehecd_member_dynamic_comment')
                ->field('d.*,m.nickname,m1.nickname as to_nickname')
                ->join("LEFT JOIN __MEMBER__ m on d.member_id=m.id")
                ->join("LEFT JOIN __MEMBER__ m1 on d.to_member_id=m1.id")
                ->where(array('d.pid'=>$vo['id'],'d.is_delete'=>0,'d.dynamic_id'=>$vo['dynamic_id'],'d.to_member_id'=>array('gt',0)))
                ->order("d.create_time asc")
                ->select();
            $list[$key]['child']=$child;
        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['total']=$count;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    function Sec2Time($time){
        if(is_numeric($time)){
            $value = array(
                //"years" => 0,
                "days" => 0,
                "hours" => 0,
                "minutes" => 0,
                "seconds" => 0,
            );
            /*if($time >= 31556926){
                $value["years"] = floor($time/31556926);
                $time = ($time%31556926);
            }*/
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            //return (array) $value;
            $t = '';
            if($value['days'] && $value['hours'] && $value['minutes'] && $value['seconds']){
                $t = $value["days"] ."天前";
            }elseif(empty($value['days']) && $value['hours'] && $value['minutes'] && $value['seconds']){
                $t =  $value["hours"] ."小时前";
            }else if(empty($value['days']) && empty($value['hours']) && $value['minutes'] && $value['seconds']){
                $t = $value["minutes"] ."分钟前";
            }else if(empty($value['days']) && empty($value['hours']) && empty($value['minutes']) && $value['seconds']){
                $t = $value["seconds"]."秒前";
            }

            //$t= $value["days"] ."天"." ". $value["hours"] ."小时". $value["minutes"] ."分".$value["seconds"]."秒";//$value["years"] ."年".
            Return $t;

        }else{
            return (bool) FALSE;
        }
    }

    function _before_write(){
        F("label_name",null);
    }

}