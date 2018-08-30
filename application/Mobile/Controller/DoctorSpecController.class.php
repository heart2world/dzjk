<?php
/**
 * Created by PhpStorm.
 * User: 马军
 * Date: 2018/1/18
 * Time: 14:28
 */

namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use Common\Model\LabelModel;
use Common\Model\MemberModel;
class DoctorSpecController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
        $this->LabelModel = new LabelModel();
        $this->MemberModel = new MemberModel();
    }

    public function search()
    {
        $p = I('p')?I('p'):1;
        $type = I('type');
        $pagesize = 10;
        $start = ($p - 1) * $pagesize;
        $kw = trim(I('kw'));
        if(!$kw)
        {
            $info = array();
        }else
        {
        $where['m.nickname|MI.hosp'] = array('like',"%$kw%");
        $where['m.types'] = 2;
        $where['m.is_delete'] = 0;
        $info = M('Member')->alias('m')
            ->field('m.id,m.avatar,m.nickname,m.id as mid,MI.hosp,MI.zw,MI.intro,MI.grjs')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on m.id = MI.pid')
            ->where($where)
            ->limit($start,$pagesize)
            ->select();

        if($info)
        {
            foreach ($info as &$item) {


                if($item['id'] == 0)
                {
                    $opt = getOptions('site_options');
                    $item['grjs'] = $opt['intro'];
                    $item['avatar'] = $opt['pic'];
                    $item['hosp'] = '番茄医学官方平台';

                }


                $item['fss'] = M('MemberFollo')->where(array('to_id'=>$item['id']))->count();

                $res = M('MemberFollo')->where(array('member_id'=>$this->member['member_id'],'to_id'=>$item['mid']))->find();
                $item['isgz'] = $res > 0 ? 1 : 0;
                $item['isshow'] = 1;
                if($this->member['member_id'] == $item['mid'])
                {
                    $item['isshow'] = 0;
                }
            }
        }

        $count = M('Member')->alias('m')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on m.id = MI.pid')
            ->where($where)
            ->count();


        }

        $total_page = ceil($count / $pagesize);
        $result['list'] = $info;
        $result['p'] = $p;
        $result['total'] = $count;
        $result['pagesize'] = $pagesize;
        $result['total_page'] = $total_page;

        $this->success($result);

    }

    public function index()
    {

        if(IS_AJAX)
        {
//            index
            $search['type'] = I('type');
            $search['types'] = I('types');
            $p = I('p');
            $info = $this->MemberModel->getList($search,$p,$this->member['member_id'],10,'fss desc');
//            var_dump($info);
            if($info)
            {
                $this->success($info);
            }else
            {
                $this->error('没有数据');
            }

        }else
        {

//            $labelList = $this->LabelModel->get_list();


            $ress = M('MemberLab')->field('lab')->where(array('uid'=>$this->member['member_id']))->find();

            if($ress['lab'])
            {
                $labelList = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$ress['lab'])))->select();
            }else if($_SESSION['lablist'])
            {
                $labelList = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$_SESSION['lablist'])))->select();

//                $labelListShow = $this->LabelModel->get_list();
            }else
            {
                $labelList = $this->LabelModel->get_list();
            }



//            if($_SESSION['lablist'])
//            {
//                $labelList = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$_SESSION['lablist'])))->select();
//            }else
//                {
//                    $labelList = $this->LabelModel->get_list();
//                }


            $minfo = M('Member')->field('status')->where(array('id'=>$this->member['member_id']))->find();
            $this->isdj = $minfo['status'];


            $this->labelList = $labelList;
            $this->display();
        }


    }

}