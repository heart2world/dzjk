<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 10:45
 */

namespace Common\Model;


class AdveModel extends CommonModel
{
    public  function get_list($data,$pageSize){


        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($pageSize)?10:$pageSize;

        $where=array();


        if(!empty($data['kw']))
        {
            $where['ggz'] = array('like',"%{$data['kw']}%");
        }

        if(!empty($data['zsqy']) && $data['zsqy'] != 'a')
        {
            $where['zsqy'] = array('like',"%{$data['zsqy']}%");
        }
        if(!empty($data['label']) && $data['label'] != 'a')
        {
            $where['lab'] = $data['label'];
        }
        if(!empty($data['status']) && $data['status'] != 'a'){
            if($data['status'] == 3)
            {
                $where['status'] = array('notin','1,2');
            }else
            {
                $where['status'] = $data['status'];
            }

        }
//        var_dump($data['endtime']);
//        var_dump($data['starttime']);
        if($data['starttime']&&$data['endtime']){
            if($data['starttime']>$data['endtime']){
                $this->error = '开始时间大于结束时间';
                return false;
            }else{
                $where['ent'] = array('elt',strtotime($data['endtime'].' 23:59:59'));
                $where['st'] = array('egt',strtotime($data['starttime']));
            }
        }elseif($data['starttime']&&!$data['endtime']){
            $where['st'] = array('egt',strtotime($data['starttime']));//有开始时间无结束时间
        }elseif(!$data['starttime']&&$data['endtime']){
            $where['ent'] = array('elt',strtotime($data['endtime'].' 23:59:59'));
            $where['st'] = array('elt',$data['endtime']);//无开始时间有结束时间
        }
//        var_dump($where);
//        if(!empty($data['kw'])){
//            $where['title'] = array('like',"%{$data['kw']}%");
//        }
//        var_dump($where);
        $where['is_delete'] = 0;
        $list = $this
            ->where($where)
            ->order('id desc')
            ->page( $data['p'],$data['pageNum'])
            ->select();
        if($list)
        {
            foreach ($list as &$item) {

                if($item['st'] <= time())
                {
                    $this->where(array('id'=>$item['id']))->save(array('status'=>1));
                }
                if($item['ent'] <= time())
                {
                    $this->where(array('id'=>$item['id']))->save(array('status'=>2));
                }

                $item['st'] = date('Y-m-d',$item['st']);
                $item['lab_n'] = $item['lab'];
                $item['lab'] = D('Label')->where(array('id'=>$item['lab']))->getField('name');
                $item['zsqy_n'] = $item['zsqy'];
                $arrays = explode(',',$item['zsqy']);
                $str = '';
                if(in_array('1',$arrays))
                {
                    $str .= '首页'.',';
                }
                if(in_array('2',$arrays))
                {
                    $str .=  '首页标签'.',';
                }
                if(in_array('3',$arrays))
                {
                    $str .=  '文章详情';
                }
                $item['zsqy'] = $str;
                $item['ent'] = date('Y-m-d',$item['ent']);
                $status = array('0'=>'未开始','1'=>'上架','2'=>'下架');
                $item['status'] =  $status[$item['status']];
            }
        }

        $count = $this->where($where)->count();
        $total_page = ceil($count / $data['pageNum']);
        $result['list'] = $list;
        $result['p'] = $data['p'] ;
        $result['total'] = $count;
        $result['pagesize'] = $data['pageNum'];
        $result['total_page'] = $total_page;
        return $result;
    }

}