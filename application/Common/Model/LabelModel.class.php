<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 10:45
 */

namespace Common\Model;


class LabelModel extends CommonModel
{
    public  function get_list()
    {
        $res = $this->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();
        return $res;
    }
    public  function getNameById($id)
    {
        $res = $this->where(array('id'=>$id))->getField('name');
        return $res;
    }
}