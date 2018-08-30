<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10
 * Time: 13:58
 */

namespace Common\Model;

//点赞记录

class ZanLogModel extends CommonModel
{


    /**
     * @param  $data数组
     * @return bool 收藏状态
     */
    public function add_zan($data){
        if(!$data['member_id']){
            $this->error="请登录操作";
            return false;
        }
        if(!$data['to_id']){
            $this->error="点赞信息ID不能为空";
            return false;
        }
        $where['to_id']=$data['to_id'];
        $where['type']=$data['type'];
        $where['member_id']=$data['member_id'];
        $id=$this->where($where)->getField('id');
        if($id){
            $this->error="已经点赞，不能重复点赞";
            return false;
        }else{
            $where['create_time']=time();
            $res=$this->add($where);
            if($res&&$data['type']==0){
                M('MemberDynamic')->where(array('id'=>$data['to_id']))->setInc('thumbs_up');
            }
        }
        if($res!==false){
            return true;
        }else{
            $this->error="点赞失败";
            return false;
        }
    }


    /**
     * @param $to_id 信息ID
     * @param $member_id 会员ID
     * * @param $type 信息类型 默认0攻略
     * @return bool 删除状态
     */
    public function delete_zan($to_id,$member_id,$type=0){
        if(!$member_id){
            $this->error="请登录操作";
            return false;
        }
        $where['to_id']=$to_id;
        $where['type']=$type;
        $where['member_id']=$member_id;
        $res=$this->where($where)->delete();
        if($res){
            if($res&&$type==0){
                M('MemberDynamic')->where(array('id'=>$to_id))->setDec('thumbs_up');
            }
            return true;
        }else{
            $this->error="取消点赞失败";
            return false;
        }
    }
}