<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 *      会员类
 * Date: 2016/6/5 0005
 * Time: 上午 10:19
 */

namespace Common\Model;

use Common\Model\CommonModel;

class HelpModel extends CommonModel
{

    protected $_validate = array(
        array('title','require','标题必须填写！',1),
        array('title','1,30','标题长度1-30字',1,'length'),
        array('title','check_unigue','标题已经存在！',0,'callback'),
        array('thumb','check_thumb','请上传图片！',0,'callback'),
    );

    protected $_auto = array (
        array('create_time','time',1,'function'),
        array('update_time','time',2,'function'),
    );


    //检查名称唯一
    public function check_unigue($title){
        $id=I('id',0,'intval');
        $where=array();
        $where['is_delete']=array('eq',0);
        $where['title']=$title;
        if($id){
            $where['id']=array('neq',$id);
        }
        $total=$this->where($where)->count();
        if($total){
            return false;
        }else{
            return true;
        }
    }

    public  function get_list($search=array(),$order='',$page=1,$pageSize=10,$field=array()){

        if(empty($order))
        {
            $order = 'id desc';
        }
        $where = array();
        if($search['keywords'])
        {
            $where['title'] = array('like',"%{$search['keywords']}%");
        }
        if($search['st_time']&&$search['end_time']){
            if($search['st_time']>$search['end_time']){
                $this->error = '开始时间大于结束时间';
                return false;
            }else{
                $search['end_time'] = strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
                $where['create_time'] = array(array('egt',$search['st_time']),array('elt',$search['end_time']));
                //有开始时间和结束时间
            }
        }elseif($search['st_time']&&!$search['end_time']){
            $where['create_time'] = array('egt',$search['st_time']);//有开始时间无结束时间
        }elseif(!$search['st_time']&&$search['end_time']){
            $search['end_time'] = strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
            $where['create_time'] = array('elt',$search['end_time']);//无开始时间有结束时间
        }
        $where['is_delete'] = 0;
        $count = $this->where($where)->count();
        if($pageSize == 0 && $count > 10000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        if(!$field)
        {
            $field ='*';
        }
        $list = $this->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        if($list)
        {
            if($field == '*')
            {
                foreach($list as $key=>&$vo)
                {
                    $vo['create_time'] = date('Y-m-d H:i:s',$vo['create_time']);
                    $vo['opt_id'] = D('Users')->where(array('id'=>$vo['opt_id']))->getField('user_nicename');
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

    public function get_info($id){
        $info=$this->where(array('id'=>$id))->find();
        return $info;
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
}