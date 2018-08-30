<?php

namespace Mobile\Controller;

use Mobile\Controller\MemberbaseController;
use Common\Model\MemberAddrModel;
use Common\Model\RegionModel;

//收货地址控制器
class AddressController extends MemberbaseController
{

    protected $member_addr_model;

    public function __construct()
    {
        parent::__construct();
        $this->member_addr_model=new MemberAddrModel();
    }


    public function goodsaddr()
    {
        if(IS_AJAX){
            $p = I('p',1,'intval');
            $page_size = $this->pageSize;
            $search['member_id'] = $this->member['member_id'];
            $list = $this->member_addr_model->get_list($search,'',$p,$page_size);
            $this->success($list);
        }else{
            $orderurl = I('orderurl','');
            $this->assign('orderurl',$orderurl);
            $this->display();
        }
    }

    //添加|编辑收货地址
    public function addaddr(){
        if(IS_AJAX){
            $data=I("post.");
            if(!$data['is_default'])
            {
                $data['is_default'] = 0;
            }
            if($this->member_addr_model->create($data)){
                $this->member_addr_model->member_id=$this->member['member_id'];
                if($data['id'])   //更新
                {
                    $backid = $this->member_addr_model->save();
                }else
                {
                    $backid = $this->member_addr_model->add();
                }

                if($backid != false)
                {
                    $baid = $data['id']?$data['id']:$backid;
                    if($data['is_default'] == 1)  //设为默认
                    {
                        M('MemberAddr')->where(array('member_id'=>$this->member['member_id'],'is_default'=>1))->save(array('is_default'=>0));
                        M('MemberAddr')->where(array('id'=>$baid))->save(array('is_default'=>1));
                    }
                    if($data['id'])   //更新
                    {
                        $this->success("更新成功");
                    }else
                    {
                        $this->success("新增成功");
                    }
                }else{
                    if($data['id'])   //更新
                    {
                        $this->error("更新失败");
                    }else
                    {
                            $this->error("新增失败");
                    }
                }
            }else{
                $this->error($this->member_addr_model->getError());
            }
        }else{
            $region_model=new RegionModel();
            $city_list=$region_model->get_all_three();

            if(I('get.id'))
            {
                $this->info = $this->member_addr_model->get_info(I('get.id'));
            }

            $this->assign("city_list",$city_list);
            $this->display();
        }
    }




    public function editaddr(){
        $region_model=new RegionModel();
        if(I('get.id'))
        {
            $this->info = $this->member_addr_model->get_info(I('get.id'));
        }
        $city_list=$region_model->get_all_three();
        $this->assign("city_list",$city_list);
        $this->display();
    }

    //收货地址管理
    public function index()
    {
        if(IS_AJAX){
            $p=I('p',1,'intval');
            $page_size=$this->pageSize;
            $search['member_id']=$this->member['member_id'];
            $list=$this->member_addr_model->get_list($search,'',$p,$page_size);
            $this->success($list);
        }else{
            $this->display();
        }

    }


    //添加收货地址
    public function add(){
        if(IS_AJAX){
            $data=I("post.");
            if($this->member_addr_model->create($data)){
                $this->member_addr_model->member_id=$this->member['member_id'];
                if($this->member_addr_model->add()){
                    $this->success("新增成功");
                }else{
                    $this->error("新增失败");
                }
            }else{
                $this->error($this->member_addr_model->getError());
            }
        }else{
            $range_options=getOptions("range_options");
            $region_model=new RegionModel();
            if($range_options['is_all']==1){
                $city_list=$region_model->get_all_three();
            }else{
                $city_list=$region_model->get_where_three(26,322,$range_options['luoji_area_id']);
            }
            $this->assign("city_list",$city_list);
            $this->display();
        }
    }

    //删除地址
    public function delete(){
        $id=I('id',0,"intval");
        if($id){
            if($this->member_addr_model->delete_info($id,$this->member['member_id'])){
                $this->success("删除成功");
            }else{
                $this->error($this->member_addr_model->getError());
            }
        }
    }

    //设置默认
    public function set_default(){
        $id=I('id',0,"intval");
        if($id){
            if($this->member_addr_model->set_default($id,$this->member['member_id'])){
                $this->success("设置成功");
            }else{
                $this->error($this->member_addr_model->getError());
            }
        }
    }


    //编辑
    public function edit(){
        $id=I('id',0,"intval");
        if(IS_AJAX){
            $data=I("post.");
            if($this->member_addr_model->create($data)){
                $this->member_addr_model->member_id=$this->member['member_id'];
                if($this->member_addr_model->save()!==false){
                    $this->success("编辑成功");
                }else{
                    $this->error("编辑失败");
                }
            }else{
                $this->error($this->member_addr_model->getError());
            }
        }else{
            $range_options=getOptions("range_options");
            $region_model=new RegionModel();
            if($range_options['is_all']==1){
                $city_list=$region_model->get_all_three();
            }else{
                $city_list=$region_model->get_where_three(26,322,$range_options['luoji_area_id']);
            }
            $this->assign("city_list",$city_list);
            $info=$this->member_addr_model->get_info($id);
            $this->assign("info",$info);
            $this->display();
        }
    }
}