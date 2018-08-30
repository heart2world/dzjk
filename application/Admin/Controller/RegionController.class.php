<?php
/**
城市区域类
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;


class RegionController extends AdminbaseController
{

    function _initialize()
    {
        parent::_initialize();
    }

    //获取城市
    public function get_city(){
        $id = I('id');
        $where['is_delete']=0;
        if($id){
            $where['parent_id']=intval($id);
        }else{
            $where['region_type']=1;
        }
        $list=M('Region')->where($where)->order('first_pinyin asc')->select();
        $this->success($list) ;
    }
}