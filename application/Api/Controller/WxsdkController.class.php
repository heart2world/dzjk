<?php

/**
 * Created by PhpStorm.
 * User: yanglin
 * Date: 2017-04-18
 */



namespace Api\Controller;

use Api\Controller\CommonController;
use Think\Exception;
use Think\Log;
use Common\Model\WeChatModel;


//微信SDK主要用于SDK调用
class WxsdkController extends CommonController
{

    public function get_sign(){
        if(is_weixin()){
            $url=I('url');
            if(empty($url)){
                $this->error(90031,'缺少参数url');
            }
            $weChatModel=new WeChatModel();
            $data=$weChatModel->getSignPackageApi($url);
            $this->success($data);
        }else{
           $this->error(90030,'请用微信浏览器访问');
        }

    }

    public function down_pic(){
        set_time_limit(24 * 60 * 60);
        $media_id=I('media_id');
        if(empty($media_id)){
            $this->error(90031,'缺少media_id参数');
        }
        $weChatModel=new WeChatModel();
        $wx=$weChatModel->getWeChatAuthWithAccessToken();
        $token=$wx->getAccessToken();
        if(empty($token['access_token'])){
            $this->error(90032,'微信配置有误，请联系管理员');
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

}