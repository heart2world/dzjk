<?php

/**
 * 附件上传
 */
namespace Asset\Controller;
use Common\Controller\AdminbaseController;
class AssetController extends AdminbaseController {


    function _initialize() {
    	$adminid=sp_get_current_admin_id();
    	$userid=sp_get_current_userid();
    	if(empty($adminid) && empty($userid) && empty($_SESSION['COMP_ID'])){
    		exit("非法上传！");
    	}
    }
    
    /**
     * swfupload 上传 
     */
    public function swfupload() {
        if (IS_POST) {
                $module=I('module');
                $thumb_size=I('thumb_size',0,'intval');
                $filetype =I('filetype','image','trim');
                $savepath='banner/'.date('Ymd').'/';
                if($filetype == 'image')
                {
                    //上传处理类
                    $config=array(
                            'rootPath' => './'.C("UPLOADPATH"),
                            'savePath' => $savepath,
                            'maxSize' => 11048576,
                            'saveName'   =>    array('uniqid',''),
                            'exts'       =>    array('jpg', 'gif', 'png', 'jpeg','bmp'),
                            'autoSub'    =>    false,
                    );
                }else
                {
                     //上传处理类
                    $config=array(
                            'rootPath' => './'.C("UPLOADPATH"),
                            'savePath' => $savepath,
                            'maxSize' => 11048576,
                            'saveName'   =>    array('uniqid',''),
                            'exts'       =>    array('pdf'),
                            'autoSub'    =>    false,
                    );
                }
                
                $upload = new \Think\Upload($config);//
                $info=$upload->upload();
                //开始上传
                if ($info) {
                    //上传成功
                    //写入附件数据库信息
                    $first=array_shift($info);
                    if(!empty($first['url'])){
                        $url=$first['url'];
                    }else{
                        $url=C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];
                    }
                    if(in_array($exts,array('jpg','gif','png','jpeg','bmp'))){
                        $savename=explode('.',$first['savename']);
                        $exts=$savename[count($savename)-1];
                        $exts=strtolower($exts);
                        $image = new \Think\Image();
                        $image->open($_SERVER['DOCUMENT_ROOT'].$url);
                        $width = $image->width();
                        if($thumb_size){
                            if(in_array($exts,array('jpg','gif','png','jpeg','bmp'))){
                                $image->thumb($thumb_size, $thumb_size)->save($_SERVER['DOCUMENT_ROOT'].$url);
                            }
                        }elseif($width>1920){
                            if(in_array($exts,array('jpg','gif','png','jpeg'))){
                                $image->thumb(1920, 1920)->save($_SERVER['DOCUMENT_ROOT'].$url);
                            }
                        }
                    }
                    echo "1," . $url.",".'1,'.$first['name'];

                    exit;
                } else {
                    
                    //上传失败，返回错误
                    exit("0," . $upload->getError());
                }
        } else {
            $args = I("args");
            $filetype =I('filetype','image','trim');
            if($args){
                $args = explode(",", $args);
            }
            $moudle=I('module');
            if(empty($moudle)){
                $moudle=date('Ymd');
            }
            $textareaid=I('textareaid');
            if(empty($textareaid)){
                $textareaid=date('His');
            }
            if(is_array($args) && count($args)>=1){
                if($filetype == 'image')
                {
                    $this->assign('file_upload_limit',$args[0]);
                }else
                {
                    $this->assign('file_upload_limit',$args[0]);
                }
                
            } else
            {
                $this->assign('file_upload_limit',1);
            } 

            $this->assign('thumb_size',$args[2]);
            $this->assign('filetype', $filetype);
            $this->assign('upload_type',C('UPLOAD_TYPE'));
            $this->assign('upload_host',C('UPLOAD_ACTION_HOST'));
            $this->assign('upload_key',C('UPLOAD_ACTION_KEY'));
            $this->assign('upload_remote_url',C('UPLOAD_REMOTE_URL'));
            $this->assign('moudle',$moudle);
            $this->assign('textareaid',$textareaid);
            $this->display(':swfupload');
        }
    }

    //视频上传
    public function video_upload(){

        $module=I('module','video');
        $field=I('field','default');
        $savepath=$module.'/'.$field.'/'.date('Ymd').'/';
        //上传处理类
        $config=array(
                'rootPath' => './'.C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 10*1024*1024,
                'saveName'   =>    array('uniqid',''),
                'exts'       =>    array('mp4','mov'),
                'autoSub'    =>    false,
        );

        $upload = new \Think\Upload($config);// 实例化上传类
        // 上传单个文件
        $info   =   $upload->upload();
        if($info){
            $first=array_shift($info);
            if(!empty($first['url'])){
                $url=$first['url'];
            }else{
                $url=C("TMPL_PARSE_STRING.__UPLOAD__").$first['savepath'].$first['savename'];
            }
            $data['info']=$url;
            $data['status']=1;
            $this->ajaxReturn($data);
        }else{
            $data['info']=$upload->getError();
            $data['status']=0;

            $this->ajaxReturn($data);
        }
    }

    //文件上传
    public function pc_upload(){
        
        $thumb_size=I('thumb_size',1200,'intval');
        $module=I('module','news');
        $field=I('field','thumb');
        $savepath=$module.'/'.$field.'/'.date('Ymd').'/';
        //上传处理类
        $config=array(
            'rootPath' => './'.C("UPLOADPATH"),
            'savePath' => $savepath,
            'maxSize' => 11048576,
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('pdf'),
            'autoSub'    =>    false,
        );

        $upload = new \Think\Upload($config);// 实例化上传类
        // 上传单个文件
        $info   =   $upload->upload();
        if($info){

            $first=array_shift($info);
            if(!empty($first['url'])){
                $url=$first['url'];
            }else{
                $url=C("TMPL_PARSE_STRING.__UPLOAD__").$first['savepath'].$first['savename'];
            }
            $data['info']=$url;
            $data['status']=1;
            $this->ajaxReturn($data);
            
        }else{
            $data['info']=$upload->getError();
            $data['status']=0;
            $this->ajaxReturn($data);
        }
    }

    //图片base64上传
    function base64Upload() {
        $module=I('module',date('Ymd'));
        $field=I('field',date('His'));
        $base64 = I('post.base64');
        $savepath='/'.C("UPLOADPATH").$module.'/'.$field.'/'.date('Ymd').'/';
        if(IS_POST){
            $base64_image =  str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        }else{
            $base64_image = $base64;
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
            //匹配成功
            if($result[2] == 'jpeg'){
                $image_name = uniqid().'.jpg';
                //纯粹是看jpeg不爽才替换的
            }else{
                $image_name = uniqid().'.'.$result[2];
            }
            $image_file = $_SERVER['DOCUMENT_ROOT'].$savepath;
            if (!file_exists($image_file)) {
                mkdir($image_file,0777,true);
            }
            $image_file .= '/'.$image_name;
            $imgUrl = $savepath.'/'.$image_name;
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                $this->success($imgUrl);
                //return $imgUrl;
            }else{
               $this->error('上传失败');
            }
        }else{
            $this->error('上传失败');
        }
    }

    
}
