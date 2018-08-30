<?php
/**
 *   检测机构
 */
namespace Company\Controller;

use Think\Controller;

class BaseController extends Controller {

    function _initialize(){
    	if(isset($_SESSION['COMP_ID'])){
    		$users_obj= M("company");
    		$id=$_SESSION['COMP_ID'];
    		$user=$users_obj->where("id=$id")->find();
    		$this->assign("admin",$user);

    	}else{
    		if(IS_AJAX){
    			$this->error("您还没有登录！",U("Company/public/login"));
    		}else{
    			header("Location:".U("Company/public/login"));
    			exit();

    		}
    	}
    }

    /**
     * 后台分页
     *
     */

    protected function page($total_size = 1, $page_size = 0, $current_page = 1, $listRows = 6, $pageParam = '', $pageLink = '', $static = FALSE) {
        if ($page_size == 0) {
            $page_size = C("PAGE_LISTROWS");
        }

        if (empty($pageParam)) {
            $pageParam = C("VAR_PAGE");

        }

        $Page = new \Page($total_size, $page_size, $current_page, $listRows, $pageParam, $pageLink, $static);
        $Page->SetPager('Admin', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        $Page->setLinkWraper("li");

        return $Page;

    }

}