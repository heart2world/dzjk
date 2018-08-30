<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\Auth\AuthGroupModel;
class UserController extends AdminbaseController{

	protected $users_model,$role_model;

	

	function _initialize() {

		parent::_initialize();

		$this->users_model = D("Common/Users");

		$this->role_model = new AuthGroupModel();

	}




    function index(){
        $role_id = I('role_id');

        $keyword=I('keyword');

        $search['keyword']=$keyword;
       $map=array();
        if(!empty($keyword)){

            $where['u.user_login']  = array('like', "%$keyword%");
            $where['u.user_nicename']  = array('like',"%$keyword%");
            $where['u.mobile']  = array('like',"%$keyword%");

//            $group_id=M('AuthGroup')->where(array('status=1','title'=>array('like',"%$keyword%")))->getField('id',true);
//            if($group_id){
//                $user_id=M('AuthGroupAccess')->where(array('type'=>1,'group_id'=>array('in',$group_id)))->getField('uid',true);
//                if($user_id){
//                    $where['id']  = array('in',$user_id);
//                }
//            }
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if(!empty($role_id)  && $role_id>0){
            $map['ag.group_id'] = $role_id;
            $this->assign('role_id',$role_id);
        }


        $count=$this->users_model
            ->join('LEFT JOIN __AUTH_GROUP_ACCESS__ ag on ag.uid = u.id')
            ->join('LEFT JOIN __AUTH_GROUP__ g on ag.group_id = g.id')
            ->alias('u')->where($map)->count();

        $page = $this->page($count, 20);

        $users = $this->users_model
            ->alias('u')
//            ->field('u.user_login,u.user_nicename,u.update_time,u.id,u.is_administrator,ag.group_id,g.title,
//					(select count(b.uid) from ehecd_business b where u.id = b.uid ) as is_business
//				')
            ->field('u.user_login,u.user_status,u.mobile,u.user_nicename,u.update_time,u.id,u.is_administrator,ag.group_id,g.title')
            ->join('LEFT JOIN __AUTH_GROUP_ACCESS__ ag on ag.uid = u.id')
            ->join('LEFT JOIN __AUTH_GROUP__ g on ag.group_id = g.id')
            ->where($map)
//            ->where($role)
            ->order("update_time DESC,id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        //echo $this->users_model->getLastSql();
        foreach($users as $k=>$y){
            if($y['is_administrator']){
                $users[$k]['title'] = '超级管理员';
            }
            $users[$k]['user_status_text'] = $y['user_status'] == 1 ? '正常' : '冻结';

        }
        /*$auth_group_access_news=M('AuthGroupAccess');
        foreach($users as $key=>$vo){
            $roles_src=$auth_group_access_news->alias('r')
                ->field('agn.title')
                ->join('LEFT JOIN __AUTH_GROUP__ agn on r.group_id=agn.id')
                ->where(array('r.type'=>1,'r.uid'=>$vo['id']))->select();
            $roles=array();
            if($vo['is_administrator']){
                $roles[]='超级管理员';
            }else{
                foreach ($roles_src as $r){
                    $roles[]=$r['title'];
                }
            }
            $users[$key]['role']=implode(',',$roles);
        }*/
        $this->assign("page", $page->show('Admin'));
        //$this->assign("roles",$roles);
        $this->assign("users",$users);
        $this->assign("search", $search);

        $roles=$this->role_model->where("status=1 and type=1")->order("id desc")->select();

        $this->assign("all_roles",$roles);

        $this->display();

    }

    function index111111(){
        $role_id = I('role_id');
        $search['keyword'] = $keyword = I('keyword');

        $map = array();
        if(!empty($keyword))
        {
            $where['u.user_nicename|u.user_login|u.mobile']  = $keyword;
        }

        var_dump($keyword);
        var_dump($role_id);


        if(!empty($role_id)  && $role_id>0){
            $role['ag.group_id'] = $role_id;

            $map['ag.group_id'] = $role_id;

            $this->assign('role_id',$role_id);
        }
//        $map['u.user_type']=1;

        $count=M('Users')
            ->join('LEFT JOIN __AUTH_GROUP_ACCESS__ ag on ag.uid = u.id')

            ->where($map)->count();


        $page = $this->page($count, 20);

        $users = M('Users')
            ->alias('u')
            ->field('u.user_login,u.user_status,u.mobile,u.user_nicename,u.update_time,u.id,u.is_administrator,ag.group_id,g.title')

            ->join('LEFT JOIN __AUTH_GROUP_ACCESS__ ag on ag.uid = u.id')

            ->join('LEFT JOIN __AUTH_GROUP__ g on ag.group_id = g.id')

            ->where($map)

            ->where($role)

            ->order("update_time DESC,id desc")

            ->limit($page->firstRow . ',' . $page->listRows)

            ->select();



        foreach($users as $k=>&$y){
            if($y['is_administrator']){
                $y['title'] = '超级管理员';
            }
            $y['user_status_text'] = $y['user_status'] == 1 ? '正常' : '冻结';




        }
        $this->assign("page", $page->show('Admin'));
        //$this->assign("roles",$roles);
        $this->assign("users",$users);
        $this->assign("search", $search);
        $roles=$this->role_model->where("status=1 and type=1")->order("id desc")->select();
        $this->assign("all_roles",$roles);
        $this->display();

    }

	function index111(){
		$role_id = I('role_id');
		$search['keyword'] = $keyword = I('keyword');





		$map = array();
		if(!empty($keyword))
		{


//            $where['user_login']  = array('like', "%$keyword%");
//            $where['user_nicename|user_login|mobile']  = array('like',"%$keyword%");
            $where['user_nicename|user_login|mobile']  = $keyword;

//            $group_id=M('AuthGroup')->where(array('status=1','title'=>array('like',"%$keyword%")))->getField('id',true);
//            if($group_id){
//                $user_id=M('AuthGroupAccess')->where(array('type'=>1,'group_id'=>array('in',$group_id)))->getField('uid',true);
//                if($user_id){
//                    $where['id']  = array('in',$user_id);
//                }
//            }
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
		}

		if(!empty($role_id)  && $role_id>0){
			$role['ag.group_id'] = $role_id;
			$this->assign('role_id',$role_id);
		}
		$map['user_type']=1;
		$count=M('Users')->where($map)->count();

		$page = $this->page($count, 20);

		$users = M('Users')
				->alias('u')
				->field('u.user_login,u.user_status,u.mobile,u.user_nicename,u.update_time,u.id,u.is_administrator,ag.group_id,g.title')

				->join('LEFT JOIN __AUTH_GROUP_ACCESS__ ag on ag.uid = u.id')

				->join('LEFT JOIN __AUTH_GROUP__ g on ag.group_id = g.id')

				->where($map)

				->where($role)

				->order("update_time DESC,id desc")

				->limit($page->firstRow . ',' . $page->listRows)

				->select();

		foreach($users as $k=>&$y){
			if($y['is_administrator']){
                $y['title'] = '超级管理员';
			}
            $y['user_status_text'] = $y['user_status'] == 1 ? '正常' : '冻结';

		}
		$this->assign("page", $page->show('Admin'));
		//$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->assign("search", $search);

		$roles=$this->role_model->where("status=1 and type=1")->order("id desc")->select();

		$this->assign("all_roles",$roles);

		$this->display();

	}

	

	function add(){
		if(IS_POST){

            $str =  I('mobile');
            $isMatched = preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/', $str, $matches);
		    if($isMatched != 1)
		    {
                $this->error("手机号格式错误！");
            }else
            {

			$group_id=I('group_id',0,'intval');
			if(!empty($_POST['group_id'])){
				if ($data = $this->users_model->create())
				{
                    $data['user_type'] = 1;
					$this->users_model->type=1;
					$result = $this->users_model->add($data);
					if ($result!==false)
					{
						$role_user_model = M("AuthGroupAccess");
						$role_user_model->add(array("group_id"=>$group_id,"uid"=>$result,'type'=>1));
						write_log('新增管理员','管理员管理');
						$this->success("添加成功！", U("user/index"));
					} else {
						$this->error("添加失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}
            }
		}else{
			$roles=$this->role_model->where("status=1 and type=1")->order("id desc")->select();
			$this->assign("roles",$roles);
			$this->display();

		}

	}

//user_status

    public function djAction()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数错误');
        }
        $user_status = M('Users')->where(array('id'=>$id))->getField('user_status');
        if($user_status == 1)
        {
            $user_status_n = 0;
        }else if($user_status == 0)
        {
            $user_status_n = 1;
        }
        $res = M('Users')->where(array('id'=>$id))->save(array('user_status'=>$user_status_n));
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->error('操作失败');
        }
	}

	function edit()
    {
		if(IS_POST){
			$group_id=I('group_id',0,'intval');
			if($group_id){
				if(empty($_POST['user_pass'])){
					unset($_POST['user_pass']);
				}
				if ($this->users_model->create()) {
					$result=$this->users_model->save();
					if ($result!==false) {
						$uid=intval($_POST['id']);
						$role_user_model=M("AuthGroupAccess");
						$role_user_model->where(array("uid"=>$uid,'type'=>1))->delete();
						//foreach ($role_ids as $role_id){
						$role_user_model->add(array("group_id"=>$group_id,"uid"=>$uid,'type'=>1));
						//}
						write_log('编辑管理员','管理员管理');
						//D("Log")->addLog("编辑管理员操作,ID：".$uid);
						$this->success("保存成功！",'/Admin/User/index');


					} else {
						$this->error("保存失败！");
					}
				} else {
					$this->error($this->users_model->getError());
				}
			}else{
				$this->error("请为此用户指定角色！");
			}
		}else{
			$id= intval(I("get.id"));

			$roles=$this->role_model->where("status=1 and type=1")->order("id desc")->select();
			$this->assign("roles",$roles);

			$role_user_model=M("AuthGroupAccess");
			$role_ids=$role_user_model->where(array("uid"=>$id,'type'=>1))->select();
			$group_list=array();
			foreach($role_ids as $key=>$vo){
				$group_list[]=$vo['group_id'];
			}

			$this->assign("group_list",$group_list);

			$user=$this->users_model->where(array("id"=>$id))->find();
			$this->assign('user',$user);
			$this->display('add');
		}
	}

	



	

	/**

	 *  删除

	 */

	function delete(){

		$id = intval(I("get.id"));

		if($id==1){

			$this->error("最高管理员不能删除！");

		}

		

		if ($this->users_model->where("id=$id")->delete()!==false) {

			M("RoleUser")->where(array("user_id"=>$id))->delete();
			M('AuthGroupAccess')->where(array("uid"=>$id))->delete();
			//D("Log")->addLog("删除管理员,ID：".$id);
            write_log('删除管理员','管理员管理');
			$this->success("删除成功！");

		} else {

			$this->error("删除失败！");

		}

	}

	

	

	function userinfo(){

		$id=get_current_admin_id();

		$user=$this->users_model->where(array("id"=>$id))->find();

		$this->assign($user);

		$this->display();

	}

	

	function userinfo_post(){

		if (IS_POST) {

			$_POST['id']=get_current_admin_id();

			$create_result=$this->users_model

			->field("user_login,user_email,last_login_ip,last_login_time,create_time,user_activation_key,user_status,role_id,score,user_type",true)//排除相关字段

			->create();

			if ($create_result) {

				if ($this->users_model->save()!==false) {
                    write_log('管理员信息修改','管理员管理');
					$this->success("保存成功！");

				} else {

					$this->error("保存失败！");

				}

			} else {

				$this->error($this->users_model->getError());

			}

		}

	}

	

	    function ban(){

        $id=intval($_GET['id']);

    	if ($id) {

    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','0');

    		if ($rst) {

				//D("Log")->addLog("停用管理员操作,ID：".$id);
                write_log('停用管理员操作','管理员管理');
    			$this->success("管理员停用成功！", U("user/index"));

    		} else {

    			$this->error('管理员停用失败！');

    		}

    	} else {

    		$this->error('数据传入失败！');

    	}

    }

    

    function cancelban(){

    	$id=intval($_GET['id']);

    	if ($id) {

    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','1');

    		if ($rst) {

				//D("Log")->addLog("启用管理员操作,ID：".$id);
                write_log('启用管理员操作','管理员管理');
    			$this->success("管理员启用成功！", U("user/index"));

    		} else {

    			$this->error('管理员启用失败！');

    		}

    	} else {

    		$this->error('数据传入失败！');

    	}

    }

	/**
	 *重置密码
	 **/
	public function resetting_pwd(){
		$id = I('id');
		$pwd = sp_password(123456);
		$res = M('Users')->where(array('id'=>$id))->setField('user_pass',$pwd);
		if($res){
			$this->ajaxReturn(array('status'=>1));
		}else{
			$this->ajaxReturn(array('status'=>0));
		}
	}

	

}