<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 *      用户余额变更记录
 * Date: 2016/6/5 0005
 * Time: 上午 10:19
 */

namespace Common\Model;

use Common\Model\CommonModel;
use Common\Model\MemberModel;
use Common\Model\IntegralLogModel;

class MemberMoneyModel extends CommonModel
{


    public function getMemberAfterPrice($member_id)
    {
        if($member_id)
        {
            $after_price = M('MemberMoney')->order('id desc')->where(array('member_id'=>$member_id))->getField('after_price');
            return $after_price > 0 ? $after_price : 0;
        }else
        {
            return '参数错误';
        }
    }

    public function getMemberAfterIntegral($member_id)
    {
        if($member_id)
        {
            $after_integral = M('IntegralLog')->order('id desc')->where(array('member_id'=>$member_id))->getField('after');;
            return $after_integral > 0 ? $after_integral : 0;
        }else
        {
            return '参数错误';
        }
    }

    //添加余额
    public function add_money($member_id,$price,$change_type,$note){
        $money_model=new MemberModel();
        $money=(float)$money_model->where(array('id'=>$member_id))->getField('money');
        $data['member_id']=$member_id;
        $data['price']=$price;
        $data['type']=1;
        $data['after_price']=$money+$price;
        $data['before_price']=$money;
        $data['create_time']=time();
        $data['change_type']=$change_type;
        $data['note']=$note;
        $res=$this->add($data);
        $res1=$money_model->where(array('id'=>$member_id))->setInc('money',$price);
        if($res&&$res1!==false){
            return true;
        }else{
            return false;
        }
    }

    //减余额
    public function minus_money($member_id,$price,$change_type,$note){
        $money_model=new MemberModel();
        $money=(float)$money_model->where(array('id'=>$member_id))->getField('money');
        if(($money-$price)<0){
            $this->error="余额不足，操作失败";
            return false;
        }
        $data['member_id']=$member_id;
        $data['price']=$price;
        $data['type']=0;
        $data['after_price']=$money-$price;
        $data['before_price']=$money;
        $data['create_time']=time();
        $data['change_type']=$change_type;
        $data['note']=$note;
        $res=$this->add($data);
        $res1=$money_model->where(array('id'=>$member_id))->setDec('money',$price);
        if($res&&$res1!==false){
            return true;
        }else{
            return false;
        }
    }
}