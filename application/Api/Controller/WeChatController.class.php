<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-06-23
 * Time: 15:39
 */

namespace Api\Controller;


use Think\Controller;
use Com\Wechat;
use Think\Exception;
use Think\Log;
use Common\Model\WeChatModel;

class WeChatController extends Controller
{
    /**
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     */
    public function index($id = ''){

        //调试
        //try{
            $weChatOptions = getOptions('weixin');

            $appid = $weChatOptions['appId']; //AppID(应用ID)
            $token = $weChatOptions['token']; //微信后台填写的TOKEN
            $wxkey = $weChatOptions['wxkey']; //微信后台填写的KEY

//            echo json_encode($weChatOptions);
//            $crypt = '00UvWvrBDYmmfEbqMXotNDzzCpqKDbTBDSbsY5WnEqz'; //消息加密KEY（EncodingAESKey）
            /* 加载微信SDK */
            $wechat = new Wechat($token, $appid,$wxkey);

            /* 获取请求信息 */
            $data = $wechat->request();
            Log::record(json_encode($data),"ERR");
            if($data && is_array($data)){
                /**
                 * 你可以在这里分析数据，决定要返回给用户什么样的信息
                 * 接受到的信息类型有10种，分别使用下面10个常量标识
                 * Wechat::MSG_TYPE_TEXT       //文本消息
                 * Wechat::MSG_TYPE_IMAGE      //图片消息
                 * Wechat::MSG_TYPE_VOICE      //音频消息
                 * Wechat::MSG_TYPE_VIDEO      //视频消息
                 * Wechat::MSG_TYPE_SHORTVIDEO //视频消息
                 * Wechat::MSG_TYPE_MUSIC      //音乐消息
                 * Wechat::MSG_TYPE_NEWS       //图文消息（推送过来的应该不存在这种类型，但是可以给用户回复该类型消息）
                 * Wechat::MSG_TYPE_LOCATION   //位置消息
                 * Wechat::MSG_TYPE_LINK       //连接消息
                 * Wechat::MSG_TYPE_EVENT      //事件消息
                 *
                 * 事件消息又分为下面五种
                 * Wechat::MSG_EVENT_SUBSCRIBE    //订阅
                 * Wechat::MSG_EVENT_UNSUBSCRIBE  //取消订阅
                 * Wechat::MSG_EVENT_SCAN         //二维码扫描
                 * Wechat::MSG_EVENT_LOCATION     //报告位置
                 * Wechat::MSG_EVENT_CLICK        //菜单点击
                 */

                //记录微信推送过来的数据
                file_put_contents('./data.json', json_encode($data));

                /* 响应当前请求(自动回复) */
                //$wechat->response($content, $type);

                /**
                 * 响应当前请求还有以下方法可以使用
                 * 具体参数格式说明请参考文档
                 *
                 * $wechat->replyText($text); //回复文本消息
                 * $wechat->replyImage($media_id); //回复图片消息
                 * $wechat->replyVoice($media_id); //回复音频消息
                 * $wechat->replyVideo($media_id, $title, $discription); //回复视频消息
                 * $wechat->replyMusic($title, $discription, $musicurl, $hqmusicurl, $thumb_media_id); //回复音乐消息
                 * $wechat->replyNews($news, $news1, $news2, $news3); //回复多条图文消息
                 * $wechat->replyNewsOnce($title, $discription, $url, $picurl); //回复单条图文消息
                 *
                 */

                //执行Demo
                $this->demo($wechat, $data);
            }
//        } catch(\Exception $e){
//            Log::record(json_encode($e->getMessage()),"ERR");
//            exit('');
//        }

    }

    /**
     * DEMO
     * @param  Object $wechat Wechat对象
     * @param  array  $data   接受到微信推送的消息
     */
    private function demo($wechat, $data){
        switch ($data['MsgType']) {
            case Wechat::MSG_TYPE_EVENT:
                switch ($data['Event']) {
                    case Wechat::MSG_EVENT_SUBSCRIBE:
                        $msg=M('WxReply')->where('type=2')->find();//获取自动回复内容
                        if($msg['turn']=='1'){
                            switch($msg['reply_type']){
                                case '1':
                                    $wechat->replyText($msg['content']);
                                    break;
                                case '2';
                                    $article_id=$msg['article_id'];
                                    if($article_id){
                                        $article_arr=explode(',',$article_id);
                                        $article_arr=array_unique(array_filter($article_arr));
                                        if(count($article_arr)==1){
                                            $article=D('WxPicArticle')->get_pic_once($article_arr);
                                            $wechat->replyNewsOnce(
                                                $article['title'],
                                                $article['discription'],
                                                $article['url'],
                                                $article['pic_url']
                                            ); //回复单条图文消息
                                        }elseif(count($article_arr)>1){
                                            $article_list=D('WxPicArticle')->get_pic($article_arr);
                                            $news=array();
                                            foreach($article_list as $key=>$article){
                                                $news[]=array($article['title'],$article['discription'],$article['url'],$article['pic_url']);
                                            }
                                            call_user_func_array(array($wechat, "replyNews"),$news);
                                        }else{
                                            $wechat->replyText('没有找到图文消息');
                                        }
                                    }else{
                                        $wechat->replyText('没有找到图文消息');
                                    }
                                    break;
                            }
                        }
                        break;

                    case Wechat::MSG_EVENT_UNSUBSCRIBE:
                        //取消关注，记录日志
                        $wechat->replyText("非常感谢您的支持");
                        break;

                    case 'kf_create_session':
                        //客服接入会话
                        Log::record('客服已经接待了',"ERR");
                        $this->save_kf_log($data['FromUserName'],$data['KfAccount']);
                        Log::record('更新到会话记录',"ERR");
                        $model = new WeChatModel();
                        $wx = $model->getWeChatAuthWithAccessToken();
                        $full_kfaccount=$data['KfAccount'];
                        $arr_kfaccount=explode('@',$full_kfaccount);
                        $KfAccount=$arr_kfaccount[0];
                        $kf_name=M('WxKf')->where(array('kf_account'=>$KfAccount,'is_delete'=>0))->getField('nickname');
                        $wx->sendMessage($data['FromUserName'],"您好，现在由人工客服:".$kf_name."为您服务。",$data['KfAccount']);
                        //$wechat->replyText("现在是人工客服为你服务了哟");
                        break;
                    case 'kf_close_session':
                        //客服关闭会话
                        Log::record('客服关闭会话了',"ERR");
                        $this->save_kf_log($data['FromUserName'],$data['KfAccount']);
                        Log::record('更新到会话记录',"ERR");
                        $model = new WeChatModel();
                        $wx = $model->getWeChatAuthWithAccessToken();
                        $wx->sendMessage($data['FromUserName'],"谢谢您对我们的理解及支持，再见！",$data['KfAccount']);
                        break;
                    case 'kf_switch_session':
                        //客服转接
                        Log::record('客服关闭会话了',"ERR");
                        $this->save_kf_log($data['FromUserName'],$data['KfAccount']);
                        Log::record('更新到会话记录',"ERR");
                        $model = new WeChatModel();
                        $wx = $model->getWeChatAuthWithAccessToken();
                        $full_kfaccount=$data['KfAccount'];
                        $arr_kfaccount=explode('@',$full_kfaccount);
                        $KfAccount=$arr_kfaccount[0];
                        $kf_name=M('WxKf')->where(array('kf_account'=>$KfAccount,'is_delete'=>0))->getField('nickname');
                        $wx->sendMessage($data['FromUserName'],"您好，现在由人工客服:".$kf_name."为您服务。",$data['KfAccount']);
                        break;
					case Wechat::MSG_EVENT_CLICK:
						if($data['Event']=='CLICK'){
							$model = M("WxKeywords");
							$key = $model->where("keyword = '%s'",$data['EventKey'])->find();
							Log::record(json_encode($key),"INFO");
							if($key){
								switch($key['type']){
									case '1':
										$wechat->replyText($key['content']);
										break;
									case '2':
                                        $article_id=$key['article_id'];
                                        if($article_id){
                                            $article_arr=explode(',',$article_id);
                                            $article_arr=array_unique(array_filter($article_arr));
                                            if(count($article_arr)==1){
                                                $article=D('WxPicArticle')->get_pic_once($article_arr);
                                                $wechat->replyNewsOnce(
                                                    $article['title'],
                                                    $article['discription'],
                                                    $article['url'],
                                                    $article['pic_url']
                                                ); //回复单条图文消息
                                            }elseif(count($article_arr)>1){
                                                $article_list=D('WxPicArticle')->get_pic($article_arr);
                                                $news=array();
                                                foreach($article_list as $key=>$article){
                                                    $news[]=array($article['title'],$article['discription'],$article['url'],$article['pic_url']);
                                                }
                                                call_user_func_array(array($wechat, "replyNews"),$news);
                                            }else{
                                                $wechat->replyText('没有找到图文消息');
                                            }
                                        }else{
                                            $wechat->replyText('没有找到图文消息');
                                        }
										break;
									default:
										Log::record('未知的处理类型',"ERR");
								}
							}
						}
						break;
                    default:
                        //$wechat->replyText("欢迎访问麦当苗儿公众平台！您的事件类型：{$data['Event']}，EventKey：{$data['EventKey']}");
                        break;
                }
                break;

            case Wechat::MSG_TYPE_TEXT:
                $model = M("WxKeywords");
                $key = $model->where("keyword = '%s'",$data['Content'])->find();
                Log::record(json_encode($key),"INFO");
                if($key){
                    switch($key['type']){
                        case '1':
                            $wechat->replyText($key['content']);
                            break;
                        case '2':
                            $article_id=$key['article_id'];
                            if($article_id){
                                $article_arr=explode(',',$article_id);
                                $article_arr=array_unique(array_filter($article_arr));
                                if(count($article_arr)==1){
                                    $article=D('WxPicArticle')->get_pic_once($article_arr);
                                    $wechat->replyNewsOnce(
                                            $article['title'],
                                            $article['discription'],
                                            $article['url'],
                                            $article['pic_url']
                                    ); //回复单条图文消息
                                }elseif(count($article_arr)>1){
                                    $article_list=D('WxPicArticle')->get_pic($article_arr);
                                    $news=array();
                                    foreach($article_list as $key=>$article){
                                        $news[]=array($article['title'],$article['discription'],$article['url'],$article['pic_url']);
                                    }
                                    call_user_func_array(array($wechat, "replyNews"),$news);
                                }else{
                                    $wechat->replyText('没有找到图文消息');
                                }
                            }else{
                                $wechat->replyText('没有找到图文消息');
                            }
                            break;
                        case '3':
                            $kf_account=M('WxKf')->where(array('id'=>$key['kf_id']))->getField('kf_account');
                            $this->Tokf($wechat,$data,$kf_account);//转发客服系统
                            break;
                        default:
                            Log::record('未知的处理类型',"ERR");
                    }
                }
                if(trim($data['Content'])=='01'){
                    $this->Tokf($wechat,$data);
                }
                $msg=M('WxReply')->where('type=1')->find();//获取自动回复内容
                Log::record(json_encode($msg),"INFO");
                if($msg['turn']=='1'){
                    Log::record($msg['reply_type'],"INFO");
                    switch($msg['reply_type']){
                        case '1':
                            $wechat->replyText($msg['content']);
                            break;
                        case '2';
                            $article_id=$msg['article_id'];
                            if($article_id){
                                $article_arr=explode(',',$article_id);
                                $article_arr=array_unique(array_filter($article_arr));
                                if(count($article_arr)==1){
                                    $article=D('WxPicArticle')->get_pic_once($article_arr);
                                    F('article',$article);
                                    $wechat->replyNewsOnce(
                                        $article['title'],
                                        $article['discription'],
                                        $article['url'],
                                        $article['pic_url']
                                    ); //回复单条图文消息
                                }elseif(count($article_arr)>1){
                                    $article_list=D('WxPicArticle')->get_pic($article_arr);
                                    $news=array();
                                    foreach($article_list as $key=>$article){
                                        $news[]=array($article['title'],$article['discription'],$article['url'],$article['pic_url']);
                                    }
                                    call_user_func_array(array($wechat, "replyNews"),$news);
                                }else{
                                    $wechat->replyText('没有找到图文消息');
                                }
                            }else{
                                $wechat->replyText('没有找到图文消息');
                            }
                            break;
                    }
                }


//                if($msg['reply_type']=='1'){
//                    $content=$msg['content'];
//                }
//                if($content){
//                    $wechat->replyText($content);exit;
//                }
//                $wechat->replyText('好饿');exit;
            case Wechat::MSG_EVENT_LOCATION:

                break;
            default:
                Log::record('未找到对应的处理',"ERR");
                exit('');
                break;
        }
    }

    private function Tokf($wechat,$data,$kf_account=""){
        Log::record('消息转客服系统开始',"ERR");
        if(empty($kf_account)) {
            if ($data['FromUserName']) {
                $find = M('WxKfLog')->where(array('openid' => $data['FromUserName']))->order('create_time')->find();
                //检查以前有个记录没得
                if ($find) {
                    if ($find['kf_account']) {
                        //以前存在客服聊天优先转给他
                        Log::record($find['kf_account'], "ERR");
                        $kf_account = $this->get_kf_account($find['kf_account']);
                    } else {
                        //没得客服接待过。
                        M('WxKfLog')->where(array('openid' => $data['FromUserName']))->setField('create_time', time());

                        //新功能调整
                        $all_ptkf_list=M('WxKfAccess')->alias('a')
                            ->join('INNER JOIN __WX_KF__ kf on a.kf_id=kf.id')
                            ->where(array('kf.is_delete' => 0,'a.group_id'=>1))->select();
                        //所有普通分组下的客服
                        $model = new WeChatModel();
                        $wx = $model->getWeChatAuthWithAccessToken();
                        $result = $wx->getonlinekflist();
                        Log::record(json_encode($result), "ERR");
                        if ($result['kf_online_list']) {
                            $ptkf_account=array();//普通客服的账户去掉后缀
                            foreach($all_ptkf_list as $k=>$kf){
                                $ptkf_account[]=$kf['kf_account'];
                            }
                            $all_ptkf_list_online=array();//能转发的客服数组
                            foreach($result['kf_online_list'] as $key=>$vo){
                                $online_kf_account=explode('@',$vo['kf_account']);
                                $online_kf_account=$online_kf_account[0];//去掉后缀
                                if(in_array($online_kf_account,$ptkf_account)){
                                    $all_ptkf_list_online[]=$online_kf_account;
                                }
                            }
                            if($all_ptkf_list_online){
                                Log::record('随机抽取一个普通客服', "ERR");
                                $k=rand(0,count($all_ptkf_list_online)-1);
                                $kf_account = $all_ptkf_list_online[$k];
                            }else{
                                Log::record('普通客服没有一个在线', "ERR");
                                $kf_account = false;
                            }
                        } else {
                            Log::record('微信服务器反馈没有在线客服', "ERR");
                            $kf_account = false;
                        }
                    }
                } else {
                    M('WxKfLog')->add(array('openid' => $data['FromUserName'], 'create_time' => time()));
                    //新功能调整
                    $all_ptkf_list=M('WxKfAccess')->alias('a')
                        ->join('INNER JOIN __WX_KF__ kf on a.kf_id=kf.id')
                        ->where(array('kf.is_delete' => 0,'a.group_id'=>1))->select();
                    //所有普通分组下的客服
                    $model = new WeChatModel();
                    $wx = $model->getWeChatAuthWithAccessToken();
                    $result = $wx->getonlinekflist();
                    Log::record(json_encode($result), "ERR");
                    if ($result['kf_online_list']) {
                        $ptkf_account=array();//普通客服的账户去掉后缀
                        foreach($all_ptkf_list as $k=>$kf){
                            $ptkf_account[]=$kf['kf_account'];
                        }
                        $all_ptkf_list_online=array();//能转发的客服数组
                        foreach($result['kf_online_list'] as $key=>$vo){
                            $online_kf_account=explode('@',$vo['kf_account']);
                            $online_kf_account=$online_kf_account[0];//去掉后缀
                            if(in_array($online_kf_account,$ptkf_account)){
                                $all_ptkf_list_online[]=$online_kf_account;
                            }
                        }
                        if($all_ptkf_list_online){
                            Log::record('随机抽取一个普通客服', "ERR");
                            $k=rand(0,count($all_ptkf_list_online)-1);
                            $kf_account = $all_ptkf_list_online[$k];
                        }else{
                            Log::record('普通客服没有一个在线', "ERR");
                            $kf_account = false;
                        }
                    } else {
                        Log::record('微信服务器反馈没有在线客服', "ERR");
                        $kf_account = false;
                    }
                }
            }
        }
        if($kf_account===false){
            $model = new WeChatModel();
            $wx = $model->getWeChatAuthWithAccessToken();
            $wx->sendMessage($data['FromUserName'],"没有在线客服，稍后咨询",$data['KfAccount']);
            //$wechat->replyText('没有在线客服，稍后咨询');
            exit('');
        }
        if($kf_account){
            $wx_config=get_options('weixin');
            $kf_account=$kf_account.'@'.$wx_config['wxID'];
        }
        $wechat->replyCustomerService($kf_account);
    }


    private function get_kf_account($kf_account){
        $model = new WeChatModel();
        $wx = $model->getWeChatAuthWithAccessToken();
        $result=$wx->getonlinekflist();
        Log::record(json_encode($result),"ERR");
        if($result['kf_online_list']){
            foreach($result['kf_online_list'] as $key=>$vo){
                $kf_account_arr=explode('@',$vo['kf_account']);
                if($kf_account==$kf_account_arr[0]&&$vo['status']==1){ //如果上次对话的客服在线那么返回该账号
                    Log::record('上次对话的客服在线，直接转上次客服',"ERR");
                    return $kf_account;
                }
            }
        }else{
            Log::record('微信服务器反馈没有在线客服',"ERR");
            return false;//微信反馈的客户不存在
        }
        $group_id_list=M('WxKfAccess')->alias('a')
            ->field('a.group_id')
            ->join('INNER JOIN __WX_KF__ k on a.kf_id=k.id')
            ->where(array('k.kf_account'=>$kf_account,array('k.is_delete'=>0)))->select();
        $group_id=array();
        foreach($group_id_list as $vo){
            $group_id[]=$vo['group_id'];
        }
        if($group_id){
            $kf_list=M('WxKfAccess')->alias('a')
                ->field('k.kf_account')
                ->join('LEFT JOIN __WX_KF__ k on a.kf_id=k.id')
                ->where(array('a.group_id'=>array('in',$group_id),'k.is_delete'=>0,'k.kf_account'=>array('neq',$kf_account)))->select();
            if($kf_list){
                $ky_kf=array();//可用的客服
                foreach($kf_list as $key=>$vo){
                    foreach($result['kf_online_list'] as $k=>$v){
                        $kf_account_arr=explode('@',$v['kf_account']);
                        if($vo['kf_account']==$kf_account_arr[0]&&$v['status']==1){ //同组的其他客户在线放入可用客服
                            $ky_kf[]=array('kf_account'=>$vo['kf_account'],'accepted_case'=>$v['accepted_case']);
                        }
                    }
                }
                if($ky_kf){
                    Log::record('同分组下在线客户未按接待排序'.json_encode($ky_kf),"ERR");
                    $ky_kf=array_order($ky_kf,'accepted_case','SORT_ASC');
                    Log::record('已排序同分组下在线客户'.json_encode($ky_kf).'取第一个',"ERR");
                    return $ky_kf[0]['kf_account'];
                }else{
                    Log::record('同分组下其他人都不在线',"ERR");
                    return '';//同组没有人在线
                }
            }else{
                Log::record('同分组下没有其他会员了',"ERR");
                return '';//同组没有人
            }
        }else{
            //同分组下面没有其他人
            Log::record('没有找到分组信息',"ERR");
            return '';
        }

    }

    /**
     * @param $openid 用户ID
     * @param $kf_account 客服账号完整
     */
    private function save_kf_log($openid,$kf_account){
        if($kf_account){
            $kf_account_arr=explode('@',$kf_account);
            M('WxKfLog')->where(array('openid'=>$openid))->save(array('kf_account'=>$kf_account_arr[0],'create_time'=>time()));
        }
    }
}