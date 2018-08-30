<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/1
 * Time: 11:56
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;
use Common\Model\IntegralModel;

class IntegralController extends AdminbaseController
{
    protected $IntegralModel;
    function _initialize()
    {
        parent::_initialize();
        $this->IntegralModel = new IntegralModel();
    }

    public function index(){
        if(IS_AJAX){
            $data = I();
            $list = $this->IntegralModel->get_list($data);
            $this->ajaxReturn($list);
        }else{
            $this->display();
        }

    }
}