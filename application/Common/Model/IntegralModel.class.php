<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/1
 * Time: 14:45
 */

namespace Common\Model;


class IntegralModel extends CommonModel
{
    protected $tableName  = 'integral_log';
    public function get_list($data){

        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?20:$data['pageNum'];

        $data['start_time'] = empty($data['start_time'])?strtotime('1990-01-01'):strtotime($data['start_time']);
        $data['end_time'] = empty($data['end_time'])?time():strtotime($data['end_time']);
        $where['il.create_time'] = array('between',array($data['start_time'],$data['end_time']+86400));

        if(!empty($data['nickname'])){
            $where['m.nickname'] = array('like','%'.$data['nickname'].'%');
        }
        if(!empty($data['mobile'])){
            $where['m.mobile'] = array('like','%'.$data['mobile'].'%');
        }

        $count = $this
            ->alias('il')
            ->field("il.*,m.nickname,m.mobile,m1.nickname as to_name ")
            ->join('LEFT JOIN __MEMBER__ m on m.id = il.member_id')
            ->join('LEFT JOIN __MEMBER__ m1 on m1.id = il.to_member_id')
            ->where($where)
            ->count();

        $list = $this
            ->alias('il')
            ->field("il.*,(select  FROM_UNIXTIME(il.create_time,'%Y-%m-%d %H:%i:%S'))as create_times ,m.nickname,m.mobile,m1.nickname as to_name ")
            ->join('LEFT JOIN __MEMBER__ m on m.id = il.member_id')
            ->join('LEFT JOIN __MEMBER__ m1 on m1.id = il.to_member_id')
            ->where($where)
            ->page($data['p'],$data['pageNum'])
            ->order('il.create_time desc')
            ->select();

        foreach($list as $k=>$y){
            switch($y['change_type']){
                case 1 :$list[$k]['change_type'] = "订单成交";break;
                case 2 :$list[$k]['change_type'] = "点评攻略";break;
                case 3 :$list[$k]['change_type'] = "点评游记";break;
                case 4 :$list[$k]['change_type'] = "点评景点";break;
                case 5 :$list[$k]['change_type'] = "点评熊猫趣玩";break;
                case 6 :$list[$k]['change_type'] = "点评熊猫户外";break;
                case 7 :$list[$k]['change_type'] = "点评熊猫游";break;
                case 8 :$list[$k]['change_type'] = "发游记";break;
                case 9 :$list[$k]['change_type'] = "发提问";break;
                case 10 :$list[$k]['change_type'] = "回答提问";break;
                case 11 :$list[$k]['change_type'] = "转发";break;
                case 12 :$list[$k]['change_type'] = "兑换商品";break;
                case 13 :$list[$k]['change_type'] = $y['nickname']." 打赏给 ".$y['to_name'];break;
                case 14 :$list[$k]['change_type'] = $y['to_name']." 打赏给 ".$y['nickname'];break;
                case 15 :$list[$k]['change_type'] = "打赏平台";break;
                case 17 :$list[$k]['change_type'] = "问题采纳";break;
                case 18 :$list[$k]['change_type'] = "点评动态";break;
                case 19 :$list[$k]['change_type'] = "提问过期，系统返还";break;
                case 20 :$list[$k]['change_type'] = "发提问支付积分";break;
            }
        }
        $result['list'] = $list;
        $result['totalPage'] = ceil($count/$data['pageNum']);
        return $result;
    }

    /**
     * @param $member_id 减少会员
     * @param $integral 积分
     * @param $change_type 类型
     * @param $to_member_id 转到会员ID
     * @return bool
     */
    public function reduce_integral($member_id,$integral,$change_type,$to_member_id){
        if(!$member_id){
            $this->error="参数错误";
            return false;
        }
        $integral=intval($integral);
        if($integral<=0){
            $this->error="积分数量不能为空";
            return false;
        }
        $old_integral=M('Member')->where(array('id'=>$member_id))->getField('integral');
        if($integral>$old_integral){
            $this->error="积分不足";
            return false;
        }
        $data['member_id']=$member_id;
        $data['change']=$integral;
        $data['change_type']=$change_type;
        $data['change_status']=2;
        $data['after']=$old_integral-$integral;
        $data['to_member_id']=$to_member_id;
        $data['create_time']=time();
        $this->startTrans();
        $res=$this->add($data);
        if($res){
            $res1=M('Member')->where(array('id'=>$member_id))->setDec('integral',$integral);
            if($res1){
                $this->commit();
                return true;
            }else{
                $this->error="减少积分失败";
                return false;
            }
        }else{
            $this->error="减少积分失败";
            return false;
        }
    }

    /**
     * @param $member_id 增加会员
     * @param $integral 积分
     * @param $change_type 类型
     * @param $to_member_id 来自会员ID
     * @param $to_id 来自提问ID
     * @return bool
     */
    public function add_integral($member_id,$integral,$change_type,$to_member_id=0,$to_id=0){
        if(!$member_id){
            $this->error="参数错误";
            return false;
        }
        $integral=intval($integral);
        if($integral<=0){
            $this->error="积分数量不能为空";
            return false;
        }
        $old_integral=M('Member')->where(array('id'=>$member_id))->getField('integral');
        $data['member_id']=$member_id;
        $data['change']=$integral;
        $data['change_type']=$change_type;
        $data['change_status']=1;
        $data['after']=$old_integral+$integral;
        $data['to_member_id']=$to_member_id;
        $data['create_time']=time();
        $data['to_id'] = $to_id;
        $this->startTrans();
        $res=$this->add($data);
        if($res){
            $res1=M('Member')->where(array('id'=>$member_id))->setInc('integral',$integral);
            if($res1){
                $this->commit();
                return true;
            }else{
                $this->error="增加积分失败";
                return false;
            }
        }else{
            $this->error="增加积分失败";
            return false;
        }
    }
}