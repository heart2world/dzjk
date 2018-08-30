<?php
/* *
 * 系统权限配置，用户角色管理 by YL
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Model\Auth\AuthGroupModel;
class AuthController extends AdminbaseController {

    protected $role_model,$auth_access_model;

    function _initialize() {
        parent::_initialize();
        $this->role_model = new AuthGroupModel();
    }



    /**
     * 角色管理，有add添加，edit编辑，delete删除
     */
    public function index() {
		D('Interlocution')->plan_adopt();
		$type=I('type',100,'intval');
		$name=I('name');
		$st_time=I('st_time','','strtotime');
		$end_time=I('end_time','','strtotime');
		$map=array();

		if($type!=100){
			$map['r.type']=$type;
		}
		if(!empty($name)){
			$map['r.title']=array('like',"%$name%");
		}
		if($st_time&&$end_time){
			if($st_time>$end_time){
				$this->error('开始时间大于结束时间');
			}else{
				$end_time=strtotime(date('Y-m-d',$end_time).' 23:59:59');
				$map['r.update_time']=array(array('egt',$st_time),array('elt',$end_time));
				//有开始时间和结束时间
			}
		}elseif($st_time&&!$end_time){
			$map['r.update_time']=array('egt',$st_time);//有开始时间无结束时间
		}elseif(!$st_time&&$end_time){
			$end_time=strtotime(date('Y-m-d',$end_time).' 23:59:59');
			$map['r.update_time']=array('elt',$end_time);//无开始时间有结束时间
		}

        $data = $this->role_model->alias('r')
			->field('r.*,user.user_nicename,user.user_login')
			->join("LEFT JOIN __USERS__ user on r.admin_id=user.id")
			->where($map)
			->order("r.update_time desc,r.id asc")->select();
		foreach($data as $key=>$vo){
			if($vo['user_nicename']){
				$data[$key]['admin_name']=$vo['user_nicename'];
			}else{
				$data[$key]['admin_name']=$vo['user_login'];
			}
		}
        $this->assign("roles", $data);
		$this->assign("type", $type);
		$this->assign("name", $name);
		$this->assign("st_time", $st_time?date('Y-m-d',$st_time):'');
		$this->assign("end_time", $end_time?date('Y-m-d',$end_time):'');
        $this->display();
    }



    /**
     * 添加角色
     */

    public function roleadd() {
		if(IS_POST){
			if ($this->role_model->create()) {
				$this->role_model->admin_id=(int)session("ADMIN_ID");
				$ruleid=I('post.ruleid');
				if($ruleid){
					$rule=implode(',',$ruleid);
					$this->role_model->rules=$rule;
				}

				$role_id=$this->role_model->add();
				if ($role_id!==false) {
					//D("Log")->addLog("添加角色操作");
					write_log('添加角色操作','角色管理');
					$this->success("添加角色成功",U("Auth/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->role_model->getError());
			}
		}else{
			import("Tree");
			$menu = new \Tree();
			$menu->icon = array('│ ', '├─ ', '└─ ');
			$menu->nbsp = '&nbsp;&nbsp;&nbsp;';
			$result = M('AuthRule')->where(array('status'=>1,'id'=>array('notin','371,401,388,389'),'is_show'=>1))->select();

			$newmenus=array();

			//$priv_data=$this->auth_access_model->where(array("role_id"=>$roleid))->getField("rule_name",true);//获取权限表数据
			$priv_data=array();
			//dump($priv_data);die;
			foreach ($result as $k => $m){
				$newmenus[$m['id']]=$m;
			}

			foreach ($result as $n => $t) {
				$result[$n]['checked'] = ($this->_is_checked_new($t, $priv_data)) ? ' checked' : '';
				$result[$n]['level'] = $this->_get_level($t['id'], $newmenus);
				$result[$n]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
			}

			$str = "<tr id='node-\$id' \$parentid_node>

                       <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='ruleid[]' class='type_\$type' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$title</td>

	    			</tr>";

			$menu->init($result);
			$categorys = $menu->get_tree(0, $str);
			$this->assign("categorys", $categorys);
			$this->display();
		}
    }


    /**
     * 删除角色
     */

    public function roledelete() {
        $id = intval(I("get.id"));

        $count = M("AuthGroupAccess")->field('uid')->where("group_id=$id")->select();
        $newR = array();
        if($count){
            foreach ($count as $item) {
                $newR[] = $item['uid'];
            }
            $res = M('Users')->where(array('id'=>array('in',implode(',',$newR))))->delete();
            $res1 = M("AuthGroupAccess")->where(array('group_id'=>$id))->delete();
            $res2 = $this->role_model->delete($id);
            if ($res && $res1 && $res2) {
                write_log('删除角色操作','角色管理');
                //D("Log")->addLog("删除角色操作,ID：".$id);
                $this->success("删除成功！", U('Auth/index'));
            } else
            {
                $this->error("删除失败！");
            }
        }else
        {
        	$status = $this->role_model->delete($id);
        	if ($status!==false) {
                write_log('删除角色操作','角色管理');
				//D("Log")->addLog("删除角色操作,ID：".$id);
        		$this->success("删除成功！", U('Auth/index'));
        	} else {
        		$this->error("删除失败！");
        	}
        }
    }



    /**
     * 编辑角色
     */
    public function roleedit() {
		$id = I("id",0,'intval');
		if ($id == 0) {
			$this->error("请选择角色！");
		}
		if(IS_POST){
			$data = $this->role_model->create();
			$this->role_model->admin_id=(int)session("ADMIN_ID");
			$ruleid=I('post.ruleid');

			if($ruleid){
				$rule=implode(',',$ruleid);
				$this->role_model->rules=$rule;
			}else{
				$this->role_model->rules='';
			}
			if ($data) {
				if ($this->role_model->save()!==false) {
					write_log('编辑角色操作','角色管理');
					$this->success("修改成功！", U('Auth/index'));
				} else {
					$this->error("修改失败！");
				}
			} else {
				$this->error($this->role_model->getError());
			}
		}else{
			$info = $this->role_model->where(array("id" => $id))->find();
			$priv_data=array();
			if (!$info) {
				$this->error("该角色不存在！");
			}

			import("Tree");
			$menu = new \Tree();
			$menu->icon = array('│ ', '├─ ', '└─ ');
			$menu->nbsp = '&nbsp;&nbsp;&nbsp;';
			$result = M('AuthRule')->where(array('status'=>1,'id'=>array('notin','371,401'), 'is_show'=>1))->select();
			$newmenus=array();

			//$priv_data=$this->auth_access_model->where(array("role_id"=>$roleid))->getField("rule_name",true);//获取权限表数据
			if($info['rules']){
				$priv_data=explode(',',$info['rules']);
			}
			//dump($priv_data);die;
			foreach ($result as $k => $m){
				$newmenus[$m['id']]=$m;
			}

			foreach ($result as $n => $t) {

				$result[$n]['checked'] = ($this->_is_checked_new($t,  $priv_data)) ? ' checked' : '';
				$result[$n]['level'] = $this->_get_level($t['id'], $newmenus);
				$result[$n]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
			}

			$str = "<tr id='node-\$id' \$parentid_node>

                       <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='ruleid[]' class='type_\$type' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$title</td>

	    			</tr>";
			$menu->init($result);
			$categorys = $menu->get_tree(0, $str);
			$this->assign("categorys", $categorys);
			$this->assign('info',$info);
			$this->display('roleadd');
		}
    }


	private function _is_checked_new($menu, $priv_data) {
		if($priv_data){
			if (in_array($menu['id'], $priv_data)) {
				return true;
			} else {
				return false;
			}
		}else{
			return false;
		}
	}


    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {

        	if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
        		return  $i;
        	}else{
        		$i++;
        		return $this->_get_level($array[$id]['parentid'],$array,$i);
        	}
    }


}