<?php

namespace Common\Model;
use Common\Model\CommonModel;
class QuestionModel extends CommonModel
{
	protected $_auto = array(

	    array('createtime','mGetDate',CommonModel:: MODEL_INSERT,'callback')
	);
	//用于获取,方法不能为private
	function mGetDate() {

		return time();

	}
}



