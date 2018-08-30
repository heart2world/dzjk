<?php

namespace Api\Controller;

use Common\Model\CouponModel;
use Think\Controller;
use Common\Model\WeChatModel;
use Common\Model\Ticket\TicketActiveModel;
use Common\Model\Seckill\DiscountModel;

/*
 * 自动拒绝处理
 */
class CrontabController extends Controller
{
    public function index(){
        F('sss',0);
        $coupon_model=new CouponModel();
        $coupon_model->crontab_send();//自动投放优惠券
        $this->auto_cancel();//自动取消
        $this->auto_confirm();//自动确认
        $this->auto_tc();//自动提成
        $this->Test();//自动下架
        $this->goods_not_discount();//商品自动取消特惠
        D('AItem')->team_change_type();
        D('Interlocution')->plan_adopt();
    }
    public function Test(){
        $t_a_model=new TicketActiveModel();
        $t_a_model->down_frame();//自动下架
    }
    //商品特惠过期,取消特惠状态
    public function goods_not_discount(){
        $t_a_model=new DiscountModel();
        $t_a_model->set_no_discount();
    }
    public function auto_cancel(){
        $config=getOptions("site_options");
        if($config['order_cancel_time']>0){
            //多少小时取消订单
            $ext_time=strtotime("-".$config['order_cancel_time']." hours");
            M('Order')
                    ->where("order_type<>2 and status=2 and create_time<$ext_time")
                    ->save(array('status'=>1,'cancel_type'=>3));
            //系统被动取消未付款的订单
        }
    }
    public function auto_confirm(){
        $config=getOptions("site_options");
        if($config['goods_order_time']>0){
            //多少天确认收货订单
            $ext_time=strtotime("-".$config['goods_order_time']." day");

            M('Order')
                    ->where("status=8 and delivery_time>0 and  delivery_time<$ext_time")
                    ->save(array('status'=>100,'confirm_time'=>time()));
            //自动确认订单
        }
    }

    public function auto_tc(){
        $config=getOptions("site_options");
        $order_list=M('Order')->alias('o')
                ->field("o.pay_price,o.id,m.path,m.parent_id,o.member_id")
                ->join("LEFT JOIN __MEMBER__ m on o.member_id=m.id")
                ->where("o.status>=100 and o.order_type <> 2 and o.business_owned_id=0 and o.is_to_member_id=0")->order('o.id desc')->limit(50)->select();

        //订单已完成，商品订单，自营订单，没有提成的
        foreach($order_list as $key=>$vo){
            $res=false;
            $order_model=M('Order');
            try{
                $order_model->startTrans();
                if($vo['pay_price']>0){

                    //支付金额大于0才提成
                    if($vo['parent_id']==0){
                        //没有上级，他就是第一级，不提成
                       $res=true;
                    }else{

                        $first_member=M('Member')->field('path,parent_id,id,is_tuan')->where(array('id'=>$vo['parent_id'],'is_delete'=>0))->find();

                        if($first_member&&$first_member['is_tuan']==1){
                            //存在第一级会员
                            //需要给一级会员提成
                            $res_1=$this->to_money($first_member['id'],$config,$vo['pay_price'],1,$vo['member_id'],$vo['id']);

                            $two_member=M('Member')->field('path,parent_id,id,is_tuan')->where(array('id'=>$first_member['parent_id'],'is_delete'=>0))->find();
                            if($two_member&&$two_member['is_tuan']==1){
                                $res_2=$this->to_money($two_member['id'],$config,$vo['pay_price'],2,$vo['member_id'],$vo['id']);
                                $three_member=M('Member')->field('path,parent_id,id,is_tuan')->where(array('id'=>$two_member['parent_id'],'is_delete'=>0))->find();
                                if($three_member&&$three_member['is_tuan']==1){
                                    $res_3=$this->to_money($three_member['id'],$config,$vo['pay_price'],3,$vo['member_id'],$vo['id']);
                                }else{
                                    $res_3=true;
                                    //第三级会员已被移除，或者不是团主
                                }
                            }else{
                                $res_2=true;
                                $res_3=true;
                                //第二级会员已经被删除了。不提成
                            }
                        }else{
                            $res_1=true;
                            $res_2=true;
                            $res_3=true;
                            //第一级会员已经被删除了,或者已经不是团主了。不提成
                        }

                        if($res_1&&$res_2&&$res_3){
                            $res=true;
                        }else{
                            $res=false;
                        }
                    }

                    $res1=$order_model->where(array('id'=>$vo['id']))->setField('is_to_member_id',1);
                    if($res==true&&$res1!==false){
                        $order_model->commit();
                    }else{
                        E('该订单修改状态失败:订单编号:'.$vo['id']."ERR:".$order_model->getError());
                    }

                }else{

                    //支付金额小于登录0
                    $res=$order_model->where(array('id'=>$vo['id']))->setField('is_to_member_id',1);
                    if($res!==false){
                        $order_model->commit();
                    }else{
                        E('该订单修改状态失败:订单编号:'.$vo['id']."ERR:".$order_model->getError());
                    }
                }
            }catch (\Exception $e){
                $order_model->rollback();
                continue;//遇到错误继续循环下一次
            }
        }
    }
    private function to_money($member_id,$config,$ktc_price,$dengji,$buy_member_id,$order_id){
        if($ktc_price<=0){
            //可提现金额小于0，不提成返回成功
            return true;
        }else{
            $bili=floatval($config['rank_'.$dengji]);
            if($bili<=0){
                //后台设置比例小于0，直接返回成功
                return true;
            }
            $price=$ktc_price*$bili/100;
            if($price<0){
                return true;
                //提成金额小于0，不提了
            }
            $money_model=D('MemberMoney');
            $res1=$money_model->add_money($member_id,$price,2,'获得佣金');
            $commission_model=D('MemberCommission');
            $res2=$commission_model->add_commission($member_id,$price,$ktc_price,$dengji,$buy_member_id,$bili,$order_id);
            if($res1&&$res2){
                return true;
            }else{
                return false;
            }
        }
    }

//    //拒绝邀请模板消息
//    public function sendTemplateMsg1($openid,$name){
//        //$templateId = "d_ddFtYtT4VV1JJ21NWZWQqYzp7ThFqfKxpZbshsUvg";
//        $templateId = "XXHg9toZHhXsT5nf-JPg_XK_Qw4DsOb0juPfLCnldb0";
//
//        $wx = new WeChatModel();
//        $data = array(
//            "first"=>array("value"=>"非常遗憾，$name 拒绝了您的请求","color"=>"#173177"),
//            "keyword3"=>array("value"=>date('Y-m-d H:i:s'),"color"=>"#173177"),
//            "remark"=>array("value"=>"退款将返回到微信零钱中。","color"=>"#173177"),
//        );
//        $url="http://".HTTP_HOST.U('Mobile/Order/my_student');
//        $res=$wx->templateMsg($openid,$templateId,$url,$data);
//        if($res!==false){
//            return true;
//        }else{
//            return false;
//        }
//    }

    //接收邀请消息i
//    public function sendTemplateMsg($openid,$name,$mobile){
//        //$templateId = "oLSffc4UMGCZy09x_Pbi2S0WoTyH1IIcvN-m78MCw_c";
//        $templateId = "aERVQZkdYwBhfFnC35ndQerRYgWCqWwhti_lMyRE55M";
//
//        $wx = new WeChatModel();
//        $data = array(
//            "first"=>array("value"=>"好消息，接受了您的邀请","color"=>"#173177"),
//            "keyword1"=>array("value"=>$name,"color"=>"#173177"),
//            "keyword2"=>array("value"=>$mobile,"color"=>"#173177"),
//            "remark"=>array("value"=>"点击查看详情。","color"=>"#173177"),
//        );
//        $url="http://".HTTP_HOST.U('Mobile/Order/my_student');
//        $res=$wx->templateMsg($openid,$templateId,$url,$data);
//        if($res!==false){
//            return true;
//        }else{
//            return false;
//        }
//    }
}