<?php
/**
 * 后台Controller
 */

namespace Common\Controller;
use Common\Controller\AppframeController;



class AdminbaseController extends AppframeController {

	protected $pageNum = 20;

	public function __construct() {
		$admintpl_path=C("SP_ADMIN_TMPL_PATH").C("SP_ADMIN_DEFAULT_THEME")."/";
		C("TMPL_ACTION_SUCCESS",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_SUCCESS"));
		C("TMPL_ACTION_ERROR",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_ERROR"));
		parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
        $this->assign("admin_menu", D("Common/Menu")->menu_json());
	}



    function _initialize(){
       parent::_initialize();
       $this->load_app_admin_menu_lang();
    	if(isset($_SESSION['ADMIN_ID'])){
    		$users_obj= M("Users");
    		$id=$_SESSION['ADMIN_ID'];
    		$user=$users_obj->where("id=$id")->find();

    		if(!$this->check_access($user)){
    			$this->error("您没有访问权限！");
    			exit();
    		}
    		$this->assign("admin",$user);

    	}else{
    		//$this->error("您还没有登录！",U("admin/public/login"));
    		if(IS_AJAX){
    			$this->error("您还没有登录！",U("admin/public/login"));
    		}else{
    			header("Location:".U("admin/public/login"));
    			exit();

    		}
    	}
    }

    

    /**
     * 初始化后台菜单
     */

    public function initMenu() {
        $Menu = F("Menu");
        if (!$Menu) {
            $Menu=D("Common/Menu")->menu_cache();
        }
        return $Menu;

    }



    /**
     * 消息提示
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax
     */

    public function success($message = '', $jumpUrl = '', $ajax = false) {
        parent::success($message, $jumpUrl, $ajax);

    }



    /**
     * 模板显示
     * @param type $templateFile 指定要调用的模板文件
     * @param type $charset 输出编码
     * @param type $contentType 输出类型
     * @param string $content 输出内容
     * 此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
     */

    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType);

    }

    

    /**
     * 获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀*
     * @return string

     */

    public function fetch($templateFile='',$content='',$prefix=''){
        $templateFile = empty($content)?$this->parseTemplate($templateFile):'';
		return parent::fetch($templateFile,$content,$prefix);

    }

    

    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */

    public function parseTemplate($template='') {
    	$tmpl_path=C("SP_ADMIN_TMPL_PATH");
    	define("SP_TMPL_PATH", $tmpl_path);
		// 获取当前主题名称
		$theme      =    C('SP_ADMIN_DEFAULT_THEME');

		if(is_file($template)) {
			// 获取当前主题的模版路径
			define('THEME_PATH',   $tmpl_path.$theme."/");
			return $template;
		}

		$depr       =   C('TMPL_FILE_DEPR');
		$template   =   str_replace(':', $depr, $template);

		// 获取当前模块
		$module   =  MODULE_NAME."/";
		if(strpos($template,'@')){ // 跨模块调用模版文件
			list($module,$template)  =   explode('@',$template);
		}

		// 获取当前主题的模版路径
		define('THEME_PATH',   $tmpl_path.$theme."/");

		

		// 分析模板文件规则
		if('' == $template) {
			// 如果模板文件名为空 按照默认规则定位
			$template = CONTROLLER_NAME . $depr . ACTION_NAME;
		}elseif(false === strpos($template, '/')){
			$template = CONTROLLER_NAME . $depr . $template;
		}

		C("TMPL_PARSE_STRING.__TMPL__",__ROOT__."/".THEME_PATH);
		C('SP_VIEW_PATH',$tmpl_path);
		C('DEFAULT_THEME',$theme);
		define("SP_CURRENT_THEME", $theme);

		

		$file = sp_add_template_file_suffix(THEME_PATH.$module.$template);
		$file= str_replace("//",'/',$file);
		if(!file_exists_case($file)) E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
		return $file;

    }



    /**
     *  排序 排序字段为listorders数组 POST 排序字段为：listorder
     */

    protected function _listorders($model) {
        if (!is_object($model)) {
            return false;
        }

        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }
        return true;

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



    private function check_access($user){

    	//如果用户角色是1，则无需判断
    	if($user['id'] == 1){
            //固定ID为超级管理员，不验证权限
    		return true;
    	}
        if($user['is_administrator'] == 1){
            //设置为超级管理员，不验证
            return true;
        }

        $rule=strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
    	$no_need_check_rules=array("admin/index/index","admin/main/index");

    	if( !in_array($rule,$no_need_check_rules) ){
            $count=M('AuthRule')->where(array('name'=>$rule,'type'=>1,'status'=>1))->count();
            if($count){
                return sp_auth_check_new($user['id'],$rule,1);
            }else{
                //权限规则不存在，代表全部角色都通过
                return true;
            }
    	}else{
    		return true;
    	}
    }

    

    private function load_app_admin_menu_lang(){
    	if (C('LANG_SWITCH_ON',null,false)){
    		$admin_menu_lang_file=SPAPP.MODULE_NAME."/Lang/".LANG_SET."/admin_menu.php";
    		if(is_file($admin_menu_lang_file)){
    			$lang=include $admin_menu_lang_file;
    			L($lang);
    		}
    	}
    }


    /**
    +----------------------------------------------------------
     * Export Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
    +----------------------------------------------------------
     * @param $expTitle     string File name
    +----------------------------------------------------------
     * @param $expCellName  array  Column name
    +----------------------------------------------------------
     * @param $expTableData array  Table data
    +----------------------------------------------------------
     */
    public function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W',
                'X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS',
                'AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  导出时间:'.date('Y-m-d H:i:s'));
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8

        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
                if($expCellName[$j][2]=='string') {
                    $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($cellName[$j].($i+3),
                            $expTableData[$i][$expCellName[$j][0]],
                            \PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}