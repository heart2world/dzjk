<?php
/**
 * Created by PhpStorm.
 * User: 杨瑜堃
 * Date: 2017/10/23
 * Time: 17:26
 */

namespace Common\Model\Label;

use Common\Model\CommonModel;

class LabelModel extends CommonModel{

    protected $_validate = array(
        array('name','require','标签名必须填写！',1),
        array('name','1,6','标签长度1-6字',1,'length'),
        array('name','check_unigue','标签名已经存在！',0,'callback'),
        array('type','require','请选择表标签类型！',1),
    );

    //检查标签名字是否唯一
    public function check_unigue($name){
        $where=array();
        $where['name']=$name;
        $total=$this->where($where)->count();
        if($total){
            return false;
        }else{
            return true;
        }
    }

    //获取list
    public function get_list($data,$p=1,$page=0){
        $where=array();
        if($data['type']){
            $where['type']=$data['type'];
        }
        $count=$this->where($where)->count();
        $list=$this->where($where)->page($p,$page)->order('id desc')->select();
        $result['list']=$list;
        $result['p']=$p;
        $result['total_page']=ceil($count/$page);
        return $result;
    }

    //批量删除
    public function deleteBatch($ids){
        $ids=ids_to_ids($ids);
        if($ids){
            $res=$this->where(array('id'=>array('in',$ids)))->delete();
            if($res){
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
}