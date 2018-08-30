<?php

namespace Common\Model;
use Common\Model\CommonModel;
class CompanyModel extends CommonModel
{
	protected $_validate = array(
		array('companyname','require','公司名称不能为空！',1,'regex',CommonModel:: MODEL_BOTH ),
        array('linkname','require','联系人不能为空！',1,'regex',CommonModel:: MODEL_BOTH ),
        array('mobile','require','手机号不能为空！',1,'regex',CommonModel:: MODEL_BOTH ),
        array('userlogin', 'require', '公司账号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
        array('userlogin', 'require', '公司账号不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
        array('userlogin','','公司账号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_login字段是否唯一

    );



	protected $_auto = array(

	    array('createtime','mGetDate',CommonModel:: MODEL_INSERT,'callback')
	);



	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private

	function mGetDate() {

		return time();

	}

	

	protected function _before_write(&$data) {

		parent::_before_write($data);

		

		if(!empty($data['userpass']) && strlen($data['userpass'])<32){

			$data['userpass']=sp_password($data['userpass']);

		}

	}

}



