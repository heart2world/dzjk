<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/8
 * Time: 10:15
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;
use Common\Model\DestinationModel;

class DestinationController extends AdminbaseController
{
    protected $DestinationModel;
    function _initialize()
    {
        parent::_initialize();
        $this->DestinationModel = new DestinationModel();
    }

    public function index(){
        if(IS_AJAX){
            $p = I('p');
            $list = $this->DestinationModel->get_list($p);
            $list['province'] =  $this->get_region_list();
            $this->ajaxReturn($list);
        }else{
            $this->display();
        }
    }

    //添加分类
    public function add_class(){
        $data = I();
        if(!empty($data['id'])){
            $res = $this->DestinationModel->save($data);
            if(!$res){
                $this->ajaxReturn(array('status'=>1,'info'=>'您未做任何修改'));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>"修改成功"));
            }
        }else{
            $res = $this->DestinationModel->add_class($data);
            if($res !== true){
                $this->ajaxReturn(array('status'=>0,'info'=>$this->DestinationModel->getError()));
            }else{
                $this->ajaxReturn(array('status'=>1,'info'=>"添加成功"));
            }
        }
    }
    public function delete(){
        $id = I('id');
        $id = trim($id,',');
        $id = explode(',',$id);
        $child = array();
        foreach($id as $v){
            $result = M('Destination')->where(array('pid'=>$v,'is_delete'=>0))->find();
            if($result){
                $name = M('Destination')->where(array('id'=>$v))->getField('name');
                Array_push($child,$name);
            }
        }
        if($child){
            $child = join('、',$child);
            $this->ajaxReturn(array('status'=>-1,'info'=>$child));
        }
        $res = M('Destination')->where(array('id'=>array('in',$id)))->setField('is_delete',1);
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'删除成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
        }
    }
    //获取单个详情
    public function get_one(){
        $id = I('id');
        $res = M('Destination')->where(array('id'=>$id))->find();
        $res['parent_name'] = M('Destination')->where(array('id'=>$res['pid']))->getField('name');
        $this->ajaxReturn($res);
    }
    //目的地图片设置
    public function default_img(){
        $data = get_default_img(false);
        //var_dump($data);
        $this->assign('data',$data);
        $this->display();
    }

    //添加、编辑图片
    public function edit(){
        $data = I();
        //var_dump($data);die;
        $arr = array();
        if($data['type']==1){
            $arr['logo_img'] = $data['logo'];
        }
        if($data['type']==2){
            $arr['main_pic_img'] = $data['main_pic'];
        }
        $res = M('Options')->where(array('option_name'=>'default_img'))->find();
        if($res){
            $Array = json_decode($res['option_value'],true);
            $Array1 = $Array;
            if($data['type']==1){
                $Array['logo_img'] = $data['logo'];
                $Array = json_encode($Array);
                $res = M('Options')->where(array('option_name'=>'default_img'))->setField('option_value',$Array);
                if($res){
                    if($Array1['logo_img']){
                        $this->ajaxReturn(array('status'=>1,'info'=>"修改成功"));
                    }else{
                        $this->ajaxReturn(array('status'=>1,'info'=>"添加成功"));
                    }
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
                }
            }
            if($data['type']==2){
                $Array['main_pic_img'] = $data['main_pic'];
                $Array = json_encode($Array);
                $res =M('Options')->where(array('option_name'=>'default_img'))->setField('option_value',$Array);
                if($res){
                    if($Array1['main_pic_img']){
                        $this->ajaxReturn(array('status'=>1,'info'=>"修改成功"));
                    }else{
                        $this->ajaxReturn(array('status'=>1,'info'=>"添加成功"));
                    }
                }else{
                    $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
                }
            }
        }else{
            $option['option_name'] = 'default_img';
            $option['option_value'] = json_encode($arr);
            $res = M('Options')->add($option);
            if($res){
                $this->ajaxReturn(array('status'=>1,'info'=>"添加成功"));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'添加失败'));
            }
        }
    }

    public function get_pro(){
        $id = I('id');
        $list = $this->get_region_list($id);
        $this->success($list);
    }

}