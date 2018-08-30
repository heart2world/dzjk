<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10
 * Time: 9:25
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;
use Common\Model\AItemModel;
use Common\Model\DestinationModel;

class AItemController extends AdminbaseController
{
    protected $AItemModel;
    protected $DestinationModel;
    function _initialize()
    {
        parent::_initialize();
        $this->AItemModel = new AItemModel();
        $this->DestinationModel = new DestinationModel();
    }

    public function index(){
        if(IS_AJAX){
            $data = I();
            $list = $this->AItemModel->examine_list($data);
            $list['province'] =  $this->get_region_list();
            $list['destination'] = $this->DestinationModel->get_d_list();
            $this->ajaxReturn($list);
        }else{
            $this->display();
        }
    }

    //详情
    public function get_info(){
        $data['id'] = I('id');
        $list = $this->AItemModel->examine_list($data);
        $arr = get_site_options();
        $list['list'][0]['a_complete_day'] = $arr['a_complete_day'];
        $this->assign('list',$list['list']);
        $this->display();
    }

    //删除
    public function delete(){
        $id = I('id');
        $id = trim($id,',');
        $id = explode(',',$id);
        $res = M('AItem')->where(array('id'=>array('in',$id)))->setField('is_delete',1);
        if($res){
            $this->ajaxReturn(array('status'=>1,'info'=>'删除成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
        }
    }
    //审核
    public function examine(){
        $data = I();
        if(empty($data['status'])){
            $this->ajaxReturn(array('status'=>2,'info'=>"系统错误"));
        }
        M('AItem')->startTrans();
        if($data['status'] == 1){
            $arr['status'] = 1;
            $arr['item_status'] = 1;
            $res = M('AItem')->where(array('id'=>$data['id']))->setField($arr);
            if($res!==false){
                $res_apply = M('AItemApply')->where(array('item_id'=>$data['id']))->setField('status',1);
                if($res_apply!==false){
                    M('AItem')->commit();
                    $this->ajaxReturn(array('status'=>1,'info'=>"通过成功"));
                }else{
                    M('AItem')->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>"数据更新失败"));
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"数据更新失败"));
            }
        }
        if($data['status'] == 2){
            if(empty($data['refuse_content'])){
                $this->ajaxReturn(array('status'=>2,'info'=>"请填写拒绝原因"));
            }
            $res = M('AItem')->save($data);
            if($res!==false){
                $res_apply = M('AItemApply')->where(array('item_id'=>$data['id']))->setField('status',2);
                if($res_apply!==false){
                    M('AItem')->commit();
                    $this->ajaxReturn(array('status'=>1,'info'=>"拒绝成功"));
                }else{
                    M('AItem')->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>"数据更新失败"));
                }
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>"数据更新失败"));
            }
        }
    }
    //已通过列表
    public function item_list(){
        if(IS_AJAX){
            $data = I();
            $data['status'] = 1;
            $list = $this->AItemModel->item_list($data);
            $list['province'] =  $this->get_region_list();
            $list['destination'] = $this->DestinationModel->get_d_list();
            $this->ajaxReturn($list);
        }else{
            $this->display();
        }
    }
    //已通过列表的详情
    public function get_item_info(){
        $data['id'] = I('id');
        $list = $this->AItemModel->item_list($data);
        $arr = get_site_options();
        $list['list'][0]['a_complete_day'] = $arr['a_complete_day'];
        $extent = $this->AItemModel->get_extent($data['id']);
        $this->assign('extent',$extent);
        $this->assign('extent_j',json_encode($extent));
        $this->assign('list',$list['list']);
        $this->display();
    }

    //查检测组队状态方法
    public function select_item(){

    }

    //获取所有用户
    public function get_user(){
        $data = I();
        $list = $this->AItemModel->get_user($data);
        $this->ajaxReturn($list);
    }

    //获取省份
    public function get_pro(){
        $id = I('id');
        $list['list'] = $this->get_region_list($id);
        $this->ajaxReturn($list);
    }

    public function get_destination(){
        $id = I('id');
        $list['list'] = $this->DestinationModel->get_d_list($id);
        $this->ajaxReturn($list);
    }
}