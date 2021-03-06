<?php

namespace Mobile\Controller;

use Common\Model\ArticleModel;
use Mobile\Controller\CommonController;

class DoctorController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
        $this->artiModel = new ArticleModel();
    }


    public function index()
    {
        $id = I('id');
        if(IS_AJAX)
        {
             $data = I('');
             if($data['status'] == 'wz')
             {
                 $where['type'] = 0;
             }else  if($data['status'] == 'dt')
             {
                 $where['type'] = 1;
             }
            $where['is_delete'] = 0;
            $where['author'] = $data['id'];
            $data = $this->artiModel->get_list_phone($where,$this->member['member_id'], $data['p']);
            if($data)
            {
                echo $data;
            }

        }else
        {
            
            if($id == 0)
            {
                $memberInfo = array();
                $memberInfo['truename'] = '官方发布';
                $memberInfo['hosp'] = '番茄医学官方发布';
                $opt = getOptions('site_options');
                $memberInfo['grjs'] = $opt['intro'];
                $memberInfo['avatar'] = $opt['pic'];

            }else
            {
                $memberInfo = M('Member')->alias('M')
                    ->field("M.truename,M.nickname,M.zy,M.fss,MI.zw,MI.hosp,M.avatar,MI.intro,MI.grjs")
                    ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                    ->where(array('M.id'=>$id,'M.types'=>2,'M.is_delete'=>0))
                    ->find();
            }

            $memberInfo['fss'] = M('MemberFollo')->where(array('to_id'=>$id))->count();

            $memberInfo['zy'] = M('Label')->where(array('id'=>$memberInfo['zy']))->getField('name');  //专业
            $memberInfo['dynas'] = M('Article')->where(array('author'=>$id,'type'=>1,'is_delete'=>0))->count();       //动态数
            $memberInfo['wzs'] = M('Article')->where(array('author'=>$id,'type'=>0,'is_delete'=>0))->count();      //文章数
            $mid = $this->member['member_id'];

            if($mid != $id)
            {
                $memberInfo['showgz'] = 1;
            }

            $isgz = M('MemberFollo')
                ->where(array('member_id'=>$this->member['member_id'],'to_id'=>$id))->find();
            if($isgz)
            {
                $memberInfo['isgz'] = 1;
            }else
           {
               $memberInfo['isgz'] = 0;
           }
            $minfo = M('Member')->field('status')->where(array('id'=>$mid))->find();
            $this->isdj = $minfo['status'];

            $this->memberInfo = $memberInfo;
            $this->display();
        }
    }

    /**
     * [changecity 切换城市]
     * @return [type] [description]
     */
    public function changecity()
    {
        $this->assign('current_provinceName',$_SESSION['current_provinceName']);
        $this->assign('current_cityName',$_SESSION['current_cityName']);
        $this->display();
    }

    public function getprocitylist()
    {
        // 省级数组
        $province =M('region')->where("is_delete=0 and parent_id=0")->order('region_id asc')->select();
        $provincestr=array();
        $citystr2=array();
         $citystr=array();
        foreach ($province as $key => $val) {
            $provincestr[$key]['name'] =$val['region_name'];
            $provincestr[$key]['ids'] ="#citys".$val['region_id'];
            $citystr[$key]['name'] =$val['region_name'];
            $citystr[$key]['ids'] ="citys".$val['region_id'];
            $city =M('region')->where("parent_id='".$val['region_id']."' and is_delete=0")->order('region_id asc')->select();
            foreach ($city as $k => $value) {
                $citystr2[$k]['name'] =$value['region_name'];
            }
            $citystr[$key]['arr']=$citystr2;
            $citystr2=array();
        }

        $data =array('province'=>$provincestr,'citystr'=>$citystr);
        $this->ajaxReturn($data);
    }
}