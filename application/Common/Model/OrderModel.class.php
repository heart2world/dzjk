<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 *      订单类
 * Date: 2016/6/5 0005
 * Time: 上午 10:19
 */

namespace Common\Model;

class OrderModel extends CommonModel
{

    protected $_validate = array(
        array('name','require','客户名称必须填写！',1),
        array('name','1,30','客户名称长度1-30字',1,'length'),
        array('cate_id','require','所属商品分类必须选择！',1),
        array('salesman_id','require','所属业务员必须选择！',1),
        array('province','require','省份必须选择！',1),
        array('city','require','城市必须选择！',1),
        array('area','require','区域必须选择！',1),
        array('address','require','地址必须输入！',1),
        array('address','1,60','地址长度1-60字',1,'length'),

        array('contact_name','require','客户联系人必须输入！',1),
        array('contact_name','1,15','客户联系人长度1-15字',1,'length'),
        array('contact_tel','require','联系电话必须输入！',1),
        array('contact_tel','1,20','联系人电话长度1-20字',1,'length'),
        array('status','require','客户状态必须选择！',1),

        array('have_car_number','require','拥有配送车辆数量必须输入！',1),
        array('have_salesman_number','require','终端业务员数量必须输入！',1),
        array('content','require','主营产品及品牌必须输入！',1),
        array('content','1,60','主营产品及品牌长度1-60字',1,'length'),
        array('year_total','require','年营业额必须输入！',1),
    );
    protected $_auto = array (
        array('create_time','time',1,'function'),
    );


    //支付回调  一对多
    public function notifySuccess($data,$is_app=0)
    {
        $order = $this->field('id,member_id,goods_id,order_type,buy_type,receiver')->where(array('order_sn'=>$data['out_trade_no'],'status'=>array('in',"2,10")))->find();
        if(!$order)
        {
            \Think\Log::record("未找到订单的记录","ERR");
            return false;
        }else
        {
            $this->startTrans();
            $goodsData = M('OrderGoods')->field('num,goods_id,attr_id')->where(array('order_id'=>$order['id']))->select();
            try{
                // 更改订单状态
                if($order['buy_type'] == 0)
                {
                    $orderarr['status'] = 7;
                }elseif($order['buy_type'] == 1)
                {
                    $orderarr['status'] = 100;
                    $orderarr['confirm_time'] = time();
                }else{
                    $orderarr['status'] = 6;
                }
                $orderarr['pay_time'] = time();
//                F('zs',$orderarr);
                $orderout = M('order')->where(array('order_sn'=>$data['out_trade_no']))->save($orderarr);




                //支付日志
                $row['member_id'] = $order['member_id'];
                $row['receiver'] = $order['receiver'];
                $row['order_id'] = $order['id'];
                $row['create_time'] = time();
                $row['type'] = 1;
                $backID = M('PayLog')->add($row);

                foreach ($goodsData as $item) {
                    // 1 减少库存
                    $Dec = M('SpecGroup')->where(array('id'=>$item['attr_id']))->setDec('stock',$item['num']);
                    // 2 增加销量
                    $IncS = M('Goods')->where(array('id'=>$item['goods_id']))->setInc('sales_no',$item['num']);
                    $IncZS = M('Goods')->where(array('id'=>$item['goods_id']))->setInc('zssales_no',$item['num']);
                }

                //如果库存为0下架商品
                $stock = M('SpecGroup')->where(array('goods_id'=>$order['goods_id']))->sum('stock');
                if($stock <= 0)
                {
                    M('Goods')->where(array('id'=>$order['goods_id']))->save(array('sale'=>0));
                }

                if($orderout && $Dec && $IncS && $IncZS && $backID)
                {
                    $this->commit();
                    return true;
                }
            }catch (\Exception $e){
                $this->rollback();
                $message="操作失败:".$e->getMessage();
                \Think\Log::write($message,'ERR');
                return false;
            }
        }
    }

    public function getGoodsList($order)
    {
        $OG = M('OrderGoods')->field('goods_id,c_num,e_num,attr_name,mpname,num')->where(array('order_no'=>$order['id']))->select();
//        var_dump($OG);
        $str = '';
        if($order['order_type'] == 1)  //门票
        {
            $str = '';
            foreach ($OG as $k=>$item) {

                $title = M('Ticket')->where(array('id'=>$item['goods_id']))->getField('title');

                if($item['c_num'] > 0)
                {
                    $str .= '<i class="w-block">';
                    $str .= $title.'—成人票 *2'.$item['c_num'];
                    $str .= '.</i>';
                }
                if($item['e_num'] > 0) {
                    $str .= '<i class="w-block">';
                    $str .= $title.'—儿童票 *'.$item['e_num'];
                    $str .= '.</i>';
                }

                if($item['num'] && (!$item['e_num'] && !$item['c_num']))
                {
                    $str .= '<i class="w-block">';
                    $str .= $title.$item['mpname'].' *'.$item['num'];
                    $str .= '</i>';
                }


            }

        }elseif ($order['order_type'] == 3 || $order['order_type'] == 4)
        {
            $arr = array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七',8=>'八',9=>'九');
            $str = '';
            foreach ($OG as $k=>$item)
            {
                $title = M('Ticket')->where(array('id'=>$item['goods_id']))->getField('title');

                $str .='<p><span>套 餐 '.$arr[$k+1].' ：</span><span>'.$title.$item['attr_name'].' *'.$item['num'].'</span></p>';
            }
        }
        return $str;
    }


    //订单 支付回调
    public function payBack($oid,$payInfo)
    {
        $orderData = $this->field('id,order_type,order_sn,pay_price,lxr_mobile,goods_id,coupon_id,member_id,jqmp_id,receiver,order_type')->where(array('id'=>$oid))->find();
        $row['pay_day'] = date('Y-m-d',time());
//       var_dump($payInfo);exit;
        $row['pay_time'] = time();
        $row['pay_type'] = $payInfo['pay_type'];

        if($orderData['coupon_id'] > 0)
        {
            $ars['is_use'] = 1;
            $ars['use_time'] = time();
            $ars['use_order'] = $orderData['order_sn'];
            M('CouponLog')->where(array('id'=>$orderData['coupon_id']))->save($ars);
        }
        $sendMsg = 0;
        if($orderData['order_type'] != 5)
        {
            $sendMsg = 1;
            $isgtx = '';
            $style = M('Ticket')->where(array('id'=>$orderData['goods_id']))->getField('style');
            if($style == 2)
            {
                $row['rede_code'] = rand(111111,999999);
            }else
            {
                $isgtx = 'tt';
            }
        }

        if($orderData['order_type'] == 1 || $orderData['order_type'] == 3 || $orderData['order_type'] == 4)
        {
            $row['status'] = 3;
        }elseif ($orderData['order_type'] == 5)   //商品
        {
            $row['status'] = 7;
        }
        $row['get_inte'] = $orderData['pay_price'];  //获得积分




        $this->startTrans();
        try{
            $res = $this->where(array('id'=>$oid))->save($row);


            $oGData = M('OrderGoods')->where(array('order_no'=>$oid))->field('attr_id,goods_id')->select();
            if($oGData)
            {
                if($orderData['order_type'] == 1 || $orderData['order_type'] == 3 || $orderData['order_type'] == 4)
                {
                    $ptype = array('in','1,2');
                }elseif ($orderData['order_type'] == 5)   //商品
                {
                    $ptype = array('eq','3');
                }

                $th_date = M('OrderGoods')->where(array('order_no'=>$orderData['id']))->getField('th_date');

                foreach ($oGData as $oGDatum)
                {
                    $Discount = array();


                    if($orderData['order_type'] == 5)
                    {
                        $Discount = M('Discount')->field('seckill_num,discount_no,discount_date')->where(
                            array('p_id'=>$oGDatum['goods_id'],'p_type'=>$ptype,'status'=>1
//                            'p_attr_id'=>$oGDatum['attr_id'],
//                            'limit_start_time'=>array('lt',time()),'limit_end_time'=>array('gt',time())
                            ))->find();
                    }else
                    {
                        $Discount = M('Discount')->field('seckill_num,discount_no,discount_date')->where(
                            array('p_id'=>$oGDatum['goods_id'],'p_type'=>$ptype,'status'=>1,'discount_date'=>$th_date
                            ))->find();
                    }


                    $num = array();
//                    $orderData['order_type'] ; 1门票订单  5商品订单       // 3自营活动订单4商家活动订单
                    if($orderData['order_type'] == 1 || $orderData['order_type'] == 5)
                    {
                        if($orderData['order_type'] == 5)
                        {
                            $num = M('OrderGoods')->alias('O')
                                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                                ->where(array(
                                        'O.goods_id'=>$oGDatum['goods_id'],
                                        'O.attr_id'=>$oGDatum['attr_id'],
                                        'O.th_no'=>$Discount['discount_no'],
                                        'p.status'=>1)
                                )
                                ->field('num')
                                ->select();

                        }else
                        {
                            $num = M('OrderGoods')->alias('O')
                                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                                ->where(array(
                                        'O.goods_id'=>$oGDatum['goods_id'],
                                        'O.attr_id'=>$oGDatum['attr_id'],
                                        'O.th_no'=>$Discount['discount_no'],
                                        'O.th_date'=>$th_date,
                                        'p.status'=>1)
                                )
                                ->field('num')
                                ->select();
                        }

                        $ns = 0;
                        if($num)
                        {
                            foreach ($num as $itema)
                            {
                                $ns += $itema['num'];
                            }
                        }

                        if($Discount['seckill_num'] <= $ns && $Discount['seckill_num'] > 0)
                        {
                            M('Discount')->where(array('discount_no'=>$Discount['discount_no']))->save(array('status'=>2));
                        }

                        //如果是商品，当所有特惠都卖完时。更新商品状态为非特惠    order_type  goods_id    is_discount 2不是
                        if($orderData['order_type'] == 5 && $oGDatum['goods_id'])
                        {
                            $did = M('Discount')->field('id')->where(array('p_type'=>3,'p_id'=>$oGDatum['goods_id'],'status'=>1))->find();
                            if(!$did && $oGDatum['goods_id'])
                            {
                                M('Goods')->where(array('id'=>$oGDatum['goods_id']))->save(array('is_discount'=>2));
                            }
                        }
                    }else  if($orderData['order_type'] == 3 || $orderData['order_type'] == 4)
                    {
                        $num = M('OrderGoods')->alias('O')
                            ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                            ->where(array(
//                                    'O.goods_id'=>$oGDatum['goods_id'],
//                                    'O.attr_id'=>$oGDatum['attr_id'],
                                    'O.th_date'=>$th_date,
                                    'O.th_no'=>$Discount['discount_no'],
                                    'p.status'=>1)
                            )
                            ->field('O.c_num,O.e_num')
                            ->select();
                        $c_num = 0;
                        $e_num = 0;
                        if($num)
                        {
                            foreach ($num as $itema)
                            {
                                $e_num += $itema['e_num'];
                                $c_num += $itema['c_num'];
                            }
                        }
                        if($Discount['seckill_num'] <= $e_num  && $Discount['seckill_num']  <= $c_num)
                        {
                            M('Discount')->where(array('discount_no'=>$Discount['discount_no'],'discount_date'=>$th_date))->save(array('status'=>2));
                        }

                    }
                }
            }

            //IntegralLog 表写数据
            $Inte['member_id'] = $orderData['member_id'];
            $Inte['change'] = $orderData['pay_price'];
            $Inte['change_type'] = 1;
            $Inte['change_status'] = 1;
            $memberInte = M('IntegralLog')->order('id desc')->where(array('member_id'=>$orderData['member_id']))->getField('after');
            $Inte['after'] = $memberInte + $orderData['pay_price'];
            $Inte['to_member_id'] = 0;
            $Inte['create_time'] = time();
            $Inte['oid'] = $oid;
            $InteId =  M('IntegralLog')->add($Inte);

            //添加积分
            M('Member')->where(array('id'=>$orderData['member_id']))->setInc('integral',$orderData['pay_price']);


            $this->commit();

            if($sendMsg == 1)
            {

                $title = M('Ticket')->where(array('id'=>$orderData['goods_id']))->getField('title');
                if($row['rede_code'] && $res)   //给用户发短信
                {
                    $res = $this->sendMsgTOMember($orderData['lxr_mobile'],$row['rede_code'],$title);
                }else  if($isgtx == 'tt')
                {
                    $res = $this->sendMsgTOMember($orderData['lxr_mobile'],'zs',$title);
                }
            }
        }catch (\Exception $e){
            $this->rollback();
            $message="操作失败:".$e->getMessage();
            \Think\Log::write($message,'ERR');
            return false;
        }






        //支付日志
//        $rowPayLog['member_id'] = $orderData['member_id'];
//        $rowPayLog['receiver'] = $orderData['receiver'];
//        $rowPayLog['order_id'] = $orderData['id'];
//        $rowPayLog['create_time'] = time();
//        $rowPayLog['type'] = 1;
//        $backID = M('PayLog')->add($rowPayLog);
        if($res)
        {
            return 1;
        }else
        {
            return false;
        }
    }


    public function sendMsgTOMember($phone,$code,$title)
    {
        if($code != 'zs')
        {
            $msg = send_sms(array('title'=>$title,'code'=>$code),$phone,'SMS_120125673');
//            $msg = send_sms('尊敬的用户，您预定的'.$title.'已生效，兑换码是'.$code.',请尽快使用!', $phone, 2);
        }else
        {
            $msg = send_sms(array('title'=>$title),$phone,'SMS_120125680');
//            $msg = send_sms('尊敬的用户，您预定的'.$title.'已生效,请尽快使用!', $phone, 2);
        }
        if ($msg)
        {
//                M('Verify')->where(array('phone' => $phone, 'code' => array('neq', $md5_code)))->save(array('issy' => 1));
            return 1;
        } else {
            return $msg;
        }
    }

//    public function sendCode1($mobile)
//    {
//        $ip = get_client_ip();
//        $start_time = strtotime(date('Y-m-d'));
//        $end_time = strtotime(date('Y-m-d') . ' 23:59:59');
//        $where = array();
//        $where['phone'] = $mobile;
//        $where['addtime'] = array(array('gt', $start_time), array('lt', $end_time));
//        $total = M('verify')->where($where)->count();
//        if ($total > 15) {
//            return '-15';
//        }
//        session('SMS_' . $mobile, time());
//        $code = rand(100000, 999999);
//        $md5_code = md5($code);
//        $ext_time = strtotime("+900 second");//有效期15分钟
//        $data = array(
//            'phone' => $mobile,
//            'code' => $md5_code,
//            'ext_time' => $ext_time,
//            'addtime' => time(),
//            'ip' => $ip,
//            'type' => 4
//        );
//        if (M('Verify')->add($data)) {
//            $msg = send_sms('[熊猫成长记]尊敬的用户，您预定的XXXXX已生效，兑换码是179541,请尽快使用!', $mobile, 2);
//            if ($msg === true) {
//                M('Verify')->where(array('phone' => $mobile, 'code' => array('neq', $md5_code)))->save(array('issy' => 1));
//                return 1;
//            } else {
//                return $msg;
//            }
//        }
//    }


    public function get_Temp($pay_no)
    {
        $order_id = M('Pay')->where(array('pay_sn'=>$pay_no))->getField('order_id');

        $data = $this->getOrderDataById($order_id,'back');
        if($data['order_type'] == 1 || $data['order_type'] == 3 || $data['order_type'] == 4)
        {
            $data['temp'] = 'new';
        }else
        {
            $data['temp'] = '';
        }

        return $data;
    }

    //获取订单下的商品总数量
    public function get_orders($pay_no)
    {
        $order_id = M('Pay')->where(array('pay_sn'=>$pay_no))->getField('order_id');
        return M('OrderGoods')->where(array('order_no'=>$order_id))->count();
    }

    //添加积分订单
    public function addInteOrder($id,$aid,$nums,$indentText,$membId,$addr)
    {
        //要消费的积分
        $SpecGroup = M('SpecGroup')->field('name,price')->where(array('id'=>$aid))->find();

        $inte = $SpecGroup['price'] * $nums;

        //会员积分
        $memberInte = M('IntegralLog')->order('id desc')->where(array('member_id'=>$membId))->getField('after');

        if($memberInte < $inte)
        {
            $this->error('积分不足');
        }

        $this->startTrans();
        try{
            //order  表写数据
            $row['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);

            $row['status'] = 7;
            $row['total_amount'] = $inte;
            $row['pay_price'] = $inte;
            $row['member_id'] = $membId;
            $row['order_type'] = 2;
            $row['create_time'] = time();
            $row['pay_day'] = date('Y-m-d',time());
            $row['pay_time'] = time();
            $row['pay_type'] = 4;
            $row['is_delete'] = 0;
            $row['goods_id'] = $id;
            $row['buyer_remarks'] = $indentText;



            $row['receiver'] = $addr['receiver'];
            $row['receiver_address'] = $addr['address'];
            $row['receiver_mobile'] = $addr['tel'];

            $backId = M('Order')->add($row);

            //orderGoods  表写数据
            $OrderG['order_no'] = $backId;
            $OrderG['goods_id'] = $id;
            $OrderG['goods_type'] = 3;
            $OrderG['num'] = $nums;
            $OrderG['isth'] = 0;
            $OrderG['total'] = $inte;
            $OrderG['prices'] = $SpecGroup['price'];
            $OrderG['mpname'] = M('Goods')->where(array('id'=>$id))->getField('goods_name');
            $OrderG['attr_id'] = $aid;
            $OrderG['attr_name'] = $SpecGroup['name'];
            $OgId = M('OrderGoods')->add($OrderG);

            //IntegralLog 表写数据
            $Inte['member_id'] = $membId;
            $Inte['change'] = $inte;
            $Inte['change_type'] = 12;
            $Inte['to_member_id'] = $id;
            $Inte['oid'] = $row['order_sn'];
            $Inte['after'] = $memberInte - $inte;
            $Inte['create_time'] = time();
            $InteId =  M('IntegralLog')->add($Inte);

            if($InteId && $OgId && $backId)
            {
                $this->commit();
                $array['order_sn'] = $row['order_sn'];
                $array['inte'] = $inte;
                return $array;
            }
        }catch (\Exception $e){
            $this->rollback();
            $message="操作失败:".$e->getMessage();
            \Think\Log::write($message,'ERR');
            return false;
        }
    }

    //商品从购物车下单
    public function AddOrderByCart($data,$mid,$addr)
    {
        $ids = $data['ids'];
        if($ids)
        {
            $allTotao = 0;
            $yh = 0;
            $idArray = explode(',',$ids);
            foreach ($idArray as $item)
            {
                $total = 0;
                $Discount = array();
                $Cart = array();
                $Cart = M('Cart')->field('goods_attr_id,goods_id,preferential_type,num')->where(array('id'=>$item))->find();

//                $Discount = M('Discount')->field('seckill_num,discount_no')
//                    ->where(array('preferential_type'=>$Cart['preferential_type'],'p_id'=>$Cart['goods_id'],'p_type'=>3
//                    ,'status'=>1,
//                        'limit_start_time'=>array('lt',time()),'limit_end_time'=>array('gt',time())
//                    ))->find();
                if(!$Cart)
                {
                    return -5;
                }
                $limit_going = M('Discount')
                    ->where(array(
                        'p_id'=>$Cart['goods_id'],'limit_start_time'=>array('lt',time()),
                        'limit_end_time'=>array('gt',time()),'p_type'=>3,
                        'seckill_num'=>array('gt',0),'status'=>1,
                    ))->field('seckill_num,discount_no')->find();

                if($limit_going)
                {
                    $Discount = $limit_going;
                }else {
                    $seckill_going = M('Discount')->alias('d')
                        ->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                        ->where(array('d.p_id' =>$Cart['goods_id'],
                            'd.preferential_type' => 1,
                            'd.status' => 1,'p_type'=>3,
                            'd.seckill_num' => array('gt', 0),
                        ))
                        ->field('d.seckill_num,discount_no')->find();
                    if ($seckill_going) {
                        $Discount = $seckill_going;
                    }
                }

                $SpecGroup = M('SpecGroup')->field('name,limit_price,seckill_price,price')->where(array('id'=>$Cart['goods_attr_id']))->find();

                if($Discount['seckill_num'] > 0)  //如果有
                {
                    if($Discount['seckill_num'] >= $Cart['num'])   //库存多余购买
                    {
                        if($Cart['preferential_type'] == '1')
                        {
                            $total = $SpecGroup['seckill_price'] * $Cart['num'];
                        }elseif ($Cart['preferential_type'] == '2')
                        {
                            $total = $SpecGroup['limit_price'] * $Cart['num'];
                        }
                    }else   //库存少余购买   其余按原价
                    {
                        $qNum = $Cart['num'] - $Discount['seckill_num'];
                        if($Cart['preferential_type'] == '1')
                        {
                            $total = ($SpecGroup['seckill_price'] * $Discount['seckill_num']) + ($SpecGroup['price'] * $qNum);
                        }elseif ($Cart['preferential_type'] == '2')
                        {
                            $total = ($SpecGroup['limit_price'] * $Discount['seckill_num'])+ ($SpecGroup['price'] * $qNum);
                        }
                    }
                }else   //如果没有秒杀按原价
                {
                    $total = $SpecGroup['price'] * $Cart['num'];
                }

                $allTotao = $allTotao + $total;

                //商品列表
                $goods = M('Goods')->field('id,freight_id,cover_pic,goods_name')->where(array('id'=>$Cart['goods_id']))->find();
                $row['goods_name'] = $goods['goods_name'];
                $row['cover_pic'] = $goods['cover_pic'];
                $row['id'] = $goods['id'];
                $row['total'] = $total;
                $row['num'] = $Cart['num'];
                $row['attr_name'] = $SpecGroup['name'];

//                $row['attr_name'] =  M('SpecGroup')->where(array('id'=>$Cart['goods_attr_id']))->getField('name');

                if($Cart['preferential_type'] == '1')
                {
                    $row['price'] = $SpecGroup['seckill_price'];
                }elseif ($Cart['preferential_type'] == '2')
                {
                    $row['price'] = $SpecGroup['limit_price'];
                }else
                {
                    $row['price'] = $SpecGroup['price'];
                }
                $row['attr_id'] = $Cart['goods_attr_id'];
                if($Discount)
                {
                    $row['th_type'] = $Cart['preferential_type'];
                    $row['th_no'] = $Discount['discount_no'];
                    $row['isth'] = 1;
                    $row['th_date'] = date('Y-m-d');
                }
                $rows[] = $row;

                //运费信息
                $goodsYhdata[$Cart['goods_id']][]['num'] = $Cart['num'];
                $goodsYhdata[$Cart['goods_id']][]['freight_id'] = $goods['freight_id'];

            }

            $newRow = array();
            foreach ($goodsYhdata as $ks=>$goodsYhdatum) {
                $nus = 0;
                foreach ($goodsYhdatum as $item) {
                    $nus += $item['num'];
                }
                $newRow[$ks]['num'] = $nus;
                $newRow[$ks]['freight_id'] = $item['freight_id'];
            }
            $yh = 0;
            foreach ($newRow as $item)
            {
                $yhall = D('MemberAddr')->getYhByAll($item['freight_id'],$data['addrid']);
                if($item['num'] <= $yhall['first'])
                {
                    $yh +=  $yhall['first_freight'];
                }elseif ($item['num'] > $yhall['first'])
                {
                    $ynum = $item['num'] - $yhall['first'];
                    $yh +=  $yhall['first_freight'];
                    if($ynum <= $yhall['next'])
                    {
                        $yh +=  $yhall['next_freight'];
                    }else
                    {
                        $yh += $yhall['next_freight']*(ceil($ynum/$yhall['next']));
                    }
                }
            }
            $datas['yh'] = $yh?$yh:0;



            $datas['receiver'] = $addr['receiver'];
            $datas['tel'] = $addr['tel'];
            $datas['address'] = $addr['address'];


            //优惠券

            if($data['yhjid'])
            {
                $coups['id'] = $data['yhjid'];
                $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coups)->find();
            }else
            {
                $coup['member_id'] = $mid;
                $coup['coupon_type'] = array('in','0,2');
                $coup['end_time'] = array('egt',time());
                $coup['is_use'] = 0;
                $coup['use_where'] = array('elt',$allTotao);
                $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coup)->order('use_where asc')->limit(1)->find();
            }
            $datas['yhj'] = 0;
            if($CouponLog)
            {
                $datas['yhjid'] = $CouponLog['id'];
                $datas['yhj'] = $CouponLog['price'];
            }
            $datas['list'] = $rows;
            $datas['total'] = $allTotao;
            $datas['alltotal'] = $allTotao + $datas['yh'] - $datas['yhj'];

            $totalP = $datas['alltotal'];
            $ordata['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);
            $ordata['order_type'] = 5;
            $ordata['status'] = 2;
            $ordata['total_amount'] = $allTotao;
            $ordata['pay_price'] = $totalP;
            $ordata['member_id'] = $mid;
            $ordata['freight'] = $datas['yh'];
            $ordata['create_time'] = time();
            $ordata['is_delete'] = 0;
            $ordata['goods_id'] = $data['ids'];

            $ordata['receiver'] = $addr['receiver'];
            $ordata['receiver_mobile'] = $addr['tel'];
            $ordata['receiver_address'] = $addr['address'];

            $ordata['ishx'] = 0;
            $ordata['coupon_dedu'] = $datas['yhj']?$datas['yhj']:0;
            $ordata['coupon_id'] = $datas['yhjid']?$datas['yhjid']:0;
            $ordata['jqmp_id'] = $data['ids'];
            $ordata['buyer_remarks'] = $data['buyer_remarks'];

            M('Order')->startTrans();
            $oid = M('Order')->add($ordata);
            if($oid)
            {
                try{
                    foreach ($datas['list'] as $items)
                    {
                        $OG['order_no'] = $oid;
                        $OG['goods_id'] = $items['id'];
                        $OG['goods_type'] = 3;
                        $OG['num'] = $items['num'];
                        $OG['isth'] = $items['isth']?$items['isth']:0;
                        $OG['total'] = $items['total'];

                        $OG['th_type'] = $items['preferential_type'];
                        $OG['th_no'] = $items['discount_no'];
                        $OG['isth'] = $items['isth'];
                        $OG['th_date'] = $items['th_date'];


                        $OG['prices'] = $items['price'];
                        $OG['mpname'] = $items['goods_name'];
                        $OG['th_type'] = $items['th_type'];
                        $OG['th_no'] = $items['th_no'];
                        $OG['ishx'] = 0;
                        $OG['attr_id'] = $items['attr_id'];
                        $OG['attr_name'] = $items['attr_name'];
                        $attrid = M('OrderGoods')->add($OG);
                    }
                    $paydata['pay_sn'] = $ordata['order_sn'];
                    $paydata['create_time'] = time();
                    $paydata['pay_price'] = $ordata['pay_price'];
                    $paydata['status'] = 0;
                    $paydata['order_type'] = 1;
                    $paydata['order_id'] = $oid;

                    $payid = M('Pay')->add($paydata);

                    if($CouponLog['id'])
                    {
                        M('CouponLog')->where(array('id'=>$CouponLog['id']
                        ))->save(array('is_use'=>1));
                    }
                    if($payid && $attrid && $oid)
                    {
                        M('Order')->commit();
                        M('Cart')->where(array('id'=>array('in',$data['ids'])))->delete();
                        return $ordata['order_sn'];
                    }
                }catch (\Exception $e){
                    M('Order')->rollback();
                    $message="操作失败:".$e->getMessage();
                    \Think\Log::write($message,'ERR');
                    return false;
                }
            }
        }
    }

    //商品下单
    public function addGoodsOrder($data,$mid,$addr)
    {

        $goods = M('Goods')->field('is_discount,freight_id')->where(array('id'=>$data['id']))->find();
        $attrData = M('SpecGroup')->where(array('goods_id'=>$data['id'],'id'=>$data['attr_id']))->find();

//        if($data['nums'] > $attrData['num'])
//        {
//            return '库存不足';
//        }

        //促销
        $yjsp = $aprice = $attrData['price'];

        $spec = array();
        if($goods['is_discount']==1)
        {
            $limit_going = M('Discount')
                ->where(array('p_id'=>$data['id'],'p_type'=>3,
                    'status'=>1,'preferential_type'=>2,
                    'limit_start_time'=>array('lt',time()),
                    'limit_end_time'=>array('gt',time())))
                ->field('id,seckill_num,discount_no,discount_price,preferential_type')->find();

            if($limit_going['discount_price'])
            {
                $spec['seckill_num'] = $limit_going['seckill_num'];
                $spec['is_going'] = 2;
                $spec['discount_no'] = $limit_going['discount_no'];
                $aprice = $attrData['limit_price'];
                $th_no = $limit_going['discount_no'];
            }else
            {
                $seckill_going = M('Discount')->alias('d')->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                    ->where(array('d.p_id'=>$data['id'],'d.p_type'=>3,
                        'preferential_type'=>1,'s.seckill_time'=>array('lt',time()),'s.end_time'=>array('gt',time()),'s.status'=>1))

                    ->field('d.discount_no,d.id,d.seckill_num,d.discount_price,d.preferential_type')->find();
                if($seckill_going['discount_price'])
                {
                    $spec['is_going'] = 1;
                    $spec['seckill_num'] = $seckill_going['seckill_num'];
                    $spec['discount_no'] = $seckill_going['discount_no'];
                    $th_no = $seckill_going['discount_no'];
                    $aprice = $attrData['seckill_price'];
                }else
                {
                    $spec['is_going'] = 0;
                }
            }

            //已卖数量
            $num = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->where(array('th_no'=>$spec['discount_no'],'p.status'=>1))
                ->sum('O.num');
            if($num >=  $spec['seckill_num'])
            {
                $spec['is_going'] = 0;
            }
        }else
        {
            $spec['is_going'] = 0;
        }



        if($spec['is_going'] > 0)
        {
            if($spec['is_going'] == 1)   //秒杀
            {
                $attrData['price'] = $attrData['seckill_price'];
            }elseif ($spec['is_going'] == 2)   //促销
            {
                $attrData['price'] = $attrData['limit_price'];
            }
        }

        $yhjdata = 0;
        if($data['yhjid'])
        {
            $yhjdata = M('CouponLog')->where(array('id'=>$data['yhjid']))->getField('price');
        }

        $yh = 0;
//        $yh = $this->getYhByFidAndAddrid($goods['freight_id'],$data['addrid']);

        //运费    getYhByAll()          first  first_freight  next   next_freight
        $yhall = D('MemberAddr')->getYhByAll($goods['freight_id'],$data['addrid']);
        if($data['nums'] <= $yhall['first'])
        {
            $yh +=  $yhall['first_freight'];
        }elseif ($data['nums'] > $yhall['first'])
        {
            $ynum = $data['nums'] - $yhall['first'];
            $yh +=  $yhall['first_freight'];
            if($ynum <= $yhall['next'])
            {
                $yh +=  $yhall['next_freight'];
            }else
            {
                $yh += $yhall['next_freight']*(ceil($ynum/$yhall['next']));
            }
        }


        if($spec['is_going'] > 0)
        {
            $num = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->where(array('th_no'=>$spec['discount_no'],'th_date'=>date('Y-m-d'),'p.status'=>1))
                ->sum('O.num');
            $eKm = $spec['seckill_num'] - $num;



            if($eKm >= $data['nums'])
            {
                $etatal = $aprice * $data['nums'];
            }else if($eKm < $data['nums'])
            {
                $OKN = $data['nums'] - $eKm;
                $etatal = ($aprice * $eKm) + ($yjsp * $OKN);
            }
        }else
        {
            $etatal = $attrData['price'] * $data['nums'];
        }

        $totalP = $etatal - $yhjdata +$yh;

        $ordata['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);
        $ordata['order_type'] = 5;
        $ordata['status'] = 2;
        $ordata['total_amount'] = $etatal;
        $ordata['pay_price'] = $totalP;
        $ordata['member_id'] = $mid;
        $ordata['create_time'] = time();
        $ordata['is_delete'] = 0;
        $ordata['goods_id'] = $data['id'];

        $ordata['freight'] = $yh;

        $ordata['receiver'] = $addr['receiver'];
        $ordata['receiver_mobile'] = $addr['tel'];
        $ordata['receiver_address'] = $addr['address'];

        $ordata['ishx'] = 0;
        $ordata['coupon_dedu'] = $yhjdata;
        $ordata['coupon_id'] = $data['yhjid']?$data['yhjid']:0;

        $ordata['jqmp_id'] = $data['id'];
        $ordata['buyer_remarks'] = $data['buyer_remarks'];

        M('Order')->startTrans();
        $oid = M('Order')->add($ordata);
        if($oid)
        {
            try{
                $OG['order_no'] = $oid;
                $OG['goods_id'] = $data['id'];
                $OG['goods_type'] = 3;

                $OG['num'] = $data['nums'];
                $OG['isth'] = $data['isth']?$data['isth']:0;
                $OG['total'] = $totalP;

                $OG['prices'] = $attrData['price'];
                $OG['mpname'] = $attrData['name'];

                if($spec['is_going'] > 0)
                {
                    $OG['th_type'] = $spec['is_going'];
                    $OG['th_no'] = $th_no;
                    $OG['isth'] = 1;
                    $OG['th_date'] = date('Y-m-d');
                }
                $OG['ishx'] = 0;
                $OG['attr_id'] = $data['attr_id'];
                $OG['attr_name'] = $attrData['name'];
                $attrid = M('OrderGoods')->add($OG);

                $paydata['pay_sn'] = $ordata['order_sn'];
                $paydata['create_time'] = time();
                $paydata['pay_price'] = $ordata['pay_price'];
                $paydata['status'] = 0;
                $paydata['order_type'] = 1;
                $paydata['order_id'] = $oid;

                $payid = M('Pay')->add($paydata);
                if($payid && $attrid && $oid)
                {
                    M('Order')->commit();
                    return $ordata['order_sn'];
                }
            }catch (\Exception $e){
                M('Order')->rollback();
                $message="操作失败:".$e->getMessage();
                \Think\Log::write($message,'ERR');
                return false;
            }
        }
    }

    /**  获取运费
     * @param $fid
     * @param $addrid
     * @return mixed
     */
    public function getYhByFidAndAddrid($fid,$addrid)
    {
        $freight_id =  $fid;
        $province = M('MemberAddr')->where(array('id'=>$addrid))->getField('province');
        $where['type'] = $freight_id;
        $where['_string'] = "FIND_IN_SET($province,province)";
        $Frdata = M('FreightExtend')->field('first_freight')->where($where)->find();
        if($Frdata)
        {
            return $Frdata['first_freight'];
        }else
        {
            $Frdata = M('FreightExtend')->field('first_freight')->where(array('type'=>$freight_id,'province'=>array('eq','')))->find();
            return $Frdata['first_freight'];
        }
    }

    public function getAllMpPrice($data)
    {
        $ywdate = $data['ywdate'];
        $yhjid = $data['yhjid'];
        $c_num = $data['c_num'];
        $e_num = $data['e_num'];
        $gid = $data['gid'];
        $attr_id = $data['attr_id'];
        if($yhjid)   //优惠券
        {
            $CouponPrice = M('CouponLog')->where(array('id'=>$yhjid))->getField('price');
            $CouponPrice = $CouponPrice > 0 ? $CouponPrice : 0;
        }

        $SpecGroup = M('SpecGroup')->field('c_price,e_price')->where(array('id'=>$attr_id))->find();

        if($ywdate)
        {

            $thNums = 0;
            $thNums = M('Discount')->where(array('p_id'=>$gid,'p_attr_id'=>$attr_id,'discount_date'=>$ywdate))
                ->getField('seckill_num');



//            $Discount = M('Discount')->field('id,discount_no,c_money,e_money')
//                ->where(array(
//                    'p_id'=>$gid,
//                    'p_attr_id'=>$attr_id,
//                    'discount_date'=>$ywdate?$ywdate:'',
//                    'limit_end_time'=>array('gt',time()),
//                    'limit_start_time'=>array('lt',time()),
//                    'seckill_num'=>array('gt',0))
//            )->find();

            $limit_going = M('Discount')
                ->where(array(
                    'p_attr_id'=>$attr_id,'limit_start_time'=>array('lt',time()),
                    'limit_end_time'=>array('gt',time()),
                    'seckill_num'=>array('gt',0),'status'=>1,
                    'discount_date'=>$ywdate?$ywdate:'',
                ))->field('id,c_money,e_money,discount_no,seckill_num')->find();
            $rows = array();
            if($limit_going)
            {
                $Discount = $limit_going;
            }else {
                $seckill_going = M('Discount')->alias('d')
                    ->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                    ->where(array('d.p_attr_id' =>$attr_id,
                        'd.preferential_type' => 1,
                        's.seckill_time' => array('lt', time()),
                        's.end_time' => array('gt', time()),
                        'd.seckill_num' => array('gt', 0),
                        'd.status' => 1,
                        'd.discount_date'=>$ywdate?$ywdate:'',
                    ))
                    ->field('d.id,c_money,e_money,d.discount_date,d.discount_no,d.seckill_num')->find();
                if ($seckill_going) {
                    $Discount = $seckill_going;
                }
            }


            if($Discount['c_money'] > 0 || $Discount['e_money'] > 0)
            {
//                echo $ywdate; exit;
//                $e_num_da = M('OrderGoods')->where(array('goods_id'=>$gid,'attr_id'=>$attr_id,'th_date'=>$ywdate))->sum('e_num');
//                $c_num_da = M('OrderGoods')->where(array('goods_id'=>$gid,'attr_id'=>$attr_id,'th_date'=>$ywdate))->sum('c_num');

                $e_num_da = M('OrderGoods')->alias('O')
                    ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                    ->where(array('O.th_no'=>$Discount['discount_no'],'O.th_date'=>$ywdate,'p.status'=>1))
                    ->sum('O.e_num');


                $c_num_da = M('OrderGoods')->alias('O')
                    ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                    ->where(array('th_no'=>$Discount['discount_no'],'th_date'=>$ywdate,'p.status'=>1))
                    ->sum('O.c_num');

                $eKm = $thNums - $e_num_da;

                $eKm = $eKm > 0 ? $eKm : 0;
                $cKm = $thNums- $c_num_da;
                $cKm = $cKm > 0 ? $cKm : 0;
                //儿童
                $etatal = $ctatal = 0;
                if($eKm >= $e_num)
                {
                    $etatal = $Discount['e_money'] * $e_num;

                }else if($e_num > $eKm)
                {
                    $ZcENum = $e_num - $eKm;
                    $etatal = ($Discount['e_money'] * $eKm) + ($SpecGroup['e_price'] * $ZcENum);
                }
                //成人
                if($cKm >= $c_num)
                {
                    $ctatal = $Discount['c_money'] * $c_num;
                }else if($c_num > $cKm)
                {
                    $ZcENum = $c_num - $cKm;
                    $ctatal = ($Discount['c_money'] * $cKm) + ($SpecGroup['c_price'] * $ZcENum);
                }
                $total = ($etatal * 100) + ($ctatal * 100);
                $total = $total / 100;
                if($cKm > 0)
                {
                    $csj = $Discount['c_money'];
                }else
                {
                    $csj =  $SpecGroup['c_price'];
                }
                if($eKm > 0)
                {
                    $etj = $Discount['e_money'];
                }else
                {
                    $etj = $SpecGroup['e_price'];
                }
            }else
            {



                $csj = $SpecGroup['c_price'];
                $etj = $SpecGroup['e_price'];
//                $total = $SpecGroup['e_price'] * $e_num + $SpecGroup['c_price'] * $c_num  - $CouponPrice;
                $total = ($SpecGroup['e_price']) * 100 * $e_num + ($SpecGroup['c_price'] * 100) * $c_num;
                $total = $total / 100;
            }


            $coup['member_id'] = $data['member_id'];
            $coup['coupon_type'] = array('in', '0,2');
            $coup['end_time'] = array('egt', time());
            $coup['is_use'] = 0;
            $coup['use_where'] = array('elt', $total);
            //优惠券
            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coup)->order('use_where asc')->limit(1)->find();

//            echo $total;
            return array('total'=>$total,'c'=>$csj,'e'=>$etj,'yhj'=>$CouponLog);
        }

    }

    public function addActiveOrder($data,$mid)
    {
        if($data['seller_id'] > 0)
        {
            $ordata['order_type'] = 4; //商家活动
            $ordata['business_owned_id'] = $data['seller_id'];
        }else{
            $ordata['order_type'] = 3;//自营活动
        }
        $data['nums'] = $data["c_num"] + $data["e_num"];  //数量

        $attrData = M('SpecGroup')->field('name,c_price,e_price,limit_price,effective_time,num')
            ->where(array('goods_id'=>$data['gid'],'id'=>$data['attr_id'],'p_type'=>2))->find();
        //检测是否促销或秒杀
        $limit_going = M('Discount')->where(array('p_attr_id'=>$data['attr_id'],'limit_start_time'=>array('lt',time()),
            'discount_date'=>$data['cx_time'],
            'limit_end_time'=>array('gt',time()),'seckill_num'=>array('gt',0)
        ))->field('discount_no,preferential_type,seckill_num,discount_date,c_money,e_money')->find();

        if($limit_going){
            $spec['c_money'] = $limit_going['c_money'];
            $spec['e_money'] = $limit_going['e_money'];
            $spec['seckill_num'] = $limit_going['seckill_num'];
            $spec['preferential_type'] = $limit_going['preferential_type'];
            $spec['discount_no'] = $limit_going['discount_no'];
            $spec['discount_date'] = $limit_going['discount_date'];
            $spec['is_going'] = 2;
        }else{
            $seckill_going = M('Discount')->alias('d')
                ->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                ->where(array('d.p_attr_id'=>$data['attr_id'],
                    'd.preferential_type'=>1,
                    'd.discount_date'=>$data['cx_time'],
                    's.seckill_time'=>array('lt',time()),'s.end_time'=>array('gt',time()),'d.seckill_num'=>array('gt',0)))
                ->field('d.discount_no,d.preferential_type,d.seckill_num,d.discount_date,d.c_money,d.e_money')->find();
            if($seckill_going)
            {
                $spec['c_money'] = $seckill_going['c_money'];
                $spec['e_money'] = $seckill_going['e_money'];

                $spec['preferential_type'] = $seckill_going['preferential_type'];
                $spec['discount_no'] = $seckill_going['discount_no'];
                $spec['seckill_num'] = $seckill_going['seckill_num'];
                $spec['discount_date'] = $seckill_going['discount_date'];
                $spec['is_going'] = 1;
            }else{
                $spec['is_going'] = 0;
            }
        }

        $attr = array();
        $etatal = 0;
        $attr['e_price'] = $spec['e_money'] ? $spec['e_money'] : $attrData['e_price'];
        $attr['c_price'] = $spec['c_money'] ? $spec['c_money'] : $attrData['c_price'];


        //算特惠余下的数量
        if($spec['is_going'] > 0)
        {
            $e_num = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->where(array('th_no'=>$spec['discount_no'],'th_date'=>$data['cx_time'],'p.status'=>1))->sum('O.e_num');
            $c_num = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->where(array('th_no'=>$spec['discount_no'],'th_date'=>$data['cx_time'],'p.status'=>1))->sum('O.c_num');


            $eKm = 0;
            $cKm = 0;
//            $data["c_num"] + $data["e_num"]
            if($attrData['num'] <($e_num+$data["e_num"]) || $attrData['num'] <($c_num+$data["c_num"]))
            {
                return -100;
            }

            $eKm = $spec['seckill_num'] - $e_num;
            $eKm = $eKm > 0 ? $eKm : 0;
            $cKm = $spec['seckill_num'] - $c_num;
            $cKm = $cKm > 0 ? $cKm : 0;
            //儿童

            if($eKm >= $data['e_num'])
            {
                $etatal = $attr['e_price'] * $data["e_num"];
            }else if($data['e_num'] > $eKm)
            {
                $ZcENum = $data['e_num'] - $eKm;
                $etatal = ($attr['e_price'] * $eKm) + ($attrData['e_price'] * $ZcENum);

                if($eKm > 0)
                {
                    $dep = strval($attr['e_price']).'X'.strval($eKm).'|'.strval($attrData['e_price']).'X'.strval($ZcENum);
                }
            }
            //成人
            if($cKm >= $data['c_num'])
            {
                $ctatal = $attr['c_price'] * $data["c_num"];
            }else if($data['c_num'] > $cKm)
            {
                $ZcENum = 0;
                $ZcENum = $data['c_num'] - $cKm;
                $ctatal = ($attr['c_price'] * $cKm) + ($attrData['c_price'] * $ZcENum);
                if($cKm > 0)
                {
                    $dcp = strval($attr['c_price']).'X'.strval($cKm).'|'.strval($attrData['c_price']).'X'.strval($ZcENum);
                }
            }
            $attr['price'] = $attr['total'] = $etatal + $ctatal;
        }else
        {

            if($data['cx_time'])
            {
            $e_num_a = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->join('lEFT JOIN __ORDER__ ODE on ODE.id=O.order_no')
                ->where(array('O.attr_id'=>$data['attr_id'],'O.goods_id'=>$data['gid'],
                    'ODE.cx_time'=>strtotime($data['cx_time']), 'p.status'=>1))
                ->sum('O.e_num');

            $c_num_a = M('OrderGoods')->alias('O')
                ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                ->join('lEFT JOIN __ORDER__ ODE on ODE.id=O.order_no')
                ->where(array('O.attr_id'=>$data['attr_id'],'O.goods_id'=>$data['gid'],
                    'ODE.cx_time'=>strtotime($data['cx_time']),'p.status'=>1))->sum('O.c_num');
            if($attrData['num'] <($e_num_a+$data["e_num"]) || $attrData['num'] <($c_num_a+$data["c_num"]))
            {
                return -100;
            }
            }
            $attr['price'] = $attr['total'] = ($attr['c_price'] * $data['c_num']) + ($attr['e_price'] * $data['e_num']);
        }


        $yhjid = $data['yhjid'];
        if($yhjid)   //优惠券
        {
            $CouponPrice = M('CouponLog')->where(array('id'=>$yhjid))->field('price')->find();
            $CouponPrice = $CouponPrice['price'] > 0 ? $CouponPrice['price'] : 0;
        }


        $ordata['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);
        $ordata['status'] = 2;
        $totalP = $attr['price'] - $CouponPrice;
        $ordata['total_amount'] = $attr['price'];
        $ordata['pay_price'] = $totalP;

        $taken_percentage = M('Business')->where(array('uid'=>$data['seller_id']))->getField('taken_percentage');
        $ordata['ptdk'] = ($totalP * $taken_percentage)/100;

        $TicketA = M('TicketActive')->field('ini_obj,a_type,theme_type,seller_id,destination_province,destination_city')
            ->where(array('ticket_id'=>$data['gid']))->find();

        if($TicketA)
        {
            $ordata['business_owned_id'] = $TicketA['seller_id'] ? $TicketA['seller_id'] : 0;
            $ordata['addr1'] = $TicketA['destination_province'];
            $ordata['addr2'] = $TicketA['destination_city'];
            $ordata['hdlx'] = $TicketA['a_type'];
            $ordata['hdlx2'] = $TicketA['theme_type'];
        }


        $ordata['member_id'] = $mid;
        $ordata['create_time'] = time();
        $ordata['lxr_name'] = $data['lxr'];
        $ordata['lxr_mobile'] = $data['phone'];
        $ordata['lxr_sfz'] = $data['sfz'];

        $ordata['is_delete'] = 0;

        $ordata['goods_id'] = $data['gid'];
        $ordata['ishx'] = 0;  //m/Order/buy_now/goods_attr_id/69/goods_num/1

        $ordata['coupon_dedu'] = $CouponPrice;
        $ordata['coupon_id'] =  $yhjid ? $yhjid : 0;



        $cxrname = explode(',',$data['cxrname']);
        $cxrsfz = explode(',',$data['cxrsfz']);
        $newrow = array();
        foreach ($cxrname as $k=>$item) {
            if($item)
            {
                $newrow[] = $item.'&'.$cxrsfz[$k];
            }
        }
        $ordata['cxr_lists'] = implode('|',$newrow);

//        $ordata['cxr_lists'] = $data['cxlxr'].'&'.''.$data['cxsfz'];



        $ordata['jqmp_id'] = $data['gid'];
        $ordata['buyer_remarks'] = $data['buyer_remarks'];


        $style = M('Ticket')->where(array('id'=>$data['gid']))->getField('style');
        if($style == 1)
        {
            $ordata['cx_time'] = strtotime($data['cx_time']);
        }else if($style == 2)
        {
            $ordata['yxq_time'] = $attrData['effective_time']?$attrData['effective_time']:strtotime($data['cx_time']);
        }

        $ordata['mp_price'] = $totalP;


        M('Order')->startTrans();
        $oid = M('Order')->add($ordata);
        if($oid)
        {

            try{
                $OG['order_no'] = $oid;
                $OG['goods_id'] = $data['gid'];
                $OG['goods_type'] = 2;
                $OG['c_num'] = $data['c_num'];
                $OG['e_num'] = $data['e_num'];
                $OG['dcp'] = $dcp?$dcp:'';
                $OG['dep'] = $dep?$dep:'';
                $OG['c_price'] = $spec['c_money']?$spec['c_money']:$attr['c_price'];
                $OG['e_price'] = $spec['e_money']?$spec['e_money']:$attr['e_price'];
                $OG['c_price'] = floatval($OG['c_price']);
                $OG['e_price'] = floatval($OG['e_price']);
                $OG['num'] = $data['c_num'] + $data['e_num'];
                $OG['isth'] = 0;
                if($spec['is_going'] > 0)
                {
                    $OG['th_type'] = $spec['preferential_type'];
                    $OG['th_no'] = $spec['discount_no'];
                    $OG['isth'] = 1;
                    $OG['th_date'] = $spec['discount_date'];
                }
                $OG['total'] = $totalP;
                $OG['prices'] = $attrData['limit_price'];
                $OG['mpname'] = $attrData['name'];
                $OG['yxq_time'] = $attrData['effective_time'];
                $OG['ishx'] = 0;
                $OG['attr_id'] = $data['attr_id'];
                $OG['attr_name'] = $attrData['name'];
                $attrid = M('OrderGoods')->add($OG);


                $paydata['pay_sn'] = $ordata['order_sn'];
                $paydata['create_time'] = time();
                $paydata['pay_price'] = $ordata['pay_price'];
                $paydata['status'] = 0;
                $paydata['order_type'] = 1;
                $paydata['order_id'] = $oid;

                $payid = M('Pay')->add($paydata);
                if($payid && $attrid && $oid)
                {
                    M('Order')->commit();
                    return $ordata['order_sn'];
                }

            }catch (\Exception $e){
                M('Order')->rollback();
                $message="操作失败:".$e->getMessage();
                \Think\Log::write($message,'ERR');
                return false;
            }
        }
    }

    //修改购买总金额
    public function modifyTotal($data,$mid)
    {
        $attrData = M('SpecGroup')->field('id,name,price,limit_price,effective_time,num')
            ->where(array('goods_id'=>$data['gid'],'id'=>array('in',$data['attr_id']),'p_type'=>1))->select();
        $num_all_array = explode(',',$data['numsall']);

        if($attrData)
        {
            $specs = array();
            $thprices = 0;
            foreach ($attrData as $k=>$attrDatum)
            {

                //检测是否促销或秒杀
                $spec = array();   //discount_no
                if($data['ywdate'])
                {
                    $limit_going = M('Discount')
                        ->where(array('seckill_num'=>array('gt',0),
                            'p_attr_id'=>$attrDatum['id'],'p_id'=>$data['gid'],
                            'p_type'=>1,'limit_start_time'=>array('lt',time()),
                            'status'=>1,
                            'discount_date'=>$data['ywdate']?$data['ywdate']:'',
                            'limit_end_time'=>array('gt',time())))
                        ->field('preferential_type,seckill_num,discount_no,discount_price')->find();
                }else
                {
                    $limit_going = M('Discount')
                        ->where(array('seckill_num'=>array('gt',0),
                            'p_attr_id'=>$attrDatum['id'],'p_id'=>$data['gid'],
                            'p_type'=>1,'limit_start_time'=>array('lt',time()),
                            'status'=>1,
                            'limit_end_time'=>array('gt',time())))
                        ->field('preferential_type,seckill_num,discount_no,discount_price')->find();
                }

                if($limit_going['discount_price']){
                    $spec['discount_price'] = $limit_going['discount_price'];
                    $spec['seckill_num'] = $limit_going['seckill_num'];
                    $spec['preferential_type'] = $limit_going['preferential_type'];
                    $spec['discount_no'] = $limit_going['discount_no'];
                    $spec['is_going'] = 2;
                }else{
                    if($data['ywdate']) {
                        $seckill_going = M('Discount')->alias('d')
                            ->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                            ->where(array('d.p_attr_id' => $attrDatum['id'], 'd.preferential_type' => 1, 'd.seckill_num' => array('gt', 0),
                                's.seckill_time' => array('lt', time()),
                                'd.discount_date' => $data['ywdate'] ? $data['ywdate'] : '',
                                'd.status' => 1,
                                's.end_time' => array('gt', time())))
                            ->field('d.preferential_type,d.seckill_num,d.discount_no,d.discount_price')->find();
                    }else
                    {
                        $seckill_going = M('Discount')->alias('d')
                            ->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                            ->where(array('d.p_attr_id' => $attrDatum['id'], 'd.preferential_type' => 1, 'd.seckill_num' => array('gt', 0),
                                's.seckill_time' => array('lt', time()),
                                'd.status' => 1,
                                's.end_time' => array('gt', time())))
                            ->field('d.preferential_type,d.seckill_num,d.discount_no,d.discount_price')->find();
                    }
                    if($seckill_going['discount_price'])
                    {
                        $spec['discount_price'] = $seckill_going['discount_price'];
                        $spec['discount_no'] = $seckill_going['discount_no'];
                        $spec['preferential_type'] = $seckill_going['preferential_type'];
                        $spec['seckill_num'] = $seckill_going['seckill_num'];
                        $spec['is_going'] = 1;
                    }else
                    {
                        $spec['is_going'] = 0;
                    }
                }

                $spec['name'] = $attrDatum['name'];
                $spec['effective_time'] = $attrDatum['effective_time'];

                //算特惠余下的数量
                if($spec['is_going'] > 0)
                {
                    $num = M('OrderGoods')->alias('OG')
                        ->join('lEFT JOIN __PAY__ p on p.order_id=OG.order_no')
                        ->where(array('OG.th_no'=>$spec['discount_no'],
                            'p.status'=>1
                        ))
                        ->sum('num');
                    $eKm = $spec['seckill_num'] - $num;  //可以购买的数量
                    if($eKm >= $num_all_array[$k])
                    {

                        $etatal = $spec['discount_price'] * $num_all_array[$k];
                    }else if($eKm < $num_all_array[$k])
                    {
                        $OKN = $num_all_array[$k] - $eKm;   //原价买数量
                        $etatal = $spec['discount_price'] * $eKm + $OKN * $attrDatum['price'];
                    }
                    $thprice = $spec['discount_price'];
                    $thpri = $etatal;
                }else
                {
                    $thprice = $attrDatum['price'];
                    $thpri = $thprice * $num_all_array[$k];
                }

                $thprices = $thprices + $thpri;
                $spec['thprice'] = $thprice;
                $specs[] = $spec;
            }
        }
        //优惠券
        if($data['yhjid'])
        {
            $coups['id'] = $data['yhjid'];
            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coups)->find();
        }else
        {
            $coup['member_id'] = $mid;
            $coup['coupon_type'] = array('in','0,2');
            $coup['end_time'] = array('egt',time());
            $coup['is_use'] = 0;
            $coup['use_where'] = array('elt',$thprices);
            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coup)->order('use_where asc')->limit(1)->find();
        }
        if($CouponLog)
        {
            $thprices = $thprices - $CouponLog['price'];
        }

        return array('price'=>$thprices,'yhjlist'=>$CouponLog?$CouponLog:array());
    }



    public function addMpOrder($data,$mid)
    {
        $attrData = M('SpecGroup')->field('id,name,price,limit_price,effective_time,num')
            ->where(array('goods_id'=>$data['gid'],'id'=>array('in',$data['attr_id']),'p_type'=>1))->select();
        $num_all_array = explode(',',$data['numsall']);
        if($attrData)
        {
            $specs = array();
            $thprices = 0;
            foreach ($attrData as $k=>$attrDatum)
            {
                $thprice = $attrDatum['price'];
                //检测是否促销或秒杀
                $spec = array();   //discount_no
                if($data['ywdate'])
                {
                    $limit_going = M('Discount')
                        ->where(array('seckill_num'=>array('gt',0),
                            'p_attr_id'=>$attrDatum['id'],'p_id'=>$data['gid'],
                            'p_type'=>1,'limit_start_time'=>array('lt',time()),
                            'status'=>1,
                            'discount_date'=>$data['ywdate']?$data['ywdate']:'',
                            'limit_end_time'=>array('gt',time())))
                        ->field('preferential_type,seckill_num,discount_date,discount_no,discount_price')->find();
                }else
                {
                    $limit_going = M('Discount')
                        ->where(array('seckill_num'=>array('gt',0),
                            'p_attr_id'=>$attrDatum['id'],'p_id'=>$data['gid'],
                            'p_type'=>1,'limit_start_time'=>array('lt',time()),
                            'status'=>1,
                            'limit_end_time'=>array('gt',time())))
                        ->field('preferential_type,seckill_num,discount_date,discount_no,discount_price')->find();
                }

                if($limit_going['discount_price']){
                    $spec['discount_price'] = $limit_going['discount_price'];
                    $spec['seckill_num'] = $limit_going['seckill_num'];
                    $spec['preferential_type'] = $limit_going['preferential_type'];
                    $spec['discount_no'] = $limit_going['discount_no'];
                    $spec['discount_date'] = $limit_going['discount_date'];
                    $spec['is_going'] = 2;

                }else{
                    if($data['ywdate']) {
                        $seckill_going = M('Discount')->alias('d')->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                            ->where(array('d.p_attr_id' => $attrDatum['id'], 'd.preferential_type' => 1, 'd.seckill_num' => array('gt', 0),
                                'd.discount_date' => $data['ywdate'] ? $data['ywdate'] : '', 'd.status' => 1,
                                's.seckill_time' => array('lt', time()), 's.end_time' => array('gt', time())))
                            ->field('d.preferential_type,d.seckill_num,d.discount_date,d.discount_no,d.discount_price')->find();
                    }else
                    {
                        $seckill_going = M('Discount')->alias('d')->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
                            ->where(array('d.p_attr_id' => $attrDatum['id'], 'd.preferential_type' => 1, 'd.seckill_num' => array('gt', 0),
                                'd.status' => 1,
                                's.seckill_time' => array('lt', time()), 's.end_time' => array('gt', time())))
                            ->field('d.preferential_type,d.seckill_num,d.discount_date,d.discount_no,d.discount_price')->find();
                    }
                    if($seckill_going['discount_price'])
                    {

                        $spec['discount_price'] = $seckill_going['discount_price'];
                        $spec['discount_no'] = $seckill_going['discount_no'];
                        $spec['discount_date'] = $seckill_going['discount_date'];
                        $spec['preferential_type'] = $seckill_going['preferential_type'];
                        $spec['seckill_num'] = $seckill_going['seckill_num'];
                        $spec['is_going'] = 1;
                    }else
                    {
                        $spec['is_going'] = 0;
                    }
                }
                $spec['name'] = $attrDatum['name'];
                $spec['effective_time'] = $attrDatum['effective_time'];
                //算特惠余下的数量



                if($spec['is_going'] > 0)
                {
                    $num = M('OrderGoods')->alias('OG')
                        ->join('lEFT JOIN __PAY__ p on p.order_id=OG.order_no')
                        ->where(array('OG.th_no'=>$spec['discount_no'],'p.status'=>1
                        ))
                        ->sum('num');
                    $eKm = $spec['seckill_num'] - $num;  //可以购买的数量

                    if($attrDatum['num'] < ($num+$num_all_array[$k]))
                    {
                        return -100;
                    }

//                           if($eKm >= $data['nums'])
//                           {
//                               $etatal = $spec['discount_price'] * $data['nums'];
//                           }else if($eKm < $data['nums'])
//                           {
//                               $OKN = $data['nums'] - $eKm;   //原价买数量
//                               $etatal = $spec['discount_price'] * $eKm + $OKN * $attrDatum['price'];
//                           }


                    if($eKm >= $num_all_array[$k])
                    {
                        $etatal = $spec['discount_price'] * $num_all_array[$k];
                    }else if($eKm < $num_all_array[$k])
                    {
                        $OKN =  $num_all_array[$k] - $eKm;   //原价买数量
                        $etatal = $spec['discount_price'] * $eKm + $OKN * $attrDatum['price'];
                    }
                    $thprice = $spec['discount_price'];
                    $thpri = $etatal;
                }else
                {
                    $thprice = $attrDatum['price'];
                    $thpri = $thprice * $num_all_array[$k];
//                    goods_id
//                    attr_id


//                    $numAll = M('OrderGoods')->alias('OG')
//                        ->join('lEFT JOIN __PAY__ p on p.order_id=OG.order_no')
//                        ->where(array('OG.attr_id'=>$attrDatum['id'],'OG.goods_id'=>$data['gid'],'p.status'=>1
//                        ))
//                        ->sum('num');

                    if($data['cx_time'])
                    {
                    $c_num_a = M('OrderGoods')->alias('O')
                        ->join('lEFT JOIN __PAY__ p on p.order_id=O.order_no')
                        ->join('lEFT JOIN __ORDER__ ODE on ODE.id=O.order_no')
                        ->where(array('O.attr_id'=>$data['attr_id'],'O.goods_id'=>$data['gid'],
                            'ODE.cx_time'=>strtotime($data['cx_time']),'p.status'=>1))->sum('O.num');
                    if($attrDatum['num'] < ($c_num_a+$num_all_array[$k]))
                    {
                        return -100;
                    }
                    }
                }
                $thprices = $thprices + $thpri;
                $spec['thprice'] = $thprice;
                $specs[] = $spec;
            }
        }

        //优惠券
        if($data['yhjid'])
        {
            $coups['id'] = $data['yhjid'];
            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coups)->find();
        }else
        {
            $coup['member_id'] = $mid;
            $coup['coupon_type'] = array('in','0,2');
            $coup['end_time'] = array('egt',time());
            $coup['is_use'] = 0;
            $coup['use_where'] = array('elt',$thprices);
            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coup)->order('use_where asc')->limit(1)->find();
        }
        $thprices1 = $thprices;
        if($CouponLog)
        {
            $thprices = $thprices - $CouponLog['price'];
        }

        $ordata['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);
        $ordata['status'] = 2;
        $ordata['total_amount'] = $thprices1;
        $ordata['pay_price'] = $thprices;
        $ordata['member_id'] = $mid;
        $ordata['order_type'] = 1;

        $ordata['coupon_id'] = $CouponLog['id'];
        $ordata['coupon_dedu'] = $CouponLog['price'];

        $TicketA = M('TicketActive')->field('ini_obj,a_type,theme_type,seller_id,destination_province,destination_city')
            ->where(array('ticket_id'=>$data['gid']))->find();
        if($TicketA)
        {
            $ordata['business_owned_id'] = $TicketA['seller_id'] ? $TicketA['seller_id'] : 0;
            $ordata['addr1'] = $TicketA['destination_province'];
            $ordata['addr2'] = $TicketA['destination_city'];
            $ordata['hdlx'] = $TicketA['a_type'];
            $ordata['hdlx2'] = $TicketA['theme_type'];
        }

        $ordata['create_time'] = time();
        $ordata['lxr_name'] = $data['lxr'];
        $ordata['lxr_mobile'] = $data['phone'];
        $ordata['lxr_sfz'] = $data['sfz'];
        $ordata['is_delete'] = 0;
        $ordata['goods_id'] = $data['gid'];
        $ordata['ishx'] = 0;

        $style = M('Ticket')->where(array('id'=>$data['gid']))->getField('style');
        if($style == 1)
        {
            $ordata['cx_time'] = strtotime($data['cx_time']);
        }

//        $ordata['rede_code'] = rand(111111,999999);

        $cxrname = explode(',',$data['cxrname']);
        $cxrsfz = explode(',',$data['cxrsfz']);
        $newrow = array();
        foreach ($cxrname as $k=>$item) {
            if($item)
            {
                $newrow[] = $item.'&'.$cxrsfz[$k];
            }
        }
        $ordata['cxr_lists'] = implode('|',$newrow);
        $ordata['jqmp_id'] = $data['gid'];
        $ordata['buyer_remarks'] = $data['buyer_remarks'];
        $ordata['yxq_time'] = strval($spec['effective_time']);
        $ordata['mp_price'] = $thprices;

        M('Order')->startTrans();
        $oid = M('Order')->add($ordata);
        if($oid)
        {
            try{

                foreach ($specs as $K=>$spec) {
                    $OG['order_no'] = $oid;
                    $OG['goods_id'] = $data['gid'];
                    $OG['goods_type'] = 1;
                    $OG['num'] = $num_all_array[$K]?$num_all_array[$K]:1;
                    $OG['total'] = $thprices;
                    //如果是特惠
                    $OG['isth'] = 0;
                    if($spec['is_going'] >= 1)
                    {
                        $OG['th_type'] = $spec['is_going'];
                        $OG['th_no'] = $spec['discount_no'];
                        $OG['isth'] = 1;
                        $OG['th_date'] = $spec['discount_date'];
                    }

                    $OG['prices'] = $thprice;
                    $OG['mpname'] = $spec['name'];
                    $OG['yxq_time'] = $spec['effective_time'];
                    $OG['ishx'] = 0;
                    $OG['attr_id'] = $data['attr_id'];
                    $OG['attr_name'] = $spec['name'];
                    $attrid = M('OrderGoods')->add($OG);

                    $paydata['pay_sn'] = $ordata['order_sn'];
                    $paydata['create_time'] = time();
                    $paydata['pay_price'] = $ordata['pay_price'];
                    $paydata['status'] = 0;
                    $paydata['order_type'] = 1;
                    $paydata['order_id'] = $oid;
                    $payid = M('Pay')->add($paydata);
                }

                if($CouponLog['id'])
                {
                    M('CouponLog')->where(array('id'=>$CouponLog['id']
                    ))->save(array('is_use'=>1));
                }

                if($payid && $attrid && $oid)
                {
                    M('Order')->commit();
                    return $ordata['order_sn'];
                }
            }catch (\Exception $e){
                M('Order')->rollback();
                $message="操作失败:".$e->getMessage();
                \Think\Log::write($message,'ERR');
                return false;
            }
        }
//        }
    }

//    public function addMpOrder($data,$mid)
//    {
//
//        $attrData = M('SpecGroup')->field('name,price,limit_price,effective_time,num')
//            ->where(array('goods_id'=>$data['gid'],'id'=>$data['attr_id'],'p_type'=>1))->find();
//
//        //检测是否促销或秒杀
//        $spec = array();   //discount_no
//        $limit_going = M('Discount')->where(array('seckill_num'=>array('gt',0),
//            'p_attr_id'=>$data['attr_id'],'p_id'=>$data['gid'],'p_type'=>1,'limit_start_time'=>array('lt',time()),
//            'limit_end_time'=>array('gt',time())))->field('preferential_type,seckill_num,discount_no,discount_price')->find();
//        if($limit_going['discount_price']){
//            $spec['discount_price'] = $limit_going['discount_price'];
//            $spec['seckill_num'] = $limit_going['seckill_num'];
//            $spec['preferential_type'] = $limit_going['preferential_type'];
//            $spec['discount_no'] = $limit_going['discount_no'];
//            $spec['is_going'] = 2;
//        }else{
//            $seckill_going = M('Discount')->alias('d')->join('lEFT JOIN __SECKILL__ s on s.id=d.seckill_nice')
//                ->where(array('d.p_attr_id'=>$data['attr_id'],'d.preferential_type'=>1,'d.seckill_num'=>array('gt',0),
//                    's.seckill_time'=>array('lt',time()),'s.end_time'=>array('gt',time())))
//                ->field('d.preferential_type,d.seckill_num,d.discount_no,d.discount_price')->find();
//            if($seckill_going['discount_price'])
//            {
//                $spec['discount_price'] = $seckill_going['discount_price'];
//                $spec['discount_no'] = $seckill_going['discount_no'];
//                $spec['preferential_type'] = $seckill_going['preferential_type'];
//                $spec['seckill_num'] = $seckill_going['seckill_num'];
//                $spec['is_going'] = 1;
//            }else
//            {
//                $spec['is_going'] = 0;
//            }
//        }
//
//        //算特惠余下的数量
//        if($spec['is_going'] > 0)
//        {
//            $num = M('OrderGoods')->where(array('th_no'=>$spec['discount_no'],'th_date'=>date('Y-m-d')))->sum('num');
//            $eKm = $spec['seckill_num'] - $num;  //可以购买的数量
//            if($eKm >= $data['nums'])
//            {
//                $etatal = $spec['discount_price'] * $data['nums'];
//            }else if($eKm < $data['nums'])
//            {
//                $OKN = $data['nums'] - $eKm;   //原价买数量
//                $etatal = $spec['discount_price'] * $eKm + $OKN * $attrData['price'];
//            }
//            $thprice = $spec['discount_price'];
//            $thprices = $etatal;
//        }else
//        {
//            $thprice = $attrData['price'];
//            $thprices = $thprice * $data['nums'];
//        }
//
//
//
//        //优惠券
//        if($data['yhjid'])
//        {
//            $coups['id'] = $data['yhjid'];
//            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coups)->find();
//        }else
//        {
//            $coup['member_id'] = $mid;
//            $coup['coupon_type'] = array('in','0,2');
//            $coup['end_time'] = array('egt',time());
//            $coup['is_use'] = 0;
//            $coup['use_where'] = array('elt',$thprices);
//            $CouponLog = M('CouponLog')->field('id,use_where,price')->where($coup)->order('use_where asc')->limit(1)->find();
//        }
//        if($CouponLog)
//        {
//            $thprices = $thprices - $CouponLog['price'];
//        }
//
//
//        $ordata['order_sn'] = substr(str_replace(' ','',str_replace(':','',str_replace('-','',date('Y-m-d H:i:s')))),2).rand(10000,999999);
//        $ordata['status'] = 2;
//        $ordata['total_amount'] = $thprices;
//        $ordata['pay_price'] = $thprices;
//        $ordata['member_id'] = $mid;
//        $ordata['order_type'] = 1;
//
//        $ordata['coupon_id'] = $CouponLog['id'];
//        $ordata['coupon_dedu'] = $CouponLog['price'];
//
//        $TicketA = M('TicketActive')->field('ini_obj,a_type,theme_type,seller_id,destination_province,destination_city')
//            ->where(array('ticket_id'=>$data['gid']))->find();
//        if($TicketA)
//        {
//            $ordata['business_owned_id'] = $TicketA['seller_id'] ? $TicketA['seller_id'] : 0;
//            $ordata['addr1'] = $TicketA['destination_province'];
//            $ordata['addr2'] = $TicketA['destination_city'];
//            $ordata['hdlx'] = $TicketA['a_type'];
//            $ordata['hdlx2'] = $TicketA['theme_type'];
//        }
//
//        $ordata['create_time'] = time();
//        $ordata['lxr_name'] = $data['lxr'];
//        $ordata['lxr_mobile'] = $data['phone'];
//        $ordata['lxr_sfz'] = $data['sfz'];
//        $ordata['is_delete'] = 0;
//        $ordata['goods_id'] = $data['gid'];
//        $ordata['ishx'] = 0;
//        $ordata['cxr_lists'] = $data['cxlxr'].'&'.''.$data['cxsfz'];
//        $ordata['jqmp_id'] = $data['gid'];
//        $ordata['buyer_remarks'] = $data['buyer_remarks'];
//        $ordata['yxq_time'] = strval($attrData['effective_time']);
//        $ordata['mp_price'] = $thprices;
//
//        M('Order')->startTrans();
//        $oid = M('Order')->add($ordata);
//        if($oid)
//        {
//            try{
//                $OG['order_no'] = $oid;
//                $OG['goods_id'] = $data['gid'];
//                $OG['goods_type'] = 1;
//                $OG['num'] = $data['nums'];
//
//                $OG['total'] = $thprices;
//
//                //如果是特惠
//                $OG['isth'] = 0;
//                if($spec['is_going'] >= 1)
//                {
//                    $OG['th_type'] = $spec['is_going'];
//                    $OG['th_no'] = $spec['discount_no'];
//                    $OG['isth'] = 1;
//                    $OG['th_date'] = date('Y-m-d');
//                }
//
//                $OG['prices'] = $thprice;
//                $OG['mpname'] = $attrData['name'];
//                $OG['yxq_time'] = $attrData['effective_time'];
//                $OG['ishx'] = 0;
//                $OG['attr_id'] = $data['attr_id'];
//                $OG['attr_name'] = $attrData['name'];
//                $attrid = M('OrderGoods')->add($OG);
//
//                $paydata['pay_sn'] = $ordata['order_sn'];
//                $paydata['create_time'] = time();
//                $paydata['pay_price'] = $ordata['pay_price'];
//                $paydata['status'] = 0;
//                $paydata['order_type'] = 1;
//                $paydata['order_id'] = $oid;
//
//                $payid = M('Pay')->add($paydata);
//
//                if($CouponLog['id'])
//                {
//                    M('CouponLog')->where(array('id'=>$CouponLog['id']
//                    ))->save(array('is_use'=>0));
//                }
//
//                if($payid && $attrid && $oid)
//                {
//                    M('Order')->commit();
//                    return $ordata['order_sn'];
//                }
//            }catch (\Exception $e){
//                M('Order')->rollback();
//                $message="操作失败:".$e->getMessage();
//                \Think\Log::write($message,'ERR');
//                return false;
//            }
//        }
////        }
//    }
    public function getOrderDetalisById($id)
    {
        $data = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __TICKET__ T on T.id=O.jqmp_id')
            ->field('O.*,M.nickname,M.headimg,M.mobile')
            ->order('O.id desc')
            ->where(array('O.id'=>$id))->find();

        $ishxArray = array(0=>'未核销',1=>'已核销',2=>'已过期');
        $orderStatusArray = array(100=>'已完成',3=>'待出行',4=>'待评价',13=>'已退款',14=>'已过期',6=>'已支付',1=>'已取消',2=>'待付款',
            7=>'待发货',8=>'待收货');
//        $payTypeArray = array(1=>'微信',2=>'支付宝',3=>'余额',4=>'积分');
        $payTypeArray = array('0'=>'微信支付','1'=>'支付宝支付','2'=>'余额支付','4'=>'积分支付');
        $data['pay_type'] = M('Pay')->where(array('order_id'=>$id))->getField('pay_type');

        $data['ishx'] = $ishxArray[$data['ishx']];

        $data['pay_type'] = $payTypeArray[$data['pay_type']];
        if($data['status'] == 100 && $data['is_comment'] == 0)
        {
            $data['status'] = '待点评';
        }else
        {
            $data['status'] = $orderStatusArray[$data['status']];
        }

        if($data['business_owned_id'] > 1)
        {
            $data['business_owned_id'] = M('Business')->where(array('uid'=>$data['business_owned_id']))->getField('name');
        }

        $data['actiname'] = M('Ticket')->where(array('id'=>$data['jqmp_id']))->getField('title');
        $OrderGoods = M('OrderGoods')->where(array('order_no'=>$id))->select();


        $ogs = M('OrderGoods')->where(array('order_no'=>$id))->find();
        if(!$ogs['c_num'] || !$ogs['e_num'])
        {

            $data['yzp'] = 2;
            if($OrderGoods)
            {
                foreach ($OrderGoods as &$orderGood) {
                    if($ogs['c_num'])
                    {
                        $orderGood['mpname'] = $ogs['mpname'].'-成人票';
                        $orderGood['prices'] = $ogs['c_price'];

                    }
                    if($ogs['e_num'])
                    {
                        $orderGood['mpname'] = $ogs['mpname'].'-儿童票';
                        $orderGood['prices'] = $ogs['e_price'];
                    }
                }
            }

            $data['mpqd'] = $OrderGoods;
        }else
        {
            $rows = array();
            if($ogs['c_num'])
            {
                $row['mpname'] = $ogs['mpname'].'-成人票';
                $row['prices'] = $ogs['c_price'];
                $row['num'] = $ogs['c_num'];
                $row['isth'] = $ogs['isth'];
                $row['th_type'] = $ogs['th_type'];
                $row['total'] = $ogs['c_price'] * $ogs['c_num'];
                $rows[] = $row;
            }
            if($ogs['e_num'])
            {
                $row['mpname'] = $ogs['mpname'].'-儿童票';
                $row['prices'] = $ogs['e_price'];
                $row['num'] = $ogs['e_num'];
                $row['isth'] = $ogs['isth'];
                $row['th_type'] = $ogs['th_type'];
                $row['total'] = $ogs['e_price'] * $ogs['e_num'];
                $rows[] = $row;
            }

        }
        if( $data['yzp'] != 2)
        {
            $data['mpqd'] = $rows;
        }else
        {
            $data['mpqd'] = $OrderGoods;
        }

        foreach ($data['mpqd'] as &$orderGood) {
            $orderGood['isth'] = $orderGood['isth'] == 1 ? '是': '不是';
            $orderGood['th_type'] = $orderGood['th_type']? $orderGood['th_type']: '';
        }

        $commi = M('MemberCommission')->alias('MC')
            ->join('LEFT JOIN __MEMBER__ M on M.id=MC.member_id')
            ->field('MC.price,M.nickname')->order('MC.dengji asc')
            ->where(array('MC.order_id'=>$id,'MC.buy_member_id'=>$data['member_id']))->select();
        $data['tc'] = $commi;

        return $data;
    }



    public  function getJfOrder($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where = array();
        if($search['order_sn'])
        {
            $where['O.order_sn'] = array('like',"%{$search['order_sn']}%");
        }
        if($search['sales_name'])
        {
            $where['M.nickname'] = array('like',"%{$search['sales_name']}%");
        }
        if($search['mobile'])
        {
            $where['M.mobile'] = array('like',"%{$search['mobile']}%");
        }



        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $search['end_time'] = strtotime($search['end_time'].'23:59:59');
                $where['O.create_time'] = array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time']){
            $where['O.create_time'] = array('egt', strtotime($search['st_time']));
        }elseif(!$search['st_time'] && $search['end_time']){
            $search['end_time'] = strtotime($search['end_time'].'23:59:59');
            $where['O.create_time'] = array('elt',$search['end_time']);
        }

        if($search['shrphone'])
        {
            $where['O.receiver_mobile'] = array('like',"%{$search['shrphone']}%");
        }

        if($search['status'])
        {
            $where['O.status'] = $search['status'];
        }
        $where['O.order_type'] = 2;
        $where['O.is_delete'] = 0;
//        $counts = $this->alias('O') ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->count();

        $data = $this->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->page($page,$pageSize)
            ->field('O.order_sn,O.create_time,O.receiver_address,O.receiver,O.receiver_mobile,O.pay_price,O.status,O.id,M.nickname,M.mobile')
            ->order('O.id desc')
            ->where($where)->select();
        foreach ($data as &$datum) {
            switch ($datum['status']){
                case 7:$datum['status'] = '待发货';break;
                case 8:$datum['status'] = '待收货';break;
                case 100:$datum['status'] = '已完成';break;

            }
            $datum['create_time'] = date('Y-m-d H:i:s',$datum['create_time']);
        }
        $allData = M('Order')->field('pay_price')->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->select();
        $counts = count($allData);
        $price = 0;
        foreach ($allData as $allDatum)
        {
            $price += $allDatum['pay_price'];
        }
        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['total'] = $counts;
        $dataS['pagesize'] = $pageSize;
        $dataS['price'] = $price;
        $dataS['total_page'] = $total_page;
        return $dataS;
    }


    public function getGoodsOrder($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where = array();
        if($search['order_sn'])
        {
            $where['O.order_sn'] = array('like',"%{$search['order_sn']}%");
        }
        if($search['sales_name'])
        {
            $where['M.nickname'] = array('like',"%{$search['sales_name']}%");
        }
        if($search['mobile'])
        {
            $where['M.mobile'] = array('like',"%{$search['mobile']}%");
        }

        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $search['end_time'] = strtotime($search['end_time'].'23:59:59');
                $where['O.create_time'] = array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time']){
            $where['O.create_time'] = array('egt', strtotime($search['st_time']));
        }elseif(!$search['st_time'] && $search['end_time']){
            $search['end_time'] = strtotime($search['end_time'].'23:59:59');
            $where['O.create_time'] = array('elt',$search['end_time']);
        }
        if($search['shrphone'])
        {
            $where['M.receiver_mobile'] = array('like',"%{$search['shrphone']}%");
        }
        if($search['status'])
        {
            $where['O.status'] = $search['status'];
        }
        $where['O.order_type'] = 5;
        $where['O.is_delete'] = 0;
        if($search['member_id']){
            $where['O.member_id']=$search['member_id'];
        }
//        $counts = $this->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->count();

        $data = $this->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->page($page,$pageSize)
            ->field('O.order_sn,O.receiver_address,O.receiver,O.receiver_mobile,O.create_time,O.pay_price,O.total_amount,O.mp_price,O.freight,O.coupon_dedu,O.status,O.id,M.nickname,M.mobile,O.order_type')
            ->order('O.id desc')
            ->where($where)->select();

        foreach ($data as &$datum) {
            switch ($datum['status']){
                case 1:$datum['status'] = '已取消';break;
                case 2:$datum['status'] = '待付款';break;
                case 7:$datum['status'] = '待发货';break;
                case 8:$datum['status'] = '待收货';break;
                case 100:$datum['status'] = '已完成';break;
            }
            switch($datum['order_type']){
                case 1:$datum['order_type'] = '门票订单 ';break;
                case 2:$datum['order_type'] = '积分订单';break;
                case 3:$datum['order_type'] = '自营活动订单';break;
                case 4:$datum['order_type'] = '商家活动订单';break;
                case 5:$datum['order_type'] = '商品订单';break;
            }
            $datum['create_time'] = date('Y-m-d H:i:s',$datum['create_time']);
        }
        $allData = M('Order')->field('pay_price')->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->select();
        $counts = count($allData);
        $price = 0;
        foreach ($allData as $allDatum)
        {
            $price += $allDatum['pay_price'];
        }
        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['total'] = $counts;
        $dataS['price'] = $price;
        $dataS['pagesize'] = $pageSize;
        $dataS['total_page'] = $total_page;
        return $dataS;
    }

    public function getBusiActiveOrder($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where = array();
        $where = $this->backWhereZy($search);

        if($where == -1){
            $this->error = '开始时间大于结束时间';
            return false;
        }
        $where['O.order_type'] = 4;
        $where['O.is_delete'] = 0;

        if($search['sssj'])
        {
            $where['O.business_owned_id'] = $search['sssj'];
        }
        $data = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __ORDER_GOODS__ OG on O.id=OG.order_no')
            ->page($page,$pageSize)
            ->field('O.*,M.nickname,M.mobile,OG.mpname')
            ->order('O.id desc')
            ->where($where)->select();
        $ishxArray = array(0=>'未核销',1=>'已核销',2=>'已过期');
        $orderStatusArray = array(100=>'已完成',3=>'待出行',4=>'待评价',13=>'已退款',14=>'已过期',6=>'已支付',1=>'已取消',2=>'待付款',
            7=>'待发货',8=>'待收货');
        $payTypeArray = array(1=>'微信',2=>'支付宝',3=>'余额',4=>'积分');
        if($data)
        {
            foreach ($data as &$datum) {
                $datum['ishx'] = $ishxArray[$datum['ishx']];
                $datum['pay_type'] = $payTypeArray[$datum['pay_type']];
                //  $datum['status'] = $orderStatusArray[$datum['status']];
                if($datum['status'] == 100 && $datum['is_comment'] == 0)
                {
                    $datum['status'] = '待点评';
                }else if($datum['status'] == 100 && $datum['is_comment'] == 1)
                {
                    $datum['status'] = '已完成';
                }
                else
                {
                    $datum['status'] = $orderStatusArray[$datum['status']];
                }

                $datum['create_time'] = date('Y-m-d H:i:s',$datum['create_time']);
                $datum['cx_time'] = $datum['cx_time']?date('Y-m-d H:i:s',$datum['cx_time']):date('Y-m-d H:i:s',$datum['yxq_time']);
                $datum['business_owned_id'] = M('Business')->where(array('uid'=>$datum['business_owned_id']))->getField('name');
                $datum['mpname'] = M('Ticket')->where(array('id'=>$datum['jqmp_id']))->getField('title');
            }
        }
        $allData = M('Order')->field('pay_price')->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->select();
        $counts = count($allData);
        $price = 0;
        foreach ($allData as $allDatum)
        {
            $price += $allDatum['pay_price'];
        }

        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['total'] = $counts;
        $dataS['price'] = $price;
        $dataS['pagesize'] = $pageSize;
        $dataS['total_page'] = $total_page;
        return $dataS;
    }

    //自营订单
    public function getZyOrder($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where = array();
        $where = $this->backWhereZy($search);

        if($where == -1){
            $this->error = '开始时间大于结束时间';
            return false;
        }
        $where['O.order_type'] = 3;
        $where['O.is_delete'] = 0;
        $data = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __ORDER_GOODS__ OG on O.id=OG.order_no')
            ->page($page,$pageSize)
            ->field('O.*,M.nickname,M.mobile,OG.mpname')
            ->order('O.id desc')
            ->where($where)->select();
        $ishxArray = array(0=>'未核销',1=>'已核销',2=>'已过期');
        $orderStatusArray = array(100=>'已完成',3=>'待出行',4=>'待评价',13=>'已退款',14=>'已过期',6=>'已支付',1=>'已取消',2=>'待付款',
            7=>'待发货',8=>'待收货');
        $payTypeArray = array(1=>'微信',2=>'支付宝',3=>'余额',4=>'积分');
        if($data)
        {
            foreach ($data as &$datum) {
                $datum['ishx'] = $ishxArray[$datum['ishx']];
                $datum['pay_type'] = $payTypeArray[$datum['pay_type']];
//                $datum['status'] = $orderStatusArray[$datum['status']];

                if($datum['status'] == 100 && $datum['is_comment'] == 0)
                {
                    $datum['status'] = '待点评';
                }else if($datum['status'] == 100 && $datum['is_comment'] == 1)
                {
                    $datum['status'] = '已完成';
                }
                else
                {
                    $datum['status'] = $orderStatusArray[$datum['status']];
                }

                $datum['create_time'] = date('Y-m-d H:i:s',$datum['create_time']);
                $datum['cx_time'] = $datum['cx_time']?date('Y-m-d H:i:s',$datum['cx_time']):date('Y-m-d H:i:s',$datum['yxq_time']);
                $datum['mpname'] = M('Ticket')->where(array('id'=>$datum['jqmp_id']))->getField('title');
            }



        }
        $allData = M('Order')->field('pay_price')->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->select();
        $counts = count($allData);
        $price = 0;
        foreach ($allData as $allDatum)
        {
            $price += $allDatum['pay_price'];
        }
        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['total'] = $counts;
        $dataS['price'] = $price;
        $dataS['pagesize'] = $pageSize;
        $dataS['total_page'] = $total_page;
        return $dataS;

    }

    //已核销数据
    public function getHxData($search=array(),$order='',$page=1,$pageSize=10,$type='')
    {
        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $search['end_time'] = strtotime($search['end_time'].'23:59:59');
                $where['O.hx_time'] = array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));

            }
        }elseif($search['st_time'] && !$search['end_time']){
            $where['O.hx_time'] = array('egt', strtotime($search['st_time']));
        }elseif(!$search['st_time'] && $search['end_time']){
            $search['end_time'] = strtotime($search['end_time'].'23:59:59');
            $where['O.hx_time'] = array('elt',$search['end_time']);
        }
        $where['O.ishx'] = 1;
        if($type == 'zy')  //自营活动
        {
            $where['O.order_type'] = 3;
        }else if($type == 'mp') //门票
        {
            $where['O.order_type'] = 1;
        }else if($type == 'sj')  //商家活动
        {
            $where['O.order_type'] = 4;
        }
        if($search['sjid'] > 0)
        {
            $where['O.business_owned_id'] = $search['sjid'];
        }
        $where['O.is_delete'] = 0;
        if($search['kw'])
        {
            $where['O.order_sn|OG.mpname'] = array('like',"%{$search['kw']}%");
        }
        $data = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __ORDER_GOODS__ OG on OG.order_no=O.id')
            ->join('LEFT JOIN __USERS__ U on O.hxopt=U.id')

            ->page($page,$pageSize)
            ->field('O.id,O.order_sn,O.hxopt,O.hx_time,O.business_owned_id,M.nickname,M.mobile,(U.user_login) as hxopt')
            ->order('O.id desc')
            ->group('OG.order_no')
            ->where($where)->select();

        foreach ($data as &$datum) {
            $datum['hx_time'] = date('Y-m-d H:i:s',$datum['hx_time']);
            if($datum['business_owned_id'])
            {
                $datum['business_owned_id'] = M('Business')->where(array('uid'=>$datum['business_owned_id']))->getField('name');
            }
        }

        $counts = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __ORDER_GOODS__ OG on OG.order_no=O.id')
            ->where($where)->count();
        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['pagesize'] = $pageSize;
        $dataS['total_page'] = $total_page;
        return $dataS;
    }





    /**  自营交易额统计
     * @param array $search
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getZyGoodsData($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where['business_owned_id'] = 0;
        if($search['type'] == 'mp')
        {
            $where['order_type'] = 1;
        }else if($search['type'] == 'sp')
        {
            $where['order_type'] = 5;
        }
        else if($search['type'])
        {
            $where['order_type'] = array('in','1,3,5');
            $where['hdlx'] = $search['type'];
        }
        $where['status'] = array('gt',2);
        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $where['pay_day'] = array(array('egt', $search['st_time']),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time'])
        {
            $where['pay_day']=array('egt', $search['st_time']);
        }elseif(!$search['st_time'] && $search['end_time'])
        {
            $where['pay_day']=array('elt', $search['end_time']);
        }else
        {
            $where['pay_day'] = array(array('egt',date('Y-m-d',strtotime('-7 day'))),array('elt',date('Y-m-d')));
        }

        if($search['stype'] == 1)    // 导出
        {
            $data = M('Order')->field('pay_day')->group('pay_day')->order('pay_day desc')->where($where)->select();
        }else{

            $data = M('Order')->field('pay_day')->group('pay_day')->order('pay_day desc')->page($page,$pageSize)->where($where)->select();
        }

//        $count = M('Order')->group('pay_day')->where($where)->count();
        $count = count($data);

        //总订单   总金额
        $total = M('Order')->where($where)->count();
        $price = M('Order')->where($where)->sum('pay_price');

        unset($where['pay_day']);
        $rows = array();
        foreach ($data as $item)
        {
            $day = $item['pay_day'];

            $where['pay_day'] = $day;
            $where['is_delete'] = 0;
            $dds = 0;
            $where['pay_time'] = array('gt',0);
            $dds = M('Order')->where($where)->count();
            unset($where['pay_time']);

            $xse = 0.00;
            $xse = M('Order')->where($where)->sum('pay_price');

            $row = array();
            $row['t'] = $day;
            $row['dds'] = $dds?$dds:0;
            $row['xse'] = $xse?$xse:0;

            if($search['type'] > 0)
            {
                $row['types'] = M('TicketActiveType')->where(array('id'=>$search['type']))->getField('name');
            }
            else{
                $row['types'] = '全部';
            }
            $rows[] = $row;
        }
        if($search['stype'] == 1)    // 导出
        {
            return $rows;
        }else
        {
            $total_page = ceil($count / $pageSize);

            $result['list'] = $rows;
            $result['p'] = $page;
            $result['total'] = $total;
            $result['price'] = $price;
            $result['pagesize'] = $pageSize;
            $result['total_page'] = $total_page;
            return $result;
        }

    }

    //总后台商家交易
    public function getAdllBusiJy($search=array(),$order='',$page=1,$pageSize=10)
    {
        if($search['bid'])
        {
            $where['business_owned_id'] = $search['bid'];
        }else
        {
            $where['business_owned_id'] = array('gt',0);
        }
        $where['status'] = array('gt',2);

        $where['order_type'] = array('neq',3);
        if($search['addr1'])
        {
            $where['addr1'] = $search['addr1'];
        }
        if($search['addr2'])
        {
            $where['addr2'] = $search['addr2'];
        }
        if($search['type'])
        {
            $where['hdlx'] = $search['type'];
        }
        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $where['pay_day'] = array(array('egt', $search['st_time']),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time'])
        {
            $where['pay_day']=array('egt', $search['st_time']);
        }elseif(!$search['st_time'] && $search['end_time'])
        {
            $where['pay_day']=array('elt', $search['end_time']);
        }else
        {
            $where['pay_day'] = array(array('egt',date('Y-m-d',strtotime('-7 day'))),array('elt',date('Y-m-d')));
        }
        if($search['stype'] == 1)  // 导出
        {
            $data = M('Order')->field('pay_day,business_owned_id')->order('pay_day desc')->group('pay_day')->where($where)->select();
        }else{
            $data = M('Order')->field('pay_day,business_owned_id')->order('pay_day desc')->group('pay_day')->page($page,$pageSize)->where($where)->select();
        }
        //分页总条数
        $counts = M('Order')->group('pay_day')->where($where)->select();
        $count = count($counts);
        //总订单   总金额

        $total = M('Order')->where($where)->count();
        $price = M('Order')->where($where)->sum('pay_price');

        unset($where['pay_day']);
        $rows = array();
        foreach ($data as $item)
        {
            $day = $item['pay_day'];
            $where['pay_day'] = $day;
            $where['is_delete'] = 0;
            $dds = 0;
            $where['pay_time'] = array('gt',0);
            $dds = M('Order')->where($where)->count();
            unset($where['pay_time']);

            $xse = 0.00;
            $xse = M('Order')->where($where)->sum('pay_price');

            $ptdk = 0;
            $ptdk = M('Order')->where($where)->sum('ptdk');

            $row = array();
            $row['t'] = $day;
            if($search['bid'] > 0)
            {
                $row['sj'] = M('Business')->where(array('uid'=>$search['bid']))->getField('name');
            }else
            {
                $row['sj'] = '全部';
            }

            $row['dds'] = $dds?$dds:0;
            $row['xse'] = $xse?$xse:0;
            $row['ptdk'] = $ptdk?$ptdk:0;

            if($search['type'] > 0)
            {
                $row['types'] = M('TicketActiveType')->where(array('id'=>$search['type']))->getField('name');
            }
            else{
                $row['types'] = '全部';
            }
            $rows[] = $row;
        }
        if($search['stype'] == 1)    // 导出
        {
            return $rows;
        }else
        {
            $total_page = ceil($count / $pageSize);

            $result['list'] = $rows;
            $result['p'] = $page;
            $result['total'] = $total;
            $result['price'] = $price;
            $result['pagesize'] = $pageSize;
            $result['total_page'] = $total_page;
            return $result;
        }
    }

    public function getBusiJyData($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where['business_owned_id'] = $_SESSION['ADMIN_ID'];
        if($search['addr1'])
        {
            $where['addr1'] = $search['addr1'];
        }
        if($search['addr2'])
        {
            $where['addr2'] = $search['addr2'];
        }
        if($search['type'])
        {
            $where['hdlx'] = $search['type'];
        }

        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $where['pay_day'] = array(array('egt', $search['st_time']),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time'])
        {
            $where['pay_day']=array('egt', $search['st_time']);
        }elseif(!$search['st_time'] && $search['end_time'])
        {
            $where['pay_day']=array('elt', $search['end_time']);
        }else
        {
            $where['pay_day'] = array(array('egt',date('Y-m-d',strtotime('-7 day'))),array('elt',date('Y-m-d')));
        }

        if($search['stype'] == 1)    // 导出
        {
            $data = M('Order')->field('pay_day')->order('pay_day desc')->group('pay_day')->where($where)->select();
        }else{
            $data = M('Order')->field('pay_day')->order('pay_day desc')->group('pay_day')->page($page,$pageSize)->where($where)->select();
        }

        $count = M('Order')->group('pay_day')->where($where)->count();

        //总订单   总金额
        $total = M('Order')->where($where)->count();
        $price = M('Order')->where($where)->sum('pay_price');

        unset($where['pay_day']);
        $rows = array();
        foreach ($data as $item)
        {
            $day = $item['pay_day'];

            $where['pay_day'] = $day;
            $where['is_delete'] = 0;
            $dds = 0;
            $where['pay_time'] = array('gt',0);
            $dds = M('Order')->where($where)->count();
            unset($where['pay_time']);

            $xse = 0.00;
            $xse = M('Order')->where($where)->sum('pay_price');

            $ptdk = 0;
            $ptdk = M('Order')->where($where)->sum('ptdk');

            $row = array();
            $row['t'] = $day;
            $row['sj'] = M('Business')->where(array('uid'=>$_SESSION['ADMIN_ID']))->getField('name');
            $row['dds'] = $dds?$dds:0;
            $row['xse'] = $xse?$xse:0;
            $row['ptdk'] = $ptdk?$ptdk:0;

            if($search['type'] > 0)
            {
                $row['types'] = M('TicketActiveType')->where(array('id'=>$search['type']))->getField('name');
            }
            else{
                $row['types'] = '全部';
            }
            $rows[] = $row;
        }
        if($search['stype'] == 1)    // 导出
        {
            return $rows;
        }else
        {
            $total_page = ceil($count / $pageSize);

            $result['list'] = $rows;
            $result['p'] = $page;
            $result['total'] = $total;
            $result['price'] = $price;
            $result['pagesize'] = $pageSize;
            $result['total_page'] = $total_page;

            return $result;
        }
    }



    //备用
    public function getBusiJyDataBACK($search=array(),$order='',$page=1,$pageSize=10)
    {
        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $timeday = strtotime($search['end_time']) - strtotime($search['st_time']);
                $days = $timeday/86400;
                $t = strtotime($search['end_time']);

                return $this->getDataByDay($search,$days,$page,$pageSize,$t);
            }
        }elseif($search['st_time'] && !$search['end_time'])
        {
            $endtime = date('Y-m-d',time());
            $timeday = strtotime($endtime) - strtotime($search['st_time']);
            $days = $timeday/86400;
            $t = strtotime($endtime);
            return $this->getDataByDay($search,$days,$page,$pageSize,$t);

        }elseif(!$search['st_time'] && $search['end_time'])
        {
            $strat = M('order')->where(array('pay_day'=>array('elt',$search['end_time'])))->order('pay_day asc')->getField('pay_day');
            $timeday = strtotime($search['end_time']) - strtotime($strat);
            $days = $timeday/86400;         //天数

            $t = strtotime($search['end_time']);  //结束时间
            return $this->getDataByDay($search,$days,$page,$pageSize,$t);
        }else
        {
            return $this->getDataByDay($search,7,$page,$pageSize,7);
        }
    }

    private function getDataByDay($search,$days,$page,$pageSize,$type='')
    {
        $where['business_owned_id'] = $_SESSION['ADMIN_ID'];
        if($search['addr1'])
        {
            $where['addr1'] = $search['addr1'];
        }
        if($search['addr2'])
        {
            $where['addr2'] = $search['addr2'];
        }
        if($search['type'])
        {
            $where['hdlx'] = $search['type'];
        }

        $rows = array();
        $total = 0;
        $price = 0.00;
        if($type == 7)
        {
            $days = $days - 1;
        }
        for($i = 0;$i <= $days;$i++)
        {
            if($type == 7)
            {
                $day = date('Y-m-d',strtotime('-'.$i.'day'));
            }else{
                $day = date('Y-m-d',$type-$i*86400);
            }
            $where['pay_day'] = $day;
            $where['is_delete'] = 0;
            $dds = 0;
            $where['pay_time'] = array('gt',0);
            $dds = M('Order')->where($where)->count();
            unset($where['pay_time']);
            $total += $dds;

            $xse = 0.00;
            $xse = M('Order')->where($where)->sum('pay_price');
            $price += $xse;

            $ptdk = 0;
            $ptdk = M('Order')->where($where)->sum('ptdk');

            $row = array();
            $row['t'] = $day;
            $row['sj'] = M('Business')->where(array('uid'=>$_SESSION['ADMIN_ID']))->getField('name');
            $row['dds'] = $dds?$dds:0;
            $row['xse'] = $xse?$xse:0;
            $row['ptdk'] = $ptdk?$ptdk:0;
            if($search['type'] > 0)
            {
                $row['types'] = M('TicketActiveType')->where(array('id'=>$search['type']))->getField('name');
            }
            else{
                $row['types'] = '全部';
            }
            if($dds > 0)
            {
                $rows[] = $row;
            }
        }
        if($search['stype'] == 1)    // 导出
        {
            return $rows;
        }else
        {
            $newarray = array();
            $stra = ($page-1) * $pageSize;
            $end = $stra + $pageSize;
            foreach ($rows as $k=>$row) {
                if($k>= $stra && $k < $end)
                {
                    $newarray[] = $row;
                }
            }
            $total_page = ceil(count($rows) / $pageSize);
            $dataS['list'] = $newarray;
            $dataS['p'] = $page;
            $dataS['total'] = count($rows);
            $dataS['pagesize'] = $pageSize;
            $dataS['total_page'] = $total_page;
            $dataS['total'] = $total;
            $dataS['price'] = $price;
            return $dataS;
        }
    }


    //门票订单
    public  function getMOrder($search=array(),$order='',$page=1,$pageSize=10){
        $Ticket = M('Ticket')->field('title,id')->where(array('type'=>1,'is_delete'=>0))->select();  //景区门票列表
        $where = array();


        $where = $this->backWhere($search);
        if($where == -1){
            $this->error = '开始时间大于结束时间';
            return false;
        }
        $where['O.order_type'] = 1;
        $where['O.is_delete'] = 0;

        $data = M('Order')->alias('O')
            ->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')
            ->join('LEFT JOIN __TICKET__ T on T.id=O.jqmp_id')
            ->page($page,$pageSize)
            ->field('O.*,M.nickname,M.mobile,T.title as jqmp')
            ->order('O.id desc')
            ->where($where)->select();
        $ishxArray = array(0=>'未核销',1=>'已核销',2=>'已过期');
        $orderStatusArray = array(100=>'已完成',3=>'待出行',4=>'待评价',13=>'已退款',14=>'已过期',6=>'已支付',1=>'已取消',2=>'待付款',
            7=>'待发货',8=>'待收货');
        $payTypeArray = array(1=>'微信',2=>'支付宝',3=>'余额',4=>'积分');
        if($data)
        {
            foreach ($data as &$datum) {
                $datum['ishx'] = $ishxArray[$datum['ishx']];
                $datum['pay_type'] = $payTypeArray[$datum['pay_type']];
                if($datum['status'] == 100 && $datum['is_comment'] == 0)
                {
                    $datum['status'] = '待点评';
                }else if($datum['status'] == 100 && $datum['is_comment'] == 1)
                {
                    $datum['status'] = '已完成';
                }
                else
                {
                    $datum['status'] = $orderStatusArray[$datum['status']];
                }


                $datum['create_time'] = date('Y-m-d H:i:s', $datum['create_time']);
            }
        }

        $allData = M('Order')->field('pay_price')->alias('O')->join('LEFT JOIN __MEMBER__ M on M.id=O.member_id')->where($where)->select();
        $counts = count($allData);
        $price = 0;
        foreach ($allData as $allDatum)
        {
            $price += $allDatum['pay_price'];
        }
        $total_page = ceil($counts / $pageSize);
        $dataS['list'] = $data;
        $dataS['p'] = $page;
        $dataS['total'] = $counts;
        $dataS['price'] = $price;
        $dataS['pagesize'] = $pageSize;
        $dataS['total_page'] = $total_page;
        $dataS['ticket'] = $Ticket;
        return $dataS;
    }

    private function backWhereZy($search)
    {
        $where = array();
        if($search['order_sn'])
        {
            $where['O.order_sn'] = array('like',"%{$search['order_sn']}%");
        }
        if($search['sales_name'])
        {
            $where['M.nickname'] = array('like',"%{$search['sales_name']}%");
        }
        if($search['mobile'])
        {
            $where['M.mobile'] = array('like',"%{$search['mobile']}%");
        }

        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $search['end_time'] = strtotime($search['end_time'].'23:59:59');
                $where['O.create_time'] = array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time']){
            $where['O.create_time'] = array('egt', strtotime($search['st_time']));
        }elseif(!$search['st_time'] && $search['end_time']){
            $search['end_time'] = strtotime($search['end_time'].'23:59:59');
            $where['O.create_time'] = array('elt',$search['end_time']);
        }

        if($search['hdlx'])
        {
            $where['O.hdlx'] = $search['hdlx'];
        }
        if($search['hdlx2'])
        {
            $where['O.hdlx2'] = $search['hdlx2'];
        }
        if($search['st_time1'] && $search['end_time1']){
            if($search['st_time1'] > $search['end_time1'])
            {
                return -1;
            }else
            {
                $search['end_time1'] = strtotime($search['end_time1'].'23:59:59');
                $where['O.cx_time'] = array(array('egt', strtotime($search['st_time1'])),array('elt',$search['end_time1']));
//                var_dump($where['O.cx_time']);
            }
        }elseif($search['st_time1'] && !$search['end_time1'])
        {
            $where['O.cx_time'] = array('egt', strtotime($search['st_time1']));


        }elseif(!$search['st_time1'] && $search['end_time1']){
            $search['end_time1'] = strtotime($search['end_time1'].'23:59:59');
            $where['O.cx_time'] = array('elt',$search['end_time1']);
        }
        if($search['ishx'] && $search['ishx'] != 'all')
        {
            if(intval($search['ishx']) == 3)
            {
                $where['O.ishx'] = 0;
            }else{
                $where['O.ishx'] = $search['ishx'];
            }
        }

        if($search['lxrname'])
        {
            $where['O.lxr_name'] = array('like',"%{$search['lxrname']}%");
        }
        if($search['lxrphone'])
        {
            $where['O.lxr_mobile'] = array('like',"%{$search['lxrphone']}%");
        }
        if($search['status'])
        {
            $where['O.status'] = $search['status'];
        }
        return $where;
    }

    private function backWhere($search)
    {
        $where = array();
        if($search['order_sn'])
        {
            $where['O.order_sn'] = array('like',"%{$search['order_sn']}%");
        }
        if($search['sales_name'])
        {
            $where['M.nickname'] = array('like',"%{$search['sales_name']}%");
        }
        if($search['mobile'])
        {
            $where['M.mobile'] = array('like',"%{$search['mobile']}%");
        }

        if($search['st_time'] && $search['end_time']){
            if($search['st_time'] > $search['end_time'])
            {
                return -1;
            }else
            {
                $search['end_time'] = strtotime($search['end_time'].'23:59:59');
                $where['O.create_time'] = array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));
            }
        }elseif($search['st_time'] && !$search['end_time']){
            $where['O.create_time'] = array('egt', strtotime($search['st_time']));
        }elseif(!$search['st_time'] && $search['end_time']){
            $search['end_time'] = strtotime($search['end_time'].'23:59:59');
            $where['O.create_time'] = array('elt',$search['end_time']);
        }
        if($search['jqmp'])
        {
            $where['O.jqmp_id'] = $search['jqmp'];
        }
        if($search['ishx'] && $search['ishx'] != 'all')
        {
            $where['O.ishx'] = $search['ishx'];
        }
        if($search['lxrname'])
        {
            $where['O.lxr_name'] = $search['lxrname'];
        }
        if($search['lxrphone'])
        {
            $where['O.lxr_mobile'] = $search['lxrphone'];
        }

//        if($search['paystatus'] == 4)
//        {
//            $where['O.status'] = 100;
//            $where['O.is_comment'] = 0;
//        }elseif ($search['paystatus'] == 100)
//        {
//            $where['O.status'] = 100;
//            $where['O.is_comment'] = 1;
//        }else
//        {
//            $where['O.status'] = $search['paystatus'];
//        }
        if($search['paystatus'])
        {
            if($search['paystatus'] == 4)
            {
                $where['O.status'] = 100;
                $where['O.is_comment'] = 0;
            }else  if($search['paystatus'] == 100)
            {
                $where['O.status'] = 100;
                $where['O.is_comment'] = 1;
            }else{

                $where['O.status'] = $search['paystatus'];
            }
        }


        if($search['paytype'])
        {
            $where['O.pay_type'] = $search['paytype'];
        }
        return $where;
    }


    //获取用户待付款 待发货  待收货 数量
    public function getOrderNums($member_id)
    {

        $where['is_delete'] = 0;
        $where['order_type'] = array('in','2,5');
        if($member_id > 0)
        {
            $where['member_id'] = $member_id;
        }else{
            $this->error('请先登录');
        }
        $where['status'] = 2;
        $row['dfk'] = $this->where($where)->count();
        $where['status'] = 7;
        $row['dfh'] = $this->where($where)->count();
        $where['status'] = 8;
        $row['dsh'] = $this->where($where)->count();
        unset($where);
        return $row;

    }


    //用户确认收货订单
    public function confirm($id,$member_id){
        $order = $this->field('member_id,status')->find($id);
        if(!$order){
            $this->error='所选订单不存在';
            return false;
        }
        if($order['member_id'] != $member_id){
            $this->error = '不能操作别人的订单';
            return false;
        }
        if($order['status'] !=8)
        {
            $this->error='当前订单不能确认收货';
            return false;
        }
        if($this->where(array('id'=>$id,'member_id'=>$member_id))->save(array('status'=>100,'confirm_time'=>time()))!==false){
            return true;
        }else
        {
            $this->error='确认收货失败';
            return false;
        }
    }

    //取消商品订单
    public function cancel($id,$member_id){
        $order = $this->field('member_id,status')->find($id);
        if(!$order){
            $this->error='所选订单不存在';
            return false;
        }
        if($order['member_id']!=$member_id){
            $this->error='不能操作别人的订单';
            return false;
        }
        if($order['status']!=2)
        {
            $this->error='当前订单不能取消订单';
            return false;
        }
        if($this->where(array('id'=>$id,'member_id'=>$member_id))->save(array('status'=>1,'cancel_type'=>2))!==false)
        {
            return true;
        }else{
            $this->error='取消订单失败';
            return false;
        }

    }
    //出行订单详情
    public function detailGoodsTravel($oid)
    {
        if(!$oid)
        {
            $this->error('参数错误');
        }
        $statusarray = array('1'=>'已取消','2'=>'待付款','3'=>'待出行','4'=>'待点评','13'=>'已退款');
        $paytype = array('0'=>'微信支付','1'=>'支付宝支付','2'=>'余额支付','4'=>'积分支付');
        $data = $this->where(array('id'=>$oid))
            ->field('member_id,id,order_sn,goods_id,status,pay_type,order_type,
            pay_price,coupon_dedu,buyer_remarks,lxr_name,lxr_mobile,lxr_sfz,cxr_lists,
            get_inte,create_time,pay_time,confirm_time,hx_time,refund_t,refund_nums,rede_code,cx_time,ishx,yxq_time')
            ->find();
        if($data)
        {
            $pay_type_data = M('Pay')->field('pay_time,pay_type')->where(array('order_id'=>$oid))->find();
            if($data['pay_type'] != 4)
            {
                $data['pay_type'] = $pay_type_data['pay_type'];
            }
            $data['pay_time'] = $pay_type_data['pay_time'];

            $data['cx_time_s'] =  $data['cx_time'];
            $data['goods_name'] = M('Ticket')->where(array('id'=>$data['goods_id']))->getField('title');
            $data['cx_time'] = str_replace('-','.',date('Y-m-d', $data['cx_time']));
            $data['create_time'] = str_replace('-','.',date('Y-m-d H:i:s', $data['create_time']));
            if($data['pay_time'])
            {
                $data['pay_time'] = str_replace('-','.',date('Y-m-d H:i:s', $data['pay_time']));
            }else
            {
                $data['pay_time'] = '';
            }
            $data['hx_time'] = str_replace('-','.',date('Y-m-d H:i:s', $data['hx_time']));
            $data['refund_t'] = str_replace('-','.',date('Y-m-d H:i:s', $data['refund_t']));
            $data['pay_type'] = $paytype[$data['pay_type']];
            if($data['yxq_time'] <= time())
            {
                $data['yxq_time'] = 0;
            }

            if($data['cxr_lists'])
            {
                $cxrArray = explode('|',$data['cxr_lists']);
                $rowLx = array();
                $rowLx[] = $data['lxr_name'];
                $rowLx[] = $data['lxr_sfz'];
                foreach ($cxrArray as $item) {
                    $endData[] = explode('&',$item);
                }
                $endData[] = $rowLx;
                $newaray = $endData;
            }
            $data['cxr_lists'] = $newaray;

            $data['iszyx'] = M('Ticket')->where(array('id'=>$data['goods_id']))->getField('style');

            $data['statusTEXT'] = $statusarray[$data['status']];
            $list = M('OrderGoods')->alias('OG')
                ->field('OG.mpname,OG.prices,OG.num,OG.c_price,OG.e_price')
                ->join('LEFT JOIN __GOODS__ G on OG.goods_id=G.id')
                ->where(array('OG.order_no'=>$data['id']))
                ->select();

            $ogs = M('OrderGoods')->where(array('order_no'=>$data['id']))
                ->field('dcp,dep,mpname,c_num,e_num,e_price,c_price')->find();
            $data['mpname'] = $ogs['mpname'];
            if(!$ogs['c_num'] || !$ogs['e_num'])  // 一张票
            {
                if($list)
                {
                    foreach ($list as &$item_a) {
                        if($item_a['prices'] == 0.00)
                        {
                            $item_a['prices'] = $ogs['c_num'] > 0 ? $ogs['c_price'] : $ogs['e_price'];
                        }
                    }
                }
                $data['yzp'] = 2;
            }else
            {
                $rowD['c'] = array('c_num'=>$ogs['c_num'],'c_price'=>$ogs['c_price'],'dcp'=>$ogs['dcp']);
                $rowD['e'] = array('e_num'=>$ogs['e_num'],'e_price'=>$ogs['e_price'],'dep'=>$ogs['dep']);

                if($ogs['dcp'])
                {
                    $rowD['c']['text'] = $ogs['dcp'];
                }else
                {
                    $rowD['c']['text'] = strval($ogs['c_price']).'X'.$ogs['c_num'];
                }
                if($ogs['dep'])
                {
                    $rowD['e']['text'] = $ogs['dep'];
                }else
                {
                    $rowD['e']['text'] = strval($ogs['e_price']).'X'.$ogs['e_num'];
                }
            }

            $style = M('Ticket')->where(array('id'=>$data['goods_id']))->getField('style');
            if($style == 1)    //跟团
            {
                $data['isgt'] = 1;
            }elseif ($style == 2)
            {
                $data['isgt'] = 2;
            }

            //获取出行前可退款的天数
            $refund_day = M('Ticket')->field('refund_day,style')
                ->where(array('id'=>$data['goods_id'],'style'=>1))->find();

            if($refund_day['style'] == 1)
            {

//                if(($datum['cx_time'] - ($refund_day['refund_day'] * 86400)) < time() && $datum['status'] != 13)   //如果在退款期限内，显示退款！
//                {
//                echo $refund_day['refund_day'];

                if(($data['cx_time_s'] - ($refund_day['refund_day'] * 86400)) > time() && $data['status'] == 3)   //如果在退款期限内，显示退款！
                {
                    $data['ktk'] = 1;
                }
            }else
            {
//                var_dump($refund_day);

                if(time() < $data['yxq_time'] && $data['status'] == 3)
                {
                    $data['ktk'] = 1;
                }
            }
//            var_dump($list);
            $data['goods'] = $list;
            $typeTick = M('Ticket')->where(array('id'=>$data['goods_id']))->getField('type');
            $data['types'] = $typeTick;
            $data['goodsnew'] = $rowD;
            return $data;
        }else{
            $this->error('没有该商品');
        }
    }


    //订单详情
    public function detailGoods($oid)
    {
        if(!$oid)
        {
            $this->error('参数错误');
        }
        $statusarray = array('1'=>'已取消','2'=>'待付款','7'=>'待发货','8'=>'待收货','100'=>'已完成');

        $data = $this->where(array('id'=>$oid))
            ->field('member_id,id,pay_type,order_sn,status,receiver,receiver_address,receiver_mobile,total_amount,
            freight,pay_price,coupon_dedu,confirm_time,buyer_remarks,get_inte,create_time,pay_time,delivery_time')
            ->find();
        $pay_type_data = M('Pay')->field('pay_time,pay_type')->where(array('order_id'=>$oid))->find();
//        if($data['pay_type'] != 4)
//        {
//            $data['pay_type'] = $pay_type_data['pay_type'];
//        }


        $data['pay_time'] = $pay_type_data['pay_time']?$pay_type_data['pay_time']:$data['pay_time'];
        if($data['pay_type'] != 4)
        {
            $payTypeArray = array('0'=>'微信支付','1'=>'支付宝支付','2'=>'余额支付','4'=>'积分支付');
            $data['pay_type'] = $payTypeArray[$pay_type_data['pay_type']];
        }else
        {
            $data['pay_time'] = $data['pay_time'];
            $data['pay_type'] = '积分支付';
        }

        if($data)
        {
            $data['statusTEXT'] = $statusarray[$data['status']];
            $list = M('OrderGoods')->alias('OG')
                ->field('OG.goods_id,OG.attr_name,OG.prices,OG.num,G.goods_name,G.cover_pic')
                ->join('LEFT JOIN __GOODS__ G on OG.goods_id=G.id')
                ->where(array('OG.order_no'=>$data['id']))
                ->select();
            $data['coupon_dedu'] = $data['coupon_dedu'] ? $data['coupon_dedu'] : 0;
            $data['goods'] = $list;
            return $data;
        }else{
            $this->error('没有该商品');
        }
    }


    //获取订单信息
    public function getOrderDataById($id,$requ = null)
    {
        $data = $this->field('id,goods_id,cx_time,order_type,pay_price,order_sn,yxq_time')->where(array('id'=>$id,'is_delete'=>0,'status'=>2))->find();
        if(!$data)
        {
            $this->error('没有该商品');
        }else
        {
            $ogData = M('OrderGoods')->field('goods_id,mpname,num')->where(array('order_no'=>$id))->select();
            if($ogData)
            {
                foreach ($ogData as &$ogDatum)
                {
                    $Tname  = M('Ticket')->where(array('id'=>$ogDatum['goods_id']))->getField('title');
                    $ogDatum['mpname'] = $Tname .'-'. $ogDatum['mpname'];
                }
            }
            $data['goods'] = $ogData;
        }
        $data['yxq_time'] = str_replace('-','.',date('Y-m-d H:i:s',$data['yxq_time']));
        $style = M('Ticket')->where(array('id'=>$data['goods_id']))->getField('style');

        if($style == 1)    //跟团
        {    //cx_time
            $data['isgt'] = 1;
            $data['time'] = date('Y-m-d',$data['cx_time']);
        }elseif ($style == 2)
        {
            //yxq_time
            $data['isgt'] = 2;

            $data['time'] = $data['yxq_time'];
        }
//var_dump($data);
        if($requ == 'back')
        {
            return $data;
        }else
        {
            $this->data = $data;
        }


    }


    public  function getTravelData($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where['O.is_delete'] = 0;
        $where['O.order_type'] = $search['order_type'];
        if($search['type']>0)
        {
            if($search['type'] == 4)  //待评价
            {
                $where['O.status'] = 100;
                $where['O.is_comment'] = 0;
            }else
            {
                $where['O.status'] = $search['type'];
            }

        }
        if($search['member_id']>0)
        {
            $where['O.member_id'] = $search['member_id'];
        }else{
            $this->error('请先登录');
        }

        //跟团票超时未出行  改状态为  待点评
        $gTWhxGq = $this->alias('OD')
            ->field('OD.id')
            ->join('LEFT JOIN __TICKET__ TI on OD.jqmp_id=TI.id')
            ->where(array('OD.member_id'=>$search['member_id'],'OD.status'=>3,'OD.order_type'=>array('in','1,3,4'),'OD.cx_time'=>array('elt',time()),'TI.style'=>1))
            ->select();
        if($gTWhxGq)
        {
            foreach ($gTWhxGq as $itemS) {
                M('Order')->where(array('id'=>$itemS['id']))->save(array('status'=>100));
            }
        }


        $start = ($page-1) * $pageSize;
        $Goodsdata = $this->alias('O')
            ->field('O.cx_time,O.status,O.goods_id,O.is_comment,O.id,O.yxq_time,O.pay_price,O.order_sn,O.status,G.title')
            ->join('LEFT JOIN __TICKET__ G on O.jqmp_id=G.id')
            ->limit($start,$pageSize)
            ->where($where)
            ->order('O.id desc')
            ->select();
        if(count($Goodsdata) > 0)
        {
            foreach ($Goodsdata as &$goodsdatum)
            {
                $goodsdatum['goods_name'] = $goodsdatum['title'];
                $list = M('OrderGoods')->alias('OG')
                    ->field('OG.goods_id,OG.mpname,OG.num,OG.prices,G.title as goods_name,G.cover_pic')
                    ->join('LEFT JOIN __TICKET__ G on OG.goods_id=G.id')
                    ->where(array('OG.order_no'=>$goodsdatum['id']))
                    ->select();
                $goodsdatum['goods'] = $list;
            }
            $Htmldata = '';
            $Htmldata = $this->modifyTravelHtml($Goodsdata,$search['type']);
            return $Htmldata;
        }
    }


    private function modifyTravelHtml($data, $type)
    {
        $statusarray = array('1'=>'已取消','2'=>'待付款','3'=>'待出行','4'=>'待点评','13'=>'已退款','100'=>'已完成');
        $str = '';


        foreach ($data as $datum)
        {
            $typeTick = M('Ticket')->where(array('id'=>$datum['goods_id']))->getField('type');

            $str .= '<div class="travelOrder back-fff box_shandow2 radius4">';
            $str .= '<a href="/Mobile/MemberTravelOrder/detail/oid/'.$datum['id'].'"><div class="w-padding02 flex_dom flex_item_mid border-b-eee p_font22">';
            $str .= '<div>';


            $str .= '<b class="p_minlabel w-height03 p_font20 back-purple color-fff radius03rem wid12rem text-center w-height03">';
            if($typeTick == 1)
            {
                $str .= '景点门票';
            }else if($typeTick == 2)
            {
                $str .= '活动门票';
            }
            $str .='</b>';
            $str .= '</div>';
            $str .= '<p class="flex_1 w-paddingLeftRight02">订单号：'.$datum['order_sn'].'</p>';

            $style = M('Ticket')->where(array('id'=>$datum['goods_id']))->getField('style');

//            if ($style == 1)
//            {
//                if (time() > $datum['cx_time'] && $datum['status'] == 3 )
//                {
//                    $str .= '<i class="color-purple">待点评</i>';
//                }
//            }
//            else
            if($datum['status'] == 100 && $datum['is_comment'] == 0 )
            {
                $str .= '<i class="color-purple">待点评</i>';
            }else
            {
                $str .= '<i class="color-purple">'.$statusarray[$datum['status']].'</i>';
            }



            $str .= '</div>';
            $str .= '<div class="padding0302 border-b-eee">';

            $str .= '<h3 class="p_font24 only_line w-marginBottom02">'.$datum['goods_name'].'</h3>';
            $str .= '<div class="flex_dom flex_item_mid">';
            $str .= '<div class="flex_1">';

            foreach ($datum['goods'] as $good) {
                $str .= '<p class="p_font20 w-marginBottom01">'.$good['mpname'].'*'.$good['num'].'</p>';
            }

            $datum['goods_id'];
            //活动类型(1:跟团行,2自由行)          体：出游时间  自由行：有效期


            if($style == 1)
            {
                $str .= '<p class="p_font20 color-333">出游时间：'.date('Y-m-d',$datum['cx_time']).'</p>';
            }elseif ($style == 2)
            {
                $str .= '<p class="p_font20 color-333">有效期：'.date('Y-m-d',$datum['yxq_time']).'</p>';
            }
            $str .= '</div>';
            if ($style == 2) {
                if (time() > $datum['yxq_time'])  //	<!--过期-->
                {
                    $str .= '<i class="overdue"></i>';
                }
            }

            $str .= '</div>';
            $str .= '</div></a>';
            $str .= '<div class="w-paddingLeftRight02">';


            if ($style == 1)
            {
                if (time() > $datum['cx_time'] && $datum['status'] == 3 )  //	<!--过期-->
                {
                    $str .= '<div class="flex_dom flex_item_mid w-height07">';
                    $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                    $str .= '<div class="font0 p_btns">';
                    $str .= '<div><a onclick="gotoComment('.$datum['id'].')" class="p_btn p_font20 back-purple color-fff">去点评</a></div>';
                    $str .= '</div>';
                    $str .= '</div>';
                }
            }
            if($datum['status'] == 2)  //<!--待付款-->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';
                $str .= '<div><a onclick="canceOrder('.$datum['id'].')" class="p_btn p_font20 back-pink color-fff">取消订单</a></div>';

                $paySN = 0;
                $paySN = M('Pay')->where(array('order_id'=>$datum['id'],'order_type'=>1))->limit(1)->getField('pay_sn');
                $str .= '<div><a  onclick="goPay('."'$paySN'".')" class="p_btn p_font20 back-purple color-fff">去付款</a></div>';
                $str .= '</div>';
                $str .= '</div>';
            }else if($datum['status'] == 3 && time() < $datum['cx_time']) //	<!--待出行-->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';

                //获取出行前可退款的天数
                $refund_day = array();
                $refund_day = M('Ticket')->field('refund_day,style')
                    ->where(array('id'=>$datum['goods_id'],'style'=>1))->find();
//var_dump($refund_day);
                if($refund_day['style'] == 1)
                {
                    if(($datum['cx_time'] - ($refund_day['refund_day'] * 86400)) > time() && $datum['status'] == 3)   //如果在退款期限内，显示退款！
                    {
                        $str .= '<div><a  onclick="refund('.$datum['id'].'.'.','.$datum['pay_price'].')" class="p_btn p_font20 back-purple color-fff">申请退款</a></div>';
                    }
                }else
                {
                    if(time() < $datum['yxq_time'] && $datum['status'] == 3)
                    {
                        $str .= '<div><a  onclick="refund(' . $datum['id'] . '.' . ',' . $datum['pay_price'] . ')" class="p_btn p_font20 back-purple color-fff">申请退款</a></div>';
                    }
                }

                $str .= '</div>';
                $str .= '</div>';
            }else if($datum['status'] == 3 && time() < $datum['yxq_time']) //	<!--待出行-->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';

                //获取出行前可退款的天数
                $refund_day = array();
                $refund_day = M('Ticket')->field('refund_day,style')
                    ->where(array('id'=>$datum['goods_id'],'style'=>1))->find();
                if($refund_day['style'] == 1)
                {
                    if(($datum['yxq_time'] - ($refund_day['refund_day'] * 86400)) > time() && $datum['status'] == 3)   //如果在退款期限内，显示退款！
                    {
                        $str .= '<div><a  onclick="refund('.$datum['id'].'.'.','.$datum['pay_price'].')" class="p_btn p_font20 back-purple color-fff">申请退款</a></div>';
                    }
                }else
                {
                    if(time() < $datum['yxq_time'] && $datum['status'] == 3)
                    {
                        $str .= '<div><a  onclick="refund(' . $datum['id'] . '.' . ',' . $datum['pay_price'] . ')" class="p_btn p_font20 back-purple color-fff">申请退款</a></div>';
                    }
                }

                $str .= '</div>';
                $str .= '</div>';
            }else if($datum['status'] == 100 && $datum['is_comment'] == 0 )//<!--待点评-->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';
                $str .= '<div><a onclick="gotoComment('.$datum['id'].')" class="p_btn p_font20 back-purple color-fff">去点评</a></div>';
                $str .= '</div>';
                $str .= '</div>';
            }else if($datum['status'] == 100)//<!--已完成 -->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22"><i class="color-666">实付金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '</div>';
            }else if($datum['status'] == 13)//	<!--已退款 -->
            {
                $str .= '<div class="flex_dom flex_item_mid w-height07">';
                $str .= '<p class="p_font22"><i class="color-666">退款金额：</i><b class="color-pink">￥'.$datum['pay_price'].'</b></p>';
                $str .= '</div>';
            }
            $str .= '</div>';
            $str .= '</div>';
        }
        return $str;
    }


    public  function getListData($search=array(),$order='',$page=1,$pageSize=10)
    {
        $where['is_delete'] = 0;
        $where['order_type'] = $search['order_type'];
        if($search['type']>0)
        {
            $where['status'] = $search['type'];
        }
        if($search['member_id']>0)
        {
            $where['member_id'] = $search['member_id'];
        }else{
            $this->error('请先登录');
        }
        $start = ($page-1) * $pageSize;
        $Goodsdata = $this->order('id desc')->field('id,pay_price,order_sn,status')
            ->limit($start,$pageSize)->where($where)->select();
        if($Goodsdata)
        {
            foreach ($Goodsdata as &$goodsdatum) {

                $list = M('OrderGoods')->alias('OG')
                    ->field('OG.goods_id,G.id,OG.attr_name,OG.prices,OG.num,G.goods_name,G.cover_pic')
                    ->join('LEFT JOIN __GOODS__ G on OG.goods_id=G.id')
                    ->where(array('OG.order_no'=>$goodsdatum['id']))
                    ->select();
                $goodsdatum['goods'] = $list;
            }

            $Htmldata = $this->modifyHtml($Goodsdata,$search['type']);
            return $Htmldata;
        }
    }


    private function modifyHtml($data, $type)
    {
        $statusarray = array('1'=>'已取消','2'=>'待付款','7'=>'待发货','8'=>'待收货','100'=>'已完成');
        $str = '';
        foreach ($data as $datum)
        {
            $str .= '<div   class="back-fff w-paddingLeftRight02 w-marginBottom02">';
            $str .= '<p onclick="detalis('.$datum['id'].')" class="w-paddingTopBottom02 flex_dom flex_item_between border-b-eee p_font22">';
            $str .= '<span   class="color-666">订单号：'.$datum['order_sn'].'</span>';
//             if($type == 0)
//             {
            $str .= '<b class="color-purple">'.$statusarray[$datum['status']].'</b>';
//             }
            $str .= '</p>';
            $str .= '<a onclick="detalis('.$datum['id'].')" class="">';

            foreach ($datum['goods'] as $good)
            {
                $str .= '<div class="w-paddingTopBottom03 flex_dom border-b-eee">';
                $str .= '<div class="cartImg w-marginRight02">';
                $str .= '<img src="'.$good['cover_pic'].'" alt="" />';
                $str .= '</div>';
                $str .= '<div class="flex_1 minheight16">';
                $str .= '<h2 class="p_font24 text-ellipsis-2line">'.$good['goods_name'].'</h2>';
//              $str .= '<p class="p_font20 color-666">规格：白色 1.5米</p>';

                $str .= '<p class="p_font20 color-666">规格：'.$good['attr_name'].'</p>';
                $str .= '<div class="font0 minheight03">';
                $str .= '</div>';
                $str .= '<div class="flex_dom flex_item_mid p_font24">';
                $str .= '<p class="flex_1">';
                $str .= '<span class="color-purple w-marginRight01">￥'.$good['prices'].'</span>';
                $str .= '</p>';
                $str .= '<i>×'.$good['num'].'</i>';
                $str .= '</div>';
                $str .= '</div>';
                $str .= '</div>';
            }

            $str .= '</a>';
            $str .= '<div class="flex_dom flex_item_mid w-height07">';
            if($datum['status'] == 100 || $datum['status'] == 7)
            {
//        <!--已完成  待发货-->
                $str .= '<p class="p_font22"><i class="color-666">实付金额：</i><b class="color-purple">￥'.$datum['pay_price'].'</b></p>';
            }else if($datum['status'] == 2)
            {
//                <!--待付款-->
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-purple">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';
                $str .= '<div><a onclick="canceOrder('.$datum['id'].')" class="p_btn p_font20 back-pink color-fff">取消订单</a></div>';

                $paySN = 0;
                $paySN = M('Pay')->where(array('order_id'=>$datum['id'],'order_type'=>1))->limit(1)->getField('pay_sn');

                $str .= '<div><a  onclick="goPay('."'$paySN'".')" class="p_btn p_font20 back-purple color-fff">去付款</a></div>';
                $str .= '</div>';
            }else if($datum['status'] == 8)
            {
//                <!--待收货-->
                $str .= '<p class="p_font22 flex_1"><i class="color-666">实付金额：</i><b class="color-purple">￥'.$datum['pay_price'].'</b></p>';
                $str .= '<div class="font0 p_btns">';
                $str .= '<div><a  onclick="confirmReceipt('.$datum['id'].')" class="p_btn p_font20 back-purple color-fff">确认收货</a></div>';
                $str .= '</div>';
                $str .= '</div>';
            }
            $str .= '</div>';
            $str .= '</div>';
        }
        return $str;
    }


    public  function get_list1($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order='o.id desc';
        }else if($order=='total_amount_asc'){
            $order='o.total_amount asc';
        }else if($order=='total_amount_desc'){
            $order='o.total_amount desc';
        }else if($order=='pay_time_asc'){
            $order='o.pay_time asc';
        }else if($order=='pay_time_desc'){
            $order='o.pay_time desc';
        }
        $where=array();
        if($search['member_id']){
            $where['o.member_id']=$search['member_id'];
        }
        if($search['status']){
            $where['o.status']=$search['status'];
        }else{
            $where['o.status']=array('gt',0);
            if($search['is_pay']==1){
                $where['o.status']=array('in',array(6,7,8,100));
            }
        }
        if($search['type']==1){
            $where['o.status']=3;
        }else if($search['type']==2){
            $where['o.status']=2;
        }else if($search['type']==3){
            $where['o.status']=7;
        }else if($search['type']==4){
            $where['o.status']=8;
        }else if($search['type']==5){
            $where['o.status']=10;
        }else if($search['type']==6){
            $where['o.status']=100;
        }else if($search['type']==7){
            $where['o.status']=1;
        }
        if($search['mobile']){
            $where['m.mobile']=array('like',"%{$search['mobile']}%");
        }
        if($search['order_sn']){
            $where['o.order_sn']=array('like',"%{$search['order_sn']}%");
        }
        if($search['sales_name']){
            $where['u.user_nicename']=array('like',"%{$search['sales_name']}%");
        }
        if($search['cancel_type']){
            $where['o.cancel_type']=$search['cancel_type'];
        }

        if($search['st_time']&&$search['end_time']){
            if($search['st_time']>$search['end_time']){
                $this->error='开始时间大于结束时间';
                return false;
            }else{
                $search['end_time']=strtotime(date('Y-m-d',$search['end_time']).'23:59:59');
                $where['o.create_time']=array(array('egt', strtotime($search['st_time'])),array('elt',$search['end_time']));
                //有开始时间和结束时间
            }
        }elseif($search['st_time']&&!$search['end_time']){
            $where['o.create_time']=array('egt', strtotime($search['st_time']));//有开始时间无结束时间
        }elseif(!$search['st_time']&&$search['end_time']){
            $search['end_time']=strtotime(date('Y-m-d',$search['end_time']).'23:59:59');
            $where['o.create_time']=array('elt',$search['end_time']);//无开始时间有结束时间
        }

        if($search['st_time1']&&$search['end_time1']){
            if($search['st_time1']>$search['end_time1']){
                $this->error='开始时间大于结束时间';
                return false;
            }else{
                $search['end_time1']=strtotime(date('Y-m-d',$search['end_time1']).'23:59:59');
                $where['o.pay_time']=array(array('egt', strtotime($search['st_time1'])),array('elt',$search['end_time1']));
                //有开始时间和结束时间
            }
        }elseif($search['st_time1']&&!$search['end_time1']){
            $where['o.pay_time']=array('egt', strtotime($search['st_time1']));//有开始时间无结束时间
        }elseif(!$search['st_time1']&&$search['end_time1']){
            $search['end_time']=strtotime(date('Y-m-d',$search['end_time1']).'23:59:59');
            $where['o.pay_time']=array('elt',$search['end_time1']);//无开始时间有结束时间
        }
        if($search['order_type']===0||$search['order_type']===1){
            $where['o.order_type']=$search['order_type'];
        }
        if($search['sales_id']){
            $where['_string']=" (o.sales_id={$search['sales_id']} or (o.sales_id=0 and o.buy_type=1 and o.status=3))";
        }
        if(!$search['is_admin']){
            $where['o.is_delete']=0;
        }
        $count = $this->alias('o')
            ->join('LEFT JOIN __MEMBER__ m on m.id=o.member_id')
            ->join('LEFT JOIN __USERS__ u on u.id=o.sales_id')
            ->where($where)->count();

        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field="o.*,m.mobile,kf.name as kf_name,u.user_nicename as sales_man";
        $list = $this->alias('o')
            ->field($field)
            ->join('LEFT JOIN __MEMBER__ m on m.id=o.member_id')
            ->join('LEFT JOIN __USERS__ u on u.id=o.sales_id')
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
//        echo $this->getLastSql();
//        exit;
        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
            if($vo['pay_time']){
                $list[$key]['pay_time']=date('Y-m-d H:i:s',$vo['pay_time']);
            }else{
                $list[$key]['pay_time']='';
            }
            if($search['member_id']){
                $list[$key]['goods']=M('OrderGoods')
                    ->field('og.*,g.title,g.pic_url')
                    ->alias('og')
                    ->join("LEFT JOIN __GOODS__ g on og.goods_id=g.id")
                    ->where(array('og.order_id'=>$vo['id']))
                    ->find();
                if($list[$key]['goods']['attr_name']){
                    $attr_list=explode('-',$list[$key]['goods']['attr_name']);
                    $attr_arr=array();
                    foreach($attr_list as $k=>$v){
                        $attr_str=explode(':',$v);
                        $attr_arr[]=array('name'=>$attr_str[0],'value'=>$attr_str[1]);
                    }
                    $list[$key]['goods']['attr_list']=$attr_arr;
                }

                $is_comments=M('GoodsComments')->where(
                    array(
                        'is_delete'=>0,
                        'order_id'=>$vo['id'],
                        'goods_id'=>(int)$list[$key]['goods']['goods_id']
                    )
                )->count();
                $list[$key]['is_comments']=$is_comments;
            }

        }

        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        if($search['is_statistics']==1){
            $t_total_amount= $this->alias('o')
                ->join('LEFT JOIN __MEMBER__ m on m.id=o.member_id')
                ->join('LEFT JOIN __KF_MEMBER__ kf on kf.id=o.kf_id')
                ->join('LEFT JOIN __USERS__ u on u.id=o.sales_id')
                ->where($where)
                ->sum("o.total_amount");
            $t_pay_price= $this->alias('o')
                ->join('LEFT JOIN __MEMBER__ m on m.id=o.member_id')
                ->join('LEFT JOIN __KF_MEMBER__ kf on kf.id=o.kf_id')
                ->join('LEFT JOIN __USERS__ u on u.id=o.sales_id')
                ->where($where)
                ->sum("o.pay_price");
            $result['t_total_amount']=floatval($t_total_amount);
            $result['t_pay_price']=floatval($t_pay_price);
            $result['t_total_num']=$count;
        }
        return $result;
    }



    //订单详情
    public function detail($order_id){
        $info=$this
            ->field('o.*,kf.name as kf_name,u.user_nicename as sales_man')
            ->alias('o')
            ->join('LEFT JOIN __KF_MEMBER__ kf on kf.id=o.kf_id')
            ->join('LEFT JOIN __USERS__ u on u.id=o.sales_id')
            ->where(array(array('o.id'=>$order_id)))->find();
        if($info){
            $info['goods']=M('OrderGoods')
                ->field('og.*,g.title,g.pic_url')
                ->alias('og')
                ->join("LEFT JOIN __GOODS__ g on og.goods_id=g.id")

                ->where(array('og.order_id'=>$order_id))
                ->find();
            if($info['goods']['attr_name']){
                $attr_list=explode('-',$info['goods']['attr_name']);
                $attr_arr=array();
                foreach($attr_list as $key=>$vo){
                    $attr_str=explode(':',$vo);
                    $attr_arr[]=array('name'=>$attr_str[0],'value'=>$attr_str[1]);
                }
                $info['goods']['attr_list']=$attr_arr;
            }
            $goods_id=$info['goods']['goods_id'];
            $info['log']=M('OrderLog')->where(array('is_delete'=>0,'order_id'=>$order_id))->select();
            $comments=M('GoodsComments')->where(array('is_delete'=>0,'order_id'=>$order_id,'goods_id'=>(int)$goods_id))->find();
            if($comments['pic_list']){
                $comments['pic_list']=explode('|',$comments['pic_list']);
            }
            $info['comments']=$comments;
        }
        return $info;
    }



    //获取用户的消费记录，包含获得的佣金
    public  function get_order_commission($search=array(),$order='',$page=1,$pageSize=10){
        if(empty($order)){
            $order='o.id desc';
        }
        $where=array();
        $where['o.member_id']=$search['member_id'];
        $where['o.status']=100;//只统计已完成订单
        $where['o.order_type']=array('neq',2);//去掉积分订单
        $where['o.is_delete']=0;
        $count = $this->alias('o')
            ->where($where)->count();
        if($pageSize==0&&$count>50000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field="o.pay_price,o.create_time,getCommissionTotalPriceByOrder(o.id,".$search['get_member_id'].") as commission_price";
        $list = $this->alias('o')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
//        echo $this->getLastSql();
//        exit;
        foreach($list as $key=>$vo){
            $list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
        }
        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }
}