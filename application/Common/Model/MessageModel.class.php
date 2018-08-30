<?php

namespace Common\Model;

use Common\Model\CommonModel;

class MessageModel extends CommonModel
{

    protected $_validate = array(
        array('title','require','消息标题必须填写！',1),
        array('title','1,50','消息标题长度1-50字',1,'length'),
        array('content','require','消息内容必须填写！',1),
    );

    protected $_auto = array (
        array('create_time','time',1,'function'),
        array('create_time','time',2,'function'),
    );

    public  function get_list($search=array(),$order='',$page=1,$pageSize=5){
        if(empty($order)){
            $order='m.create_time desc';
        }
        $where=array();
        if($search['st_time']&&$search['end_time']){
            if($search['st_time']>$search['end_time']){
                return '开始时间大于结束时间';
                return false;
            }else{
                $search['end_time']=strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
                $where['m.create_time']=array(array('egt',$search['st_time']),array('elt',$search['end_time']));
                //有开始时间和结束时间
            }
        }elseif($search['st_time']&&!$search['end_time']){
            $where['m.create_time']=array('egt',$search['st_time']);//有开始时间无结束时间
        }elseif(!$search['st_time']&&$search['end_time']){
            $search['end_time']=strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
            $where['m.create_time']=array('elt',$search['end_time']);//无开始时间有结束时间
        }
        if(!empty($search['keyword'])){
            $where['m.title'] = array('like',"%".$search['keyword']."%");
        }
        $where['m.is_delete'] = 0;
        $where['m.types'] = array('neq',1);

        $where['m.type'] = 0;

        $count = $this->alias('m')
            ->where($where)->count();
        if($pageSize==0&&$count>10000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field='m.*,u.user_nicename as admin_name';
        $list = $this->alias('m')
            ->field($field)
            ->join('LEFT JOIN __USERS__ u on u.id=m.send_users_id')
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        foreach($list as $key=>&$vo){
            $vo['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            $vo['content'] = htmlspecialchars_decode($vo['content']);
//            $vo['reads'] = D('MemberMessage')->where(array('mes_id'=>$vo['id']))->count();

        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }


    public function getUserByType($type_s,$nickname,$page,$pageSize=10)
    {
        if($type_s == 1 )
        {
            $where['types'] = array('in','1,2');
            $where['is_ok'] = array('in','0,2');

        }
        if($type_s == 2)
        {
            $where['types'] = intval($type_s);
            $where['is_ok'] = 1;
        }


        if($nickname)
        {
            $where['nickname'] = array('like','%'.$nickname.'%');
        }
        $where['is_delete'] = 0;
        $Mdata = D('Member')
            ->field('id,nickname as name,types,is_ok')
            ->where($where)
            ->page($page,$pageSize)
            ->select();
        if($Mdata)
        {
            foreach ($Mdata as &$mdatum) {

                if($mdatum['types'] == 1 && $mdatum['is_ok'] == 0)
                {
                    $mdatum['type'] =  '用户';
                }
                if($mdatum['types'] == 2 && $mdatum['is_ok'] == 0)
                {
                    $mdatum['type'] =  '用户';
                }
                if($mdatum['types'] == 2 && $mdatum['is_ok'] == 2)
                {
                    $mdatum['type'] =  '用户';
                }
                if($mdatum['types'] == 2 && $mdatum['is_ok'] == 1)
                {
                    $mdatum['type'] =  '医生';
                }
            }
        }
        $count = D('Member')->where($where)->count();
        $total_page = ceil($count / $pageSize);
        $result['p'] = $page;
        $result['total'] = $count;
        $result['pagesize'] = $pageSize;
        $result['total_page'] = $total_page;
        $result['list'] = $Mdata;
        return $result;
    }
    public function getUserByMid($type_s,$nickname,$id,$page,$pageSize=10)
    {

        if($nickname)
        {
            $whereAll['nickname'] = array('like','%'.$nickname.'%');
        }

        if($id)
        {
            $where['ms.mes_id'] = $id;
        }
        $whereAll = array();
//        receive_member_type 0所有人1指定会员  2 医生  3普通用户   receive_member_id  create_time
        $mifo = M('Message')->field('receive_member_type,receive_member_id,create_time')->where(array('id'=>$id))->find();
//var_dump($mifo);
//        $whereAll['create_time'] = array('egt',$mifo['create_time']);
        $whereAll['is_delete'] = 0;



        if($mifo['receive_member_type'] == 0)
        {
            $whereAll['id'] = array('neq',0);
        }elseif ($mifo['receive_member_type'] == 1)
        {
            $whereAll['id'] = array('in',$mifo['receive_member_id']);
        }elseif ($mifo['receive_member_type'] == 2)
        {
            $whereAll['id'] = array('neq',0);
            $whereAll['types'] = array('in','1,2');
            $whereAll['is_ok'] = 1;
        }elseif ($mifo['receive_member_type'] == 3)
        {
            $whereAll['id'] = array('neq',0);
            $whereAll['types'] = array('in','1,2');
            $whereAll['is_ok'] = array('in','0,2');
        }

        if($type_s == 1)
        {
            $whereAll['types'] = array('in','1,2');
            $whereAll['is_ok'] = array('in','0,2');
        }
        if($type_s == 2)
        {
            $whereAll['types'] = array('in','1,2');
            $whereAll['is_ok'] = 1;
        }
//        $whereAll['id'] = array('neq',0);

            $Mdata = D('Member')
            ->field('nickname as name,types')
            ->where($whereAll)
            ->page($page,$pageSize)
            ->select();
//var_dump($Mdata);
//        $Mdata = D('MemberMessage')->alias('ms')
//            ->field('MM.nickname as name,MM.types')
//            ->join('LEFT JOIN __MEMBER__ MM on MM.id=ms.member_id')
//            ->where($where)
//            ->page($page,$pageSize)
//            ->select();

        if($Mdata)
        {
            foreach ($Mdata as &$mdatum) {
//                if($mdatum['name'] = '官方发布')
//                {
//                    unset($mdatum);
//                }
                $mdatum['type'] = $mdatum['types'] == 1 ? '用户' : '医生';
            }
        }
        $count = D('Member')->alias('M')
            ->where($whereAll)
            ->page($page,$pageSize)
            ->count();

//        $count = D('MemberMessage')->alias('ms')->join('LEFT JOIN __MEMBER__ MM on MM.id=ms.member_id')->where($where)->count();



        $total_page = ceil($count / $pageSize);
        $result['p'] = $page;
        $result['total'] = $count;
        $result['pagesize'] = $pageSize;
        $result['total_page'] = $total_page;
        $result['list'] = $Mdata;
        return $result;
    }


    public function get_info($id){
        $info=$this->alias('s')
            ->field('s.*,r.region_name as province_name,r1.region_name as city_name')
            ->join("LEFT JOIN __REGION__ r on r.region_id=s.province")
            ->join("LEFT JOIN __REGION__ r1 on r1.region_id=s.city")
            ->where(array('s.id'=>$id))->find();
        return $info;
    }

    public function get_user($data){


        $data['start_time'] = empty($data['start_time'])?strtotime('2000-01-01'):strtotime($data['start_time']);
        $data['end_time'] = empty($data['end_time'])?time():strtotime($data['end_time'])+86399;
        $where['create_time'] = array('between',array($data['start_time'],$data['end_time']));

        if(!empty($data['mobile'])){
            $where['mobile'] = array('like','%'.$data['mobile'].'%');
        }
        $list['list'] = M('Member')
            ->field('nickname,mobile,id')
            ->where(array('is_delete'=>0))
            ->where($where)
            ->select();
        return $list;
    }


    //批量删除
    public function deleteBatch($ids){
        $ids=ids_to_ids($ids);
        if($ids){
            $res=$this->where(array('id'=>array('in',$ids)))->setField('is_delete',1);
            if($res){
                $res=$this->where(array('id'=>array('in',$ids)))->getField('title',true);
                return implode(',',$res);
            }else{
                $this->error="操作失败";
                return false;
            }

        }else{
            $this->error="请至少选择一条信息";
            return false;
        }
    }


    //用户删除站内信
    public function member_delete($id,$member_id,$member_type=0){

        $sql="is_delete=0 and id=$id and (receive_member_type=0 or (receive_member_type=3 and receive_member_id=$member_id)";
//        if($member_type==1){ //学生
//            $sql.=" or (receive_member_type=1)";
//        }elseif($member_type==2){
//            //公司
//            $sql.=" or (receive_member_type=2)";
//        }elseif($member_type==3){
//            //学生和公司
//            $sql.=" or (receive_member_type=1 or receive_member_type=2)";
//        }
        $sql.=")";

        $info=$this->where($sql)->find();
        if($info){
            $res=$this->update_status($info,$member_id,false);
            if($res!==false){
                return true;
            }else{
                $this->error="删除失败";
                return false;
            }
        }else{
            $this->error="没有找到可删除的消息";
            return false;
        }
    }

    public function get_message_info($id,$member_id,$member_type=0){
        $sql="m.is_delete=0 and m.id=$id and (m.receive_member_type=0 or (m.receive_member_type=3 and m.receive_member_id=$member_id)";
//        if($member_type==1){ //学生
//            $sql.=" or (m.receive_member_type=1)";
//        }elseif($member_type==2){
//            //公司
//            $sql.=" or (m.receive_member_type=2)";
//        }elseif($member_type==3){
//            //学生和公司
//            $sql.=" or (m.receive_member_type=1 or receive_member_type=2)";
//        }
        $sql.=") and (select count(ms.id) from ehecd_member_message ms where ms.is_delete=1 and  ms.mes_id=m.id and ms.member_id={$member_id}) = 0";

        $info=$this->alias('m')->where($sql)->find();
        if($info){
            return $info;
        }else{
            return false;
        }
    }

    //更新状态
    private function update_status($info,$member_id,$is_read=true){
        $field=$is_read?'is_read':'is_delete';
        $field1=$is_read?'is_read':'is_member_delete';
        if($info['receive_member_type']==3){
            $res=$this->where(array('id'=>$info['id']))->setField($field1,1);
        }else{
            $count=M('MemberMessage')->where(array('mes_id'=>$info['id'],'member_id'=>$member_id))->count();
            if($count==0){
                $res=M('MemberMessage')->add(array('mes_id'=>$info['id'],'member_id'=>$member_id,$field=>1));
            }else{
                $res=M('MemberMessage')->where(array('mes_id'=>$info['id'],'member_id'=>$member_id))->save(array($field=>1));
            }
        }
       return $res;
    }

    //用户阅读站内信
    public function member_read($id,$member_id,$member_type){
        $info=$this->get_message_info($id,$member_id,$member_type);
        if($info){
            $res=$this->update_status($info,$member_id,true);
            if($res!==false){
                return true;
            }else{
                $this->error="阅读失败";
                return false;
            }
        }else{
            return "没有找到可阅读的消息";
        }
    }

    //前台读取
    public  function list_memberBack($search=array(),$order='',$page=1,$pageSize=10){


        $search['member_id'];

        $mifo = M('Member')->field('types,is_ok')->where(array('id'=>$search['member_id']))->find();

        if($mifo['types'] == 2 && $mifo['is_ok'] == 1)
        {
            $ys = 1;
        }else
        {
            $ys = 0;
        }



        if(empty($order)){
            $order=' m.id desc';
        }
        $where=array();
        $sql="(  m.receive_member_type=0 or m.receive_member_type=2 or (m.receive_member_type=3  and m.receive_member_id={$search['member_id']})";
        if($search['reg_time']){
            $where['m.create_time']=array('egt',$search['reg_time']);
        }
        $where['m.type'] = 0;
        $sql.=") and (select count(ms.id) from ehecd_member_message ms where ms.is_delete=1 and  ms.mes_id=m.id and ms.member_id={$search['member_id']}) = 0";

        $where['_string']=$sql;
        $where['m.is_delete']=0;
        $where['m.is_member_delete']=0;
        $count = $this->alias('m')
            ->where($where)->count();
        if($pageSize==0&&$count>10000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field="m.*,(select count(ms.id) from ehecd_member_message ms where ms.is_read=1 and ms.mes_id=m.id and ms.member_id={$search['member_id']}) as is_read1";
        $list = $this->alias('m')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();


        foreach($list as $key=>$vo){
            $list[$key]['create_time']=formatTime($vo['create_time']);
            if($vo['receive_member_type']!=3){
                if($vo['is_read1']){
                    $list[$key]['is_read']=1;
                }else{
                    $list[$key]['is_read']=0;
                }
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

    public  function list_member($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order=' m.id desc';
        }
        $where=array();
//        $sql="(  m.receive_member_type=0 or m.receive_member_type=2 or (m.receive_member_type=3  and m.receive_member_id={$search['member_id']})";
//        $sql="(m.receive_member_type=0  or m.receive_member_id = {$search['member_id']}";
        $sql="(m.receive_member_type=0  or (m.receive_member_type=1 and m.receive_member_id like '%{$search['member_id']}%')";
//        $sql="(m.receive_member_type=0 )";
        if($search['reg_time']){
            $where['m.create_time']=array('egt',$search['reg_time']);
        }
        $where['m.type'] = 0;
        if($search['member_type']==2){ //医生
            $sql.=" or (m.receive_member_type=2)";
        }elseif($search['member_type']==3){     //普通用户
            $sql.=" or (m.receive_member_type=3)";
        }
//        elseif($search['member_type']==0){
//            if($search['is_company']==1){
//                $sql.=" or (m.receive_member_type=2)";
//            }else{
//                $sql.=" or (m.receive_member_type=1)";
//            }
            //学生和公司
            //$sql.=" or (m.receive_member_type=1 or m.receive_member_type=2)";
//        }


        $sql.=") and (select count(ms.id) from ehecd_member_message ms where ms.is_delete=1 and  ms.mes_id=m.id and ms.member_id={$search['member_id']}) = 0";

        $where['_string']=$sql;
        $where['m.is_delete']=0;
        $where['m.is_member_delete']=0;
        $count = $this->alias('m')
            ->where($where)->count();
        if($pageSize==0&&$count>10000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field="m.*,(select count(ms.id) from ehecd_member_message ms where ms.is_read=1 and ms.mes_id=m.id and ms.member_id={$search['member_id']}) as is_read1";
        $list = $this->alias('m')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();


        foreach($list as $key=>$vo){

//            $list[$key]['create_time'] = formatTime($vo['create_time']);
            $list[$key]['create_time'] = $this->modifyDate($vo['create_time']);
            if($vo['receive_member_type']!=3){
                if($vo['is_read1']){
                    $list[$key]['is_read']=1;
                }else{
                    $list[$key]['is_read']=0;
                }
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


    public function modifyDate($date)
    {

        $daySJ = strtotime(date('Y-m-d',time()));  //当天0点

        if($date >= $daySJ)
        {
            return date('H:i',$date);
        }else if($date >= $daySJ - 86399)
        {
            return '昨天'.date('H:i',$date);
        }else
        {
            return date('Y-m-d',$date);
        }
    }

    public  function list_member_count($member_id,$reg_time=0){
        $where=array();
        $sql="((  m.receive_member_type=0 or (m.receive_member_type=3 and m.receive_member_id=$member_id)";
        if($reg_time){
            $where['m.create_time']=array('egt',$reg_time);
        }
//        if($member_type==1){ //学生
//            $sql.=" or (m.receive_member_type=1)";
//        }elseif($member_type==2){
//            //公司
//            $sql.=" or (m.receive_member_type=2)";
//        }elseif($member_type==3){
//            //学生和公司
//            if($is_company==1){
//                $sql.=" or (m.receive_member_type=2)";
//            }else{
//                $sql.=" or (m.receive_member_type=1)";
//            }
//           // $sql.=" or (m.receive_member_type=1 or m.receive_member_type=2)";
//        }
        $sql.=") and (select count(ms.id) from ehecd_member_message ms where ms.is_delete=1 and  ms.mes_id=m.id and ms.member_id=$member_id) = 0";
        $sql.=" ) and ((m.receive_member_type=3 and is_read=0 ) or (m.receive_member_type!=3 and (select count(ms.id) from ehecd_member_message ms where ms.is_read=1 and  ms.mes_id=m.id and ms.member_id=$member_id)=0) )";
        $where['_string']=$sql;
        $where['m.is_delete']=0;
        $where['m.is_member_delete']=0;
        $count = $this->alias('m')
            ->where($where)->count();
        return $count;
    }
}