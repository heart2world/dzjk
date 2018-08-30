<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Think\Page;

/**
 * 网站设置
 * Class SettingController
 * @package Admin\Controller
 */
class SettingController extends AdminbaseController{

	protected $options_model;

	function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");

	}

	//dynamic  Adve  Cont


    function site(){
        C(S('sp_dynamic_config'));//加载动态配置
        $option=$this->options_model->where("option_name='site_options'")->find();
        if($option){

            $this->assign((array)json_decode($option['option_value']));
            $this->assign("option_id",$option['option_id']);
        }
//        $this->assign("info",json_decode($option['option_value'],true));
        $this->display();
    }

    function lablist(){
        if(IS_AJAX)
        {
            $name = I('post.name');
            $status = I('post.status');
            $id = I('post.id')?I('post.id'):0;
            $nameIS = M('Label')->field('name,id')->where(array('name'=>$name,'is_del'=>0))->find();
            if($nameIS['name'] == $name && $id != $nameIS['id'])
            {
                $this->error('名称已存在');
            }
            $data['name'] = $name;
            $data['status'] = $status;
            if($id)
            {
                M('Label')->where(array('id'=>$id))->save($data);
            }else
            {
                $data['t'] = time();
                M('Label')->add($data);
            }
            $this->success('ok');
        }else
        {
            $data = M('Label')->where(array('is_del'=>0))->select();
            if($data)
            {
                foreach ($data as &$datas)
                {
                    $datas['t'] = date('Y-m-d H:i:s',$datas['t']);
                    $datas['statusText'] = $datas['status'] > 0 ? '显示' : '隐藏';

                    $datas['artis'] = M('Article')->where(array('label'=>$datas['id'],'is_delete'=>0,'type'=>0))
                    ->count();

                    $datas['docts'] = M('Member')
                        ->where(array('zy'=>$datas['id'],'is_delete'=>0,'types'=>2,'is_ok'=>1))
                        ->count();
                    $datas['users'] = 0;
//                        M('Member')
//                        ->where(array('zy'=>$datas['id'],'is_delete'=>0,'types'=>2))
//                        ->count();
                }
            }
            $this->data = $data;
            $this->display();
        }
    }

    function deletelab(){
        if(IS_AJAX)
        {
            $id = I('post.id');
            $docts = M('Member')
                ->where(array('zy'=>$id,'is_delete'=>0,'types'=>2,'is_ok'=>1))
                ->count();

            if($docts > 0)
            {
                $this->error('该标签下有医生，不能删除!');
            }else
            {
                $isOk = M('Label')->where(array('id'=>$id))->save(array('is_del'=>1));
                if($isOk)
                {
                    $this->success('删除成功');
                }
            }

        }
    }
    public function labinfo()
    {

        $id = intval(I('id'));

        $datas = M('Label')->where(array('id'=>$id))->find();
        $datas['t'] = date('Y-m-d H:i:s',$datas['t']);
        $datas['statusText'] = $datas['status'] > 0 ? '显示' : '隐藏';

        $datas['artis'] = M('Article')->where(array('label'=>$datas['id'],'is_delete'=>0,'type'=>0))
            ->count();

        $datas['docts'] = M('Member')
            ->where(array('zy'=>$datas['id'],'is_delete'=>0,'types'=>2))
            ->count();

        $datas['users'] = 0;

        $p = I('p')?I('p'):1;
        $pageSize = 2;




        $mwhere['types'] = 2;
        $mwhere['zy'] = $id;
        $mwhere['is_delete'] = 0;


        $count      = M('Member')->where($mwhere)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,$pageSize);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        $doList = M('Member')
            ->page($p,$pageSize)
            ->field('id,nickname,fss,isshow_lab,id')
            ->where($mwhere )
            ->select();
        if($doList)
        {
            foreach ($doList as &$item)
            {
                $item['as'] =  M('Article')->where(array('author'=>$item['id']))->count();
                $item['isshow_lab'] =  $item['isshow_lab'] == 1 ?  "隐藏" : "显示";
            }
        }

        $datas['doList'] = $doList;

        $this->info = $datas;
        $this->display();
    }


    public function swMemberLab()
    {
        $id = intval(I('id'));
        if($id <= 0)
        {
            $this->error('参数错误');
        }


        $isshow_lab = M('Member')->where(array('id'=>$id))->getField('isshow_lab');
        if($isshow_lab == 0)
        {
            $st = 1;
        }else if($isshow_lab == 1)
        {
            $st = 0;
        }

        $res = M('Member')->where(array('id'=>$id))->save(array('isshow_lab'=>$st));
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->error('参数错误');
        }

    }

    //==========================================================




    function range(){
        C(S('sp_dynamic_config'));//加载动态配置
        $option=$this->options_model->where("option_name='range_options'")->find();
        if($option){
            $this->assign((array)json_decode($option['option_value']));
            $this->assign("option_id",$option['option_id']);
        }
        $region_list=M('Region')->where(array('parent_id'=>322,'is_delete'=>0))->select();
        $this->assign('region_list',$region_list);
        $this->display();
    }



	function site_post(){
		if (IS_POST) {
			if(isset($_POST['option_id'])){
				$data['option_id']=intval($_POST['option_id']);
			}
			$data['option_name']="site_options";
			$data['option_value']=json_encode($_POST['options']);
			if($this->options_model->where("option_name='site_options'")->find()){
				$r=$this->options_model->where("option_name='site_options'")->save($data);
			}else{
				$r=$this->options_model->add($data);
			}
			if ($r!==false) {
                write_log('修改网站配置','系统设置');
				$this->success("保存成功！");
			} else {
				$this->error("保存失败！");
			}
		}
	}

	

	function password(){
		$this->display();
	}

	

	function password_post(){
		if (IS_POST) {
			if(empty($_POST['old_password'])){
				$this->error("原始密码不能为空！");
			}

			if(empty($_POST['password'])){
				$this->error("新密码不能为空！");
			}

			$user_obj = D("Common/Users");
			$uid=get_current_admin_id();
			$admin=$user_obj->where(array("id"=>$uid))->find();
			$old_password=$_POST['old_password'];
			$password=$_POST['password'];
			if(sp_compare_password($old_password,$admin['user_pass'])){
				if($_POST['password']==$_POST['repassword']){
					if(sp_compare_password($password,$admin['user_pass'])){
						$this->error("新密码不能和原始密码相同！");
					}else{
						$data['user_pass']=sp_password($password);
						$data['id']=$uid;
						$r=$user_obj->save($data);
						if ($r!==false) {
                                write_log('修改个人密码','系统设置');
							$this->success("修改成功！");
						} else {
							$this->error("修改失败！");
						}
					}
				}else{
					$this->error("密码输入不一致！");
				}
			}else{
				$this->error("原始密码不正确！");
			}
		}
	}

	//清除缓存

	function clearcache(){
		sp_clear_cache();
		$this->display();

	}
}