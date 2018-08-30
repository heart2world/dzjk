<?php
//手机端继承公共手机基类

namespace Mobile\Controller;
use Common\Controller\MobilebaseController;
use Com\WechatAuth;
use Common\Model\MemberModel;
use Common\Model\WeChatModel;

class CommonController extends MobilebaseController

{

    protected $weChatOptions;
    protected $weChatAuth;
    public $pageSize=15;
    public function __construct() {
        parent::__construct();


        $p_code=I("p_code",0,"intval");
        if($p_code){
//            if($this->member&&$this->member['is_delete']==0){
//                //如果当前会员是登录的
//                $member_model=new MemberModel();
//                $member_model->change_path($this->member,$p_code);
//            }else{
                session("p_code",$p_code);
                //当前会员没有登录，或注册，保存推广码，注册或登录是使用改变层级关系
//            }
        }
        $this->assign("footer_url",$_SERVER['REQUEST_URI']);
       // dump($_SERVER['REQUEST_URI']);exit;

//        $weChatModel=new WeChatModel();
//        $signPackage=$weChatModel->getSignPackage();
//        $this->assign('signPackage',$signPackage);

//        $this->weChatOptions = getOptions('weixin');
//
//        $this->weChatAuth = new WechatAuth($this->weChatOptions['appId'],$this->weChatOptions['appSecret']);
//
//        $wx_openid=session("wx_openid");

        /*没有微信授权过，需要获取微信授权 */
//        if(empty($wx_openid) && (ACTION_NAME != 'wechat_login')){
//
//            $state = md5(time().mt_rand(1,1000));
//            session('state', $state);
//
//            if (!empty($_SERVER['REQUEST_URI'])) {
//                $backUrl = $_SERVER['REQUEST_URI'];
//            } else {
//                $backUrl = U('Index/index');
//            }
//            session('backurl', $backUrl);
//            $loginUrl = $this->weChatAuth->getRequestCodeURL("http://".$_SERVER['HTTP_HOST'].U('Index/wechat_login'),$state);
//
//            header("location:$loginUrl");
//            die;
//        }
    }

    public function no_update_city(){
        session("is_first",time());
        //3小时内不再提示用户切换城市
        $this->success("拒绝成功");
    }

    public function set_current_city(){
        $province=I('province');
        $city=I('city','成都');
        $district=I('district');
        $street=I('street');
        $streetNumber=I('streetNumber');
        $location_lng=I('location_lng');
        $location_lat=I('location_lat');
        $city=str_replace('市','',$city);

        $province_name=str_replace('省','',$province);
        if($province_name){
            $data['province']=M('Region')->where(array('is_delete'=>0,'region_type'=>1,'_string'=>" region_name='$province_name' or locate(region_name,'$province_name')>0"))->getField('region_id');
            if($data['province']&&$city){
                $info=M('Region')->where(array('parent_id'=>$data['province'],'is_delete'=>0,'region_type'=>2,'_string'=>" region_name='$city' or locate(region_name,'$city')>0"))->find();
            }
        }
        if(!$info){
            //如果没有找到城市，定位到成都
            $info['region_id']=322;
            $info['region_name']="成都";
        }

        //获取当前城市ID
        session("current_address",$province.$city.$district.$street.$streetNumber);
        session("current_city_id",$info['region_id']);
        session("current_city_name",$info['region_name']);
        $location_lng=number_format($location_lng,6,".","");
        $location_lat=number_format($location_lat,6,".","");
        session("current_location_lng",$location_lng);
        session("current_location_lat",$location_lat);
        $data=array(
           'current_city_name'=>$info['region_name'],
           'current_address'=>$province.$city.$district.$street.$streetNumber,
           'current_city_id'=>$info['region_id'],
           'current_location_lng'=>$location_lng,
           'current_location_lat'=>$location_lat,
        );
        $this->success($data);
    }


    public function wechat_login(){
        $state = I("get.state");
        $myState = session('state');
        $code = I("get.code");
        if($myState  && ($state == $myState) && $code){
            $memberModel = new MemberModel();
            try{
                $token = $this->weChatAuth->getAccessToken('code',$code);
                $userInfo = $this->weChatAuth->getUserInfo($token['openid']);
                if(!$userInfo){
                    E('微信信息反馈失败');
                }
                if(M('Wxinfo')->where(array('openid'=>$userInfo['openid']))->count() > 0){
                    M('Wxinfo')->save($userInfo);
                }else{
                    M('Wxinfo')->add($userInfo);
                }
                session("wx_openid",$userInfo['openid']);
                $member = $memberModel->field('*,id as member_id')->where("openid = '{$userInfo['openid']}' and is_delete=0")->find();
                if($member){
                    $update['last_login_time']=time();
                    $update['last_login_ip']=get_client_ip();
                    $token=sp_random_string(10);
                    $update['token']=md5($token.'_'.$member['member_id']);
                    $time=20*24*3600;//自动登录20天
                    if(C('LOGIN_SAVE_STATUS')){
                        cookie('member_login_token',get_token($member['member_id'],'Member',$token,$time),$time);
                    }else{
                        session('user',$member);
                        cookie('user',$member,$time);
                    }
                    $update['last_login_ip']=get_client_ip();
                    $update['last_login_time']=time();
                    $memberModel->where("openid = '{$userInfo['openid']}' and is_delete=0")->save($update);
                }
                $returnUrl = session('backurl');
                if($returnUrl){
                    redirect($returnUrl);
                }else{
                    redirect(U('Index/index'));
                }
            }catch (\Exception $e){
                \Think\Log::write('授权登录失败'.$e->getMessage());
                $this->error($e->getMessage());
            }
        }else{
            $this->error("非法请求");
        }
    }

    public function down_pic(){
        $thumb_size=I('thumb_size',1200,'intval');
        set_time_limit(24 * 60 * 60);
        $media_id=I('media_id');
        if(empty($media_id)){
            $this->error('缺少media_id参数');
        }
        $weChatModel=new WeChatModel();
        $wx=$weChatModel->getWeChatAuthWithAccessToken();
        $token=$wx->getAccessToken();
        if(empty($token['access_token'])){
            $this->error('微信配置有误，请联系管理员');
        }
        $res=file_get_contents("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$token['access_token']."&media_id=".$media_id);
        $module=I('module');
        $field=I('field');
        if(empty($module)){
            $module=date('Ymd');
        }
        if(empty($field)){
            $field=date('His');
        }
        $root_path='/'.C("UPLOADPATH").$module.'/'.$field;
        $savepath  = $_SERVER['DOCUMENT_ROOT'].$root_path;

        if(!is_dir($savepath)){
            mkdir($savepath,0777,true);
        }
        $filename=uniqid().'.jpg';
        file_put_contents($savepath.'/'.$filename,$res);
        $db_path=$root_path.'/'.$filename;

        $image = new \Think\Image();
        $image->open($_SERVER['DOCUMENT_ROOT'].$db_path);
        $image->thumb($thumb_size, $thumb_size)->save($_SERVER['DOCUMENT_ROOT'].$db_path);

        if(C('UPLOAD_TYPE')=='remote'){
            //传输到远程服务器
            $upload_path=C('UPLOAD_ACTION_HOST').'&appKey='.C('UPLOAD_ACTION_KEY').'&module='.$module.'&field='.$field;
            $data = array(
                'upfile'=>'@'.$savepath.'/'.$filename
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$upload_path);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec ($ch);
            curl_close ($ch);
            $result=json_decode($result,true);
            if($result['state']=='SUCCESS'){
                @unlink ($savepath.'/'.$filename);//删除服务器文件
                $db_path=C('UPLOAD_REMOTE_URL').$result['url'];
            }
        }
        $this->success($db_path);
    }

    //获取当前用户公司信息数量已付款的
    public function check_company_pay_count(){
        $count=M('MemberCompany')->where(array('member_id'=>$this->member['member_id'],'pay_status'=>1))->count();
        return $count;
    }
}