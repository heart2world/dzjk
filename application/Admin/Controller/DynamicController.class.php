<?php

/*
 * 操作日志
 * */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Model\ArticleModel;
class DynamicController extends AdminbaseController
{
	public function __construct()
	{
		parent::__construct();
        $this->article_model = new ArticleModel();
        $this->pageNum = 10;
	}

	public function index()
	{
		if (IS_AJAX ) {

//		    $p=I('p',1,'intval');
//			$user_login = trim(I('user_login','',''));
//            $type=I('type',0,'intval');
//			$map=array();
//			if($user_login){
//				$map['user.user_login|log.note']=array('like',"%$user_login%");
//                $type=(int)$this->syslog_type_model->where(array('name'=>$user_login))->getField('id');
//			}
//			$st_time=I('st_time');
//			$end_time=I('end_time');
//			if(!empty($st_time)&&!empty($end_time)){
//				$map['log.create_time']=array('between',array(strtotime($st_time),strtotime($end_time)+86400));
//			}
//			elseif (!empty($st_time)&&empty($end_time)) {
//				$map['log.create_time']=array("gt",strtotime($st_time));
//			}
//			elseif (empty($st_time)&&!empty($end_time)) {
//				$map['log.create_time']=array('lt',strtotime($end_time)+86400);
//			}
//
//
//			if($type > 0){
//				$map['log.type']=$type;
//			}
//			$count=$this->syslog_model->alias('log')
//				->join('LEFT JOIN __USERS__ user on log.operators=user.id')
//				->where($map)
//				->count();
//
//			$list=$this->syslog_model->alias('log')
//				->field('log.*,type.name')
//				->join("LEFT JOIN __USERS__ user on log.operators=user.id")
//				->join("LEFT JOIN __SYSLOG_TYPE__ type on log.type=type.id")
//				->where($map)
//				->order('log.create_time desc,log.id desc')
//				->page($p,$this->pageNum)
//				->select();
//			foreach($list as $key=>$vo){
//				if($vo['o_type']==1){
//					$list[$key]['user_login']=M('Member')->where(array('id'=>$vo['operators']))->getField('username');
//				}else{
//					$list[$key]['user_login']=M('Users')->where(array('id'=>$vo['operators']))->getField('user_login');
//				}
//				$list[$key]['create_time']=date('Y-m-d H:i:s',$vo['create_time']);
//			}
//			$totalPage = ceil($count/$this->pageNum);
//			$this->ajaxReturn(array('list'=>$list,'totalPage'=>$totalPage,'status'=>1));



//            $map['user.user_login|log.note']=array('like',"%$user_login%");


            $search['keywords'] = I('keywords');
            $p = I('p',1,'intval');
            $search['st_time'] = I('st_time',0,'strtotime');
            $search['end_time'] = I('end_time',0,'strtotime');

            $data = $this->article_model->get_list($search,'',$p,$this->pageNum,1);
            if($data['list'])
            {
                foreach ($data['list'] as &$datum)
                {
                    $datum['content'] = mb_substr($datum['content'],0,20,'utf-8');
                    $imgs = explode(',',$datum['thumb']);
                    $datum['thumb'] = $imgs[0];
                }
            }
            $this->success($data);

		}else{
//			$type_list=$this->syslog_type_model->select();
//			$this->assign('type_list',$type_list);
			$this->display();
		}
	}

}