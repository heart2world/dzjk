<?php

/**
 * 后台首页
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
class IndexController extends AdminbaseController {

    function _initialize() {
        empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码
        parent::_initialize();
        $this->initMenu();
    }

    /**
     * 后台框架首页
     */
    public function index() {


        if (C('LANG_SWITCH_ON',null,false)){
            $this->load_menu_lang();
        }
        //dump(D("Common/Menu")->menu_json());die;
        $list = D("Common/Menu")->menu_json();
//            var_dump($list);
//var_dump($list['867Admin']);
        if($_SESSION['ADMIN_ID']){
            $type = D('Users')->where(array('id'=>$_SESSION['ADMIN_ID']))->getField('user_type');
//                var_dump($list['871Admin']);

            if($type == 1)
            {
                unset($list['871Admin']);
                unset($list['878Admin']);
                unset($list['867Admin']);
            }


            if($type!=1){
                unset($list['867Admin']);
                unset($list['871Admin']);
            }
        }
        $this->assign("SUBMENU_CONFIG", $list);


        $this->display();

    }

    private function load_menu_lang(){
        $apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
        $error_menus=array();
        foreach ($apps as $app){
            if(is_dir(SPAPP.$app)){
                $admin_menu_lang_file=SPAPP.$app."/Lang/".LANG_SET."/admin_menu.php";
                if(is_file($admin_menu_lang_file)){
                    $lang=include $admin_menu_lang_file;
                    L($lang);
                }
            }
        }
    }

    //获取提示信息
    public function get_new(){

        $student_id = I('studentLastId',0,"intval");
        $where=array();
        $where['status'] = 0;
        if ($student_id > 0) {
            $where['member_id']=array('gt',$student_id);
        }
        $student['count']=M('MemberStudent')->where($where)->count();
        $student['id']=(int)M('MemberStudent')->where($where)->max('member_id');


        $company_id = I('companyLastId',0,"intval");
        $where=array();
        $where['pay_status'] = 1;
        if ($company_id > 0) {
            $where['member_id']=array('gt',$company_id);
        }
        $company['count']=M('MemberCompany')->where($where)->count();
        $company['id']=(int)M('MemberCompany')->where($where)->max('member_id');


        $order_id = I('orderLastId',0,"intval");
        $where=array();
        $where['status'] = 1;
        if ($order_id > 0) {
            $where['id']=array('gt',$order_id);
        }
        $order['count']=M('OrderResume')->where($where)->count();
        $order['id']=(int)M('OrderResume')->where($where)->max('id');



        $comments_id = I('commentsLastId',0,"intval");
        $where=array();
        $where['is_delete'] = 0;
        if ($comments_id > 0) {
            $where['id']=array('gt',$comments_id);
        }
        $comments['count']=M('Comments')->where($where)->count();
        $comments['id']=(int)M('Comments')->where($where)->max('id');



        $this->ajaxReturn(array('code'=>200,'msg'=>'查询成功','student'=>$student,'company'=>$company,'order'=>$order,'comments'=>$comments));

    }
}

