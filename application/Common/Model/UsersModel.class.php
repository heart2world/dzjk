<?php

namespace Common\Model;

use Common\Model\CommonModel;

class UsersModel extends CommonModel

{



	protected $_validate = array(
        array('user_nicename','require','姓名不能为空！',1,'regex',CommonModel:: MODEL_BOTH ),
        array('mobile','require','手机号不能为空！',1,'regex',CommonModel:: MODEL_BOTH ),
        array('user_login', 'require', '登录账号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
        array('user_login', 'require', '登录账号不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
        array('user_login','','登录账号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_login字段是否唯一

    );



	protected $_auto = array(

	    array('create_time','mGetDate',CommonModel:: MODEL_INSERT,'callback'),
        array('update_time','time',CommonModel:: MODEL_INSERT,'function'),
	    //array('birthday','',CommonModel::MODEL_UPDATE,'ignore')

	);



	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private

	function mGetDate() {

		return date('Y-m-d H:i:s');

	}

	

	protected function _before_write(&$data) {

		parent::_before_write($data);

		

		if(!empty($data['user_pass']) && strlen($data['user_pass'])<25){

			$data['user_pass']=sp_password($data['user_pass']);

		}

	}

}



