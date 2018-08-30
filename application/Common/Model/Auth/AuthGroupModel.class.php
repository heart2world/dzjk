<?php
/* *
 * 系统权限配置，用户角色 by YL
 */

namespace Common\Model\Auth;

use Common\Model\CommonModel;

class AuthGroupModel extends CommonModel{

	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('title', 'require', '角色名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
	);

	protected $_auto = array(
			array('create_time','time',1,'function'),
			array('update_time','time',3,'function'),
	);


	protected function _before_write(&$data) {
		parent::_before_write($data);
	}

}