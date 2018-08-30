<?php

namespace Common\Controller;

use Common\Controller\AppframeController;
use Common\Model\DestinationModel;
use Common\Model\Member;

class HomebaseController extends AppframeController {

	protected $user_id;
	protected $member;
	protected $site_config;
	protected $current_city_name;
	protected $current_city_id;
	protected $current_destination_id;
	protected $current_address;
    protected $current_location_lng;
    protected $current_location_lat;
	public function __construct() {
		//$this->set_action_success_error_tpl();
		parent::__construct();
        $this->site_config=getOptions('site_options');
        $this->assign('config',$this->site_config);

		if(C('LOGIN_SAVE_STATUS')){
			$login_token=cookie('member_login_token');
			if($login_token) {
				$signPackage = sp_authcode($login_token, 'DECODE','EHECD2017');
				if ($signPackage) {
					$signPackage=unserialize($signPackage);
					if($signPackage){
						$token=verify_token($signPackage);
						$mobile_user=M('Member')->alias('m')
							->field('m.*,m.id as member_id')
							->where(array('m.token'=>$token,array('m.is_delete'=>0)))->find();
						if($mobile_user){
							$this->user_id=intval($mobile_user['member_id']);
                            session('user',$mobile_user);
						}
					}
				}
			}
		}else{
			$user=session('user');
			
			//$session_user_id=intval($user['id']);
			$session_user_id=intval($user['member_id']);

			if($session_user_id){
				$mobile_user=M('Member')->alias('m')
					->field('m.*,m.id as member_id')
					->where(array('m.id'=>$session_user_id,array('m.is_delete'=>0)))->find();
				//$mobile_user['member_id']=$mobile_user['id'];//补漏

				if($mobile_user){
					$this->user_id=intval($mobile_user['member_id']);
				}
			}

		}

		if($this->user_id){
			 //获得最新信息而不是登录的时候的状态
			$this->member=$mobile_user;

			$this->assign("user",$mobile_user);
			$this->assign('member_id',$this->user_id);
		}

        $lng=I('lng');
        $lat=I("lat");
        if($lng&&$lat){
			$lng=number_format($lng,6,".","");
			$lat=number_format($lat,6,".","");
            session('current_location_lng',$lng);
            session('current_location_lat',$lat);
        }
        $this->current_city_id=(int)session("current_city_id");
        $this->current_city_name=session("current_city_name");
        $lng=session('current_location_lng');
        $lat=session('current_location_lat');
        $this->current_location_lng=session('current_location_lng');
        $this->current_location_lat=session('current_location_lat');
		$this->current_address=session('current_address');
		$destination_model=new DestinationModel();
        if($lng&&$lat){
			$lng=number_format($lng,6,".","");
			$lat=number_format($lat,6,".","");
			session('current_location_lng',$lng);
			session('current_location_lat',$lat);
            $this->current_destination_id=(int)$destination_model->get_destination_id_by_city($this->current_city_id,$lng,$lat);

        }else{
            $this->current_destination_id=(int)$destination_model->get_destination_id_by_city($this->current_city_id);
        }
		if(!$this->current_destination_id){
			//定位城市下面一个都没得开通的，那么读取成都的。
			$this->current_destination_id=(int)$destination_model->get_destination_id_by_city(322);
		}
        //必须保证当前目的地有值
        $this->assign("current_city_id",$this->current_city_id);
        $this->assign("current_city_name",$this->current_city_name);
		$this->assign("current_address",$this->current_address);
        $this->assign("current_location_lng",$this->current_location_lng);
        $this->assign("current_location_lat",$this->current_location_lat);

		$is_first=(int)session("is_first");
		//3小时不再提示用户更换定位城市
		if($is_first>0&&time()-$is_first<=3*60*60){
			$is_first=1;
		}else{
			$is_first=0;
		}
		$this->assign("is_first",$is_first);
		if(is_weixin()){
			$this->assign("is_weixin",1);
		}else{
			$this->assign("is_weixin",0);
		}
	}



	protected function check_login(){
		if($this->member&&$this->member['member_id']&&$this->member['is_delete']!=-1){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 加载模板和页面输出 可以返回输出内容
	 * @access public
	 * @param string $templateFile 模板文件名
	 * @param string $charset 模板输出字符集
	 * @param string $contentType 输出类型
	 * @param string $content 模板输出内容
	 * @return mixed
	 */

	public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
		//echo $this->parseTemplate($templateFile);
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
		$tmpl_path=C("SP_TMPL_PATH");
		define("SP_TMPL_PATH", $tmpl_path);
		// 获取当前主题名称
		$theme      =    C('SP_DEFAULT_THEME');
		if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
			$t = C('VAR_TEMPLATE');
			if (isset($_GET[$t])){
				$theme = $_GET[$t];
			}elseif(cookie('think_template')){
				$theme = cookie('think_template');
			}

			if(!file_exists($tmpl_path."/".$theme)){
				$theme  =   C('SP_DEFAULT_THEME');
			}
			cookie('think_template',$theme,864000);
		}

		

		$theme_suffix="";
		if(C('MOBILE_TPL_ENABLED') && sp_is_mobile()){//开启手机模板支持
		    if (C('LANG_SWITCH_ON',null,false)){
		        if(file_exists($tmpl_path."/".$theme."_mobile_".LANG_SET)){//优先级最高
		            $theme_suffix  =  "_mobile_".LANG_SET;
		        }elseif (file_exists($tmpl_path."/".$theme."_mobile")){
		            $theme_suffix  =  "_mobile";
		        }elseif (file_exists($tmpl_path."/".$theme."_".LANG_SET)){
		            $theme_suffix  =  "_".LANG_SET;
		        }

		    }else{
    		    if(file_exists($tmpl_path."/".$theme."_mobile")){
    		        $theme_suffix  =  "_mobile";
    		    }
		    }
		}else{

		    $lang_suffix="_".LANG_SET;
		    if (C('LANG_SWITCH_ON',null,false) && file_exists($tmpl_path."/".$theme.$lang_suffix)){
		        $theme_suffix = $lang_suffix;
		    }

		}

		

		$theme=$theme.$theme_suffix;
		C('SP_DEFAULT_THEME',$theme);
		$current_tmpl_path=$tmpl_path.$theme."/";

		// 获取当前主题的模版路径
		define('THEME_PATH', $current_tmpl_path);
		C("TMPL_PARSE_STRING.__TMPL__",__ROOT__."/".$current_tmpl_path);

		C('SP_VIEW_PATH',$tmpl_path);
		C('DEFAULT_THEME',$theme);

		define("SP_CURRENT_THEME", $theme);
		if(is_file($template)) {
			return $template;
		}

		$depr       =   C('TMPL_FILE_DEPR');
		$template   =   str_replace(':', $depr, $template);

		

		// 获取当前模块
		$module   =  MODULE_NAME;
		if(strpos($template,'@')){ // 跨模块调用模版文件
			list($module,$template)  =   explode('@',$template);
		}

		

		

		// 分析模板文件规则
		if('' == $template) {
			// 如果模板文件名为空 按照默认规则定位
			$template = "/".CONTROLLER_NAME . $depr . ACTION_NAME;
		}elseif(false === strpos($template, '/')){
			$template = "/".CONTROLLER_NAME . $depr . $template;
		}

		$file = sp_add_template_file_suffix($current_tmpl_path.$module.$template);
		$file= str_replace("//",'/',$file);
		if(!file_exists_case($file)) E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
		return $file;
	}

	

	/**
	 * 设置错误，成功跳转界面
	 */

	private function set_action_success_error_tpl(){
		$theme      =    C('SP_DEFAULT_THEME');
		if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
			if(cookie('think_template')){
				$theme = cookie('think_template');
			}
		}

		//by ayumi手机提示模板
		$tpl_path = '';
		if(C('MOBILE_TPL_ENABLED') && sp_is_mobile() && file_exists(C("SP_TMPL_PATH")."/".$theme."_mobile")){//开启手机模板支持
			$theme  =   $theme."_mobile";
			$tpl_path=C("SP_TMPL_PATH").$theme."/";
		}else{
			$tpl_path=C("SP_TMPL_PATH").$theme."/";
		}

		

		//by ayumi手机提示模板
		$defaultjump=THINK_PATH.'Tpl/dispatch_jump.tpl';
		$action_success = sp_add_template_file_suffix($tpl_path.C("SP_TMPL_ACTION_SUCCESS"));
		$action_error = sp_add_template_file_suffix($tpl_path.C("SP_TMPL_ACTION_ERROR"));
		if(file_exists_case($action_success)){
			C("TMPL_ACTION_SUCCESS",$action_success);
		}else{
			C("TMPL_ACTION_SUCCESS",$defaultjump);
		}

		if(file_exists_case($action_error)){
			C("TMPL_ACTION_ERROR",$action_error);
		}else{
			C("TMPL_ACTION_ERROR",$defaultjump);
		}
	}
}