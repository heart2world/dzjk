<?php
/**
 * Created by PhpStorm.
 * User: 马军
 * Date: 2017/9/4
 * Time: 14:49
 */
namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use Common\Model\LabelModel;
use Common\Model\HelpModel;
class IndexController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
        $this->LabelModel = new LabelModel();
        $this->article_model = new HelpModel();
        $this->pageNum = 10;
    }
    //搜索
    public function searchArti()
    {
        $this->display();
    }

    //搜索
    public function search()
    {
        $this->display();
    }
    public function mnologin()
    {
        $this->display();
    }

    public function dnologin()
    {
        $this->display();
    }


    public function setLab()
    {

        $larrayA = I('setLab');  //设置的lab
        //设置标签
        if($larrayA)
        {
            $_SESSION['lablist'] = null;
            $_SESSION['lablist'] = $larrayA;
            if($this->member['member_id'])  //如果登录了就同步到数据库
            {
                $id = M('MemberLab')->where(array('uid'=>$this->member['member_id']))->find();
                if($id && $larrayA)
                {
                    $row['lab'] = implode(',',$larrayA);
                    $res = M('MemberLab')->where(array('uid'=>$this->member['member_id']))->save($row);
                }else if($larrayA)
                {
                    M('MemberLab')->add(array('uid'=>$this->member['member_id'],'lab'=>implode(',',$larrayA)));
                }
            }
            $this->success('ok');
        }
    }

    //首页
    public function index()
    {

        //dump(get_site_options());die;

        if(IS_AJAX)
        {
            $pagesize = 8;
            $page = I('p') ? I('p') : 1;


            $larrayA = I('setLab');  //设置的lab
            $larray = I('lablist');  //设置的lab

//            $lastId = M('Article')->order('id desc')->getField('id');
            $neworder=I('neworder','');
            if($larray[0] == 0)   //推荐的数据
            {
                $order='AC.create_time desc,AC.dzs desc';
                $order2='AC.dzs desc';
                //$this->member['member_id']=238;
                if($this->member['member_id'])   //   如果已登录，显示关注的医生的动态
                {
                    $isdl = 1;
                    $whereA['MF.member_id'] = $this->member['member_id'];
                    $count =  M('MemberFollo')->alias('MF')
                        ->field('M.id')
                        ->where($whereA)
                        ->join('LEFT JOIN __MEMBER__ M on M.id = MF.to_id')
                        ->count();
                    $info = M('MemberFollo')->alias('MF')
                        ->where($whereA)
                        ->field('M.id')
                        ->join('LEFT JOIN __MEMBER__ M on M.id = MF.to_id')
                        ->select();

                    $whs = array();
                    $memberGzID = array();
                    if($info) {
                        foreach ($info as $item) {  //关注的医生id
                            $memberGzID[] = $item['id'];
                        }
                        $memberGzID = implode(',',$memberGzID);

                        $map['AC.author'] = array('in', $memberGzID);
                        $map['AC.tj'] = 1;
                        $map['_logic'] = 'OR';

                    }else
                    {
                        $map['AC.tj'] = 1;
                    }

                    $whs['_complex'] = $map;
                    $whs['AC.is_delete'] = 0;

                    $readac=M('article_relog')->where(array('member_id'=>$this->member['member_id']))->select();
                    $logarr=array();
                    foreach ($readac as $logval){
                        $logarr[]=$logval['article_id'];
                    }
                    if($logarr){
                        if(session('artarr')){
                            $logarr=array_merge($logarr,session('artarr'));
                        }
                        $whs['AC.id']=array('not in',$logarr);
                    }
                    else{
                        if(session('artarr')){
                            $whs['AC.id']=array('not in',session('artarr'));
                        }
                    }


                    if($neworder==1){
                        $order='AC.create_time desc';
                    }
                    else if($neworder==2){
                        $order='AC.dzs desc';
                    }
                    $dataDyam = M('Article')->alias('AC')
                        ->field('AC.id,AC.isyc,AC.author,AC.id as aid,AC.type,AC.title,AC.author,AC.create_time,AC.content,AC.label,M.zy,AC.thumb,AC.dzs,AC.pls,AC.fxs,M.nickname,M.avatar,MI.hosp,MI.zw')
                        ->join('LEFT JOIN __MEMBER__ M on M.id = AC.author')
                        ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                        ->order($order)
                        //->order($order2)
                        ->page($page,$pagesize)
                        ->where($whs)
                        ->select();
                    /*dump(M('Article')->getLastSql());
                    dump($dataDyam);
                    dump(count($dataDyam));die;*/
                    if(count($dataDyam)<$pagesize){
                        unset($whs['AC.id']);
                        $dataDyam = M('Article')->alias('AC')
                            ->field('AC.id,AC.isyc,AC.author,AC.id as aid,AC.type,AC.title,AC.author,AC.create_time,AC.content,AC.label,M.zy,AC.thumb,AC.dzs,AC.pls,AC.fxs,M.nickname,M.avatar,MI.hosp,MI.zw')
                            ->join('LEFT JOIN __MEMBER__ M on M.id = AC.author')
                            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                            ->order($order)
                            //->order($order2)
                            ->page($page,$pagesize)
                            ->where($whs)
                            ->select();
                    }

                    //最新文章的id
                    $lastId = M('Article')->order('id desc')->getField('id');
                    //传入的最后id
                    $getLastId = intval(I('lastid'));
                    $showNewStr = 0;
                    if($getLastId > 0 && $lastId > $getLastId)
                    {
                        $showNewStr = 1;
                    }



                }else
                {
                    $where['AC.tj'] = 1;
                }
            }else
            {
                if(count($larray) > 0)
                {
                    $where['AC.label'] = array('in',$larray);
                }
            }

            //设置标签
            if($larrayA)
            {
                $_SESSION['lablist'] = null;
                $_SESSION['lablist'] = $larrayA;
                if($this->member['member_id'])  //如果登录了就同步到数据库
                {
                    $id = M('MemberLab')->where(array('uid'=>$this->member['member_id']))->find();
                    if($id && $larrayA)
                    {
                        $row['lab'] = implode(',',$larrayA);
                        $res = M('MemberLab')->where(array('uid'=>$this->member['member_id']))->save($row);
                    }else if($larrayA)
                    {
                        M('MemberLab')->add(array('uid'=>$this->member['member_id'],'lab'=>implode(',',$larrayA)));
                    }
                }
            }

            if($isdl != 1)   //如果动态不存在，取文章
            {

                $where['AC.is_delete'] = 0;

                $start = ($page - 1) * $pagesize;

                //最新文章的id
                $lastId = M('Article')->order('id desc')->getField('id');
                //传入的最后id
                $getLastId = intval(I('lastid'));
                $showNewStr = 0;
                if($getLastId > 0 && $lastId > $getLastId)
                {
                    $showNewStr = 1;
                }
                if(session('artarr')){

                    //dump(session('artarr'));die;
                    $where['AC.id']=array('not in',session('artarr'));
                }
                $order='AC.create_time desc,AC.dzs desc';
                $data = M('Article')->alias('AC')
                    ->field('AC.id,AC.isyc,AC.author,AC.id as aid,AC.type,AC.title,AC.author,AC.create_time,AC.content,AC.label,M.zy,AC.thumb,AC.dzs,AC.pls,AC.fxs,M.nickname,M.avatar,MI.hosp,MI.zw')
                    ->join('LEFT JOIN __MEMBER__ M on M.id = AC.author')
                    ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                    ->order($order)
                    ->limit($start,$pagesize)
                    ->where($where)
                    ->select();
                if(count($data)<$pagesize){
                    unset($where['AC.is_delete']);
                    $data = M('Article')->alias('AC')
                        ->field('AC.id,AC.isyc,AC.author,AC.id as aid,AC.type,AC.title,AC.author,AC.create_time,AC.content,AC.label,M.zy,AC.thumb,AC.dzs,AC.pls,AC.fxs,M.nickname,M.avatar,MI.hosp,MI.zw')
                        ->join('LEFT JOIN __MEMBER__ M on M.id = AC.author')
                        ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                        ->order($order)
                        ->limit($start,$pagesize)
                        ->where($where)
                        ->select();
                    session('artarr',null);
                }
                //dump(M('Article')->getLastSql());die;
            }else
            {
                $data =  $dataDyam;
            }
            if(!$neworder){
                shuffle($data);
            }
            $temparr1=array();
            $temparr2=array();
            $readartarr=array();
            foreach ($data as $ssitem){
                $time=time()-7*24*60*60;
                if($ssitem['create_time']>=$time){
                    $temparr1[]=$ssitem;
                }
                else{
                    $temparr2[]=$ssitem;
                }
                $readartarr[]=$ssitem['id'];

            }
            if(session('artarr')){
                $artarr=session('artarr');
                $readartarr=array_merge($artarr,$readartarr);
                session('artarr',$readartarr);
            }
            else{
                session('artarr',$readartarr);
            }


            $data=array_merge($temparr1,$temparr2);
            if($this->member['member_id']){
                $temparr1=array();
                $temparr2=array();
                foreach ($data as $ssitem){
                    $sssout=M('article_relog')->where(array('member_id'=>$this->member['member_id'],'article_id'=>$ssitem['aid']))->find();
                    if(!$sssout){
                        $temparr1[]=$ssitem;
                    }
                    else{
                        $temparr2[]=$ssitem;
                    }
                }

                $data=array_merge($temparr1,$temparr2);
            }
            $str = '';
            $zts = ($page-1) * $pagesize + 1;  //已读取的数据

            foreach ($data as $k=>$vo)
            {
                $thumbs = explode(',',$vo['thumb']);

                if($vo['author'] == 0)
                {
                    $vo['nickname'] = '官方发布';
                }
                if($vo['type'] == 1)
                {    //动态

                    $thumbs = explode(',',$vo['thumb']);
                    $info = M('Member')->alias('M')
                        ->field("M.nickname,M.avatar,MI.zw,MI.hosp")
                        ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                        ->where(array('M.id'=>$vo['author'],'M.types'=>2,'M.is_delete'=>0))
                        ->find();
                    $str .='<a  href="/Article/infoDyna/id/'.$vo['aid'].'"><div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">';
                    $str .='<div class="w-paddingTopBottom02 w-borderBeee">';
                    $str .='<div class="w-flex w-marginBottom01">';
                    $str .='<img src="'.$info['avatar'].'" class="header1 w-marginRight02" alt=""/>';
                    $str .='<div class="w-flexitem1">';
                    $str .='<h4 class="w-height03 w-font14 w-color000 w-onlyline">'.$info['nickname'].'</h4>';
                    $str .='<span class="w-block w-height03 w-font12 w-color999 w-onlyline">'.$info['hosp'].$info['zw'].'</span>';
                    $str .='</div>';
                    $str .='</div>';
                    $str .='<div class="threeLine w-marginBottom01" >';
                    $str .= $vo['content'].'</a>';
                    $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
                    $str .='</div>';
//                    $str .='';
                    if(count($thumbs) <= 1)
                    {
                        if($thumbs[0])
                        {

                            $str .='<a href="/Article/infoDyna/id/'.$vo['aid'].'"><div  class="oneImgBox"><img src="'.$thumbs[0].'" alt=""/></div>'.'</a>';

                        }
                    }elseif (count($thumbs) >= 2)
                    {

                        $str .='<a href="/Article/infoDyna/id/'.$vo['aid'].'"><div class="w-font0 threeImgBox w-position w-marginBottom02">';
                        foreach ($thumbs as $k=>$thumb)
                        {
                            if($k <= 2)
                            {
                                $str .='<div class="threeImg" style="mzx-width: "><img src="'.$thumb.'" alt=""/></div>';
                            }
                        }
                        if (count($thumbs) > 3)
                        {
                            $cos = count($thumbs)-3;
                            $str .='<div class="threeFixed"><span>+'.$cos.'</span></div>';
                        }
                        $str .='</div>'.'</a>';
                    }

                    $str .='</div>';
                    $str .='<div class="w-paddingTopBottom02 w-flex">';
                    $str .='<div class="w-flexitem1 w-textalignR">';


                    $str .='<label   class="iconZan w-marginRight02">';

                    $ra = array();
                    $mid = $this->member['member_id'];
                    $ra = M('ArticleGive')->field('id')
                        ->where(array('member_id'=>$mid,
                            'a_id'=>$vo['aid'],'parent_id'=>0))->find();
                    if(!$ra['id'])  //如果没点赞，可以点，
                    {
                        $str .= '<input onclick="giveAction('.$vo['aid'].',this'.')" type="checkbox" />';
                    }else  //已经点赞，不能点，
                    {
                        $str .= '<input  disabled checked type="checkbox" >';
                    }
                    if($vo['dzs'] > 0)
                    {
                        $str .='<span>'.$vo['dzs'].'</span>';
                    }else
                    {
                        $str .='<span></span>';
                    }

                    $str .='</label>';
                    $str .='<a href="javascript:;" class="iconTalk w-marginRight02">'.$vo['pls'].'</a>';
                    $str .='<a data-title="'.strip_tags(html_entity_decode($vo['content'])).'"  data-id="'.$vo['aid'].'"  onclick="shareFunc(this)" href="javascript:;" class="iconShare">'.$vo['fxs'].'</a>';
                    $str .='</div>';
                    $str .='</div>';
                    $str .='</div>';


                }else  if($vo['type'] == 0)  //文章
                {

                    $lab = M('Label')->where(array('id'=>$vo['label']))->getField('name');


                    if($vo['title'] && count($thumbs)<=1)
                    {
                        $str .= '<a  href="/Mobile/Article/info/id/'.$vo['aid'].'"><div class="w-bgcolorFFF w-flex w-padding0302 w-marginBottom01">';
                        $str .= ' <div class="w-flexitem1">';
                        if($vo['isyc'] == 1)
                        {

                            $str .= '<div class="w-height08 w-line04 w-font14 w-color333 w-marginBottom03 w-doubleline"><span class="iszbox">[转]</span>'.$vo['title'].'</div>';
                        }else
                        {
                            $str .= '<div class="w-height08 w-line04 w-font14 w-color333 w-marginBottom03 w-doubleline">'.$vo['title'].'</div>';
                        }


                        $str .= ' <div class="w-height03 w-onlyline w-font12 w-color999">';
                        $str .= '  <span class="w-btn1 w-marginRight02">'.$lab.'</span>';
                        $str .= '  <em class="newName w-marginRight02">'.$vo['nickname'].'</em>';
                        $str .= '  <em>'.$vo['pls'].'评论</em>';
                        $str .= ' </div>';
                        $str .= ' </div>';
                        if($vo['thumb'])
                        {
                            $str .= ' <img src="'.$vo['thumb'].'" alt="" class="goods1"/>';
                        }
                        $str .= ' </div></a>';
                    }else if($vo['title'] && count($thumbs) > 1)
                    {
                        $str .= '<a  href="/Mobile/Article/info/id/'.$vo['aid'].'"><div class="w-bgcolorFFF w-padding02 w-marginBottom01">';
                        if($vo['isyc'] == 1)
                        {
                            $str .= '<div class="threeLine"><span class="iszbox">[转]</span>'.$vo['title'];
                        }else
                        {
                            $str .= '<div class="threeLine">'.$vo['title'];
                        }


                        $str .= ' <span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
                        $str .= ' </div>';
                        $str .= '  <div class="w-font0 threeImgBox w-position w-marginBottom02">';
                        foreach ($thumbs as $kss=>$thum) {
                            if($kss <= 2)
                            {
                                $str .= '<div class="threeImg"><img src="'.$thum.'" alt=""/></div>';
                            }
                        }
                        if(count($thumbs) > 3)
                        {
                            $str .= '      <div class="threeFixed"><span>+'.count($thumbs).'</span></div>';
                        }
                        $str .= ' </div>';
                        $str .= ' <div class="w-height03 w-onlyline w-font12 w-color999">';
                        $str .= '   <span class="w-btn1 w-marginRight02">'.$lab.'</span>';
                        $str .= '   <em class="newName w-marginRight02">'.$vo['nickname'].'</em>';
                        $str .= '   <em>'.$vo['pls'].'评论</em>';
                        $str .= '  </div>';
                        $str .= '  </div></a>';
                    }
                }
                $yjts = $zts + $k ;   //当前执行到的数据  广告
                if($yjts % 5 == 0)
                {
                    if(I('ggid'))
                    {
                        $whereAD['id'] = array('lt',I('ggid'));
                    }

                    $whereAD['st'] = array('lt',time());
                    $whereAD['ent'] = array('gt',time());
                    $whereAD['status'] = 1;
                    $whereAD['is_delete'] = 0;
                    if($larray[0] == 0)
                    {
                        $whereAD['zsqy'] = array('like','%1%');
                    }else
                    {
                        $whereAD['zsqy'] = array('like','%2%');
                        $whereAD['lab'] = $larray[0];
                    }

                    $res = M('Adve')
                        ->where($whereAD)
                        ->field('id,links,pic,title')
                        ->order('id desc')
                        ->limit(1)
                        ->find();
                    if($res['pic'])
                    {

//                        if($this->member['member_id'])
//                        {
//                            M('Adve')->where(array('id'=>$res['id']))->setInc('visit',1);
//                        }

                        $str .= '<a href="'.$res['links'].'"><div class="w-padding02 w-bgcolorFFF w-marginBottom01">';
                        $str .= '<div class="w-font14 w-line04 w-color333 w-imgBox advtise">';
                        $str .= '<span class="adv">广告</span>'.$res['title'];
                        $str .= '<img src="'.$res['pic'].'" alt=""/>';
                        $str .= '</div>';
                        $str .= '</div></a>';
                    }
                }


                //展示更新提示
                if($showNewStr == 1)
                {
                    $str .= '<div class="w-padding0102 w-marginBottom01 w-bgcolorFFF w-textalignC w-color44D397 w-font0">';
                    $str .= '<em onclick="urlJumpLoca()" class="w-verticalMiddle w-marginRight01 w-font14 w-inlineblock w-height03">之前看到这里，点击刷新</em>';
                    $str .= '<b class="iconRefresh"></b>';
                    $str .= '</div>';
                    $showNewStr = 0;
                }

            }
            if($str)
            {
                $newRow['str'] = $str;
                $newRow['ggid'] = $res['id'];
                $newRow['lastId'] = $lastId;
                $this->success($newRow);
            }else
            {
                $this->error('nodata');
            }

//            var_dump($data);



        }else
        {

            $ress = M('MemberLab')->field('lab')->where(array('uid'=>$this->member['member_id']))->find();

            if($ress['lab'])
            {
                $labelListShow = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$ress['lab'])))->select();
            }else if($_SESSION['lablist'])
            {
                $labelListShow = $res = M('Label')->field('id,name')->where(array('id'=>array('in',$_SESSION['lablist'])))->select();

//                $labelListShow = $this->LabelModel->get_list();
            }else
            {
                $labelListShow = $this->LabelModel->get_list();
            }


            $labelList = $this->LabelModel->get_list();
            foreach ($labelList as $k=>&$item)
            {
                $k = $k + 1;
                if($k <= 3)
                {
                    $item['color'] = 'h';

                }elseif ($k <= 6)
                {
                    $item['color'] = 'Orange';

                }elseif ($k <= 9)
                {
                    $item['color'] = 'blue';

                }
                elseif ($k <= 12)
                {
                    $item['color'] = 'h';

                }
                elseif ($k <= 15)
                {
                    $item['color'] = 'Orange';

                }
                elseif ($k <= 18)
                {
                    $item['color'] = 'blue';
                }
            }


            $minfo = M('Member')->field('status')->where(array('id'=>$this->member['member_id']))->find();
            $this->isdj = $minfo['status'];

            $this->labelList = $labelList;
            $this->labelListShow = $labelListShow;
            $this->display();
        }
    }


//    public function t()
//    {
//        if(count($thumbs)<=1)
//        {
//            $str .= ' <div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">';
//            $str .= '<div class="w-paddingTopBottom02 w-borderBeee">';
//            $str .= '<div class="w-flex w-marginBottom01">';
//            $str .= '<img src="'.$vo['avatar'].'" class="header1 w-marginRight02" alt=""/>';
//            $str .= '<div class="w-flexitem1">';
//            $str .= '<h4 class="w-height03 w-font14 w-color000 w-onlyline">'.$vo['nickname'].'</h4>';
//            $str .= '<span class="w-block w-height03 w-font12 w-color999 w-onlyline">'.$vo['hosp'].$vo['zw'].'</span>';
//            $str .= '</div>';
//            $str .= '</div> ';
//            $str .= '<div class="threeLine w-marginBottom01" style="height: 60px;">';
////                        $str .= '<a href="/Mobile/Article/infoDyna/id/'.$vo['aid'].'">'.$vo['content'].'</a>';
////                        $str .= '<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
//
//            $str .= '<a href="/Article/infoDyna/id/'.$vo['aid'].'">'.$vo['content'].'</a>';
//            $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
//
//            $str .= '</div>';
//            $str .= '<div class="w-imgBox">';
//
//            if($vo['thumb'])
//            {
//                $str .= '<img src="'.$vo['thumb'].'" alt=""/>';
//            }
//
//            $str .= '</div>';
//            $str .= '</div>';
//            $str .= '<div class="w-paddingTopBottom02 w-flex">';
//
//            $ra = M('MemberFollo')->field('id')->where(array('member_id'=>$this->member['member_id'],'to_id'=>$vo['author']))->find();
//            if($ra['id'])
//            {
////                            $str .= '<span>已关注</span>';
//            }else
//            {
//                $str .= '<label   class="iconCollect">';
//                $str .= '<input onclick="gzbtn('.$vo['author'].')" type="checkbox" />';
//                $str .= '<span></span>';
//                $str .= '</label>';
//            }
//            $str .= ' <div class="w-flexitem1 w-textalignR">';
//            $str .= ' <label  class="iconZan w-marginRight02">';
//
//            //当前用户是否点赞过
//            $ra = array();
//            $ra = M('ArticleGive')->field('id')
//                ->where(array('member_id'=>$this->member['member_id'],
//                    'a_id'=>$vo['aid'],'parent_id'=>0))->find();
//            if(!$ra['id'])
//            {
//                $str .= '<input onclick="giveAction('.$vo['aid'].',this'.')" type="checkbox" />';
//            }else
//            {
//                $str .= '<input disabled  checked type="checkbox" id="zan2">';
//            }
//
//            $str .= '    <span>'.$vo['dzs'].'</span>';
//            $str .= '    </label>';
//            $str .= '     <a href="javascript:;" class="iconTalk w-marginRight02">'.$vo['pls'].'</a>';
//            $str .= '     <a  data-title="'.$vo['content'].'"  data-id="'.$vo['aid'].'"  onclick="shareFunc(this)" class="iconShare">'.$vo['fxs'].'</a>';
//            $str .= '   </div>';
//            $str .= ' </div>';
//            $str .= ' </div>';
//        }else if(count($thumbs) > 1)
//        {
//            $str .= '  <div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">';
//            $str .= '<div class="w-paddingTopBottom02 w-borderBeee">';
//            $str .= ' <div class="w-flex w-marginBottom01">';
//            $str .= '    <img src="'.$vo['avatar'].'" class="header1 w-marginRight02" alt=""/>';
//            $str .= '   <div class="w-flexitem1">';
//            $str .= '      <h4 class="w-height03 w-font14 w-color000 w-onlyline">'.$vo['nickname'].'</h4>';
//            $str .= '       <span class="w-block w-height03 w-font12 w-color999 w-onlyline">'.$vo['hosp'].$vo['zw'].'</span>';
//            $str .= '    </div>';
//            $str .= ' </div> ';
//            $str .= ' <div class="threeLine" style="height: 60px;">';
////                        $str .=  '<a href="/Mobile/Article/infoDyna/id/'.$vo['aid'].'">'.$vo['content'].'</a>';
////                        $str .= '    <span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
//
//            $str .= '<a href="/Article/infoDyna/id/'.$vo['aid'].'">'.$vo['content'].'</a>';
//            $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
//
//            $str .= '  </div>';
//            $str .= '   <div class="w-font0 threeImgBox w-position">';
//            foreach ($thumbs as $kss=>$thum)
//            {
//                if($kss <= 2)
//                {
//                    $str .= '       <div class="threeImg"><img src="'.$thum.'" alt=""/></div>';
//                }
//            }
//            if(count($thumbs) > 3)
//            {
//                $str .= '      <div class="threeFixed"><span>+'.count($thumbs).'</span></div>';
//            }
//
//            $str .= '    </div>';
//            $str .= '   </div>';
//            $str .= '  <div class="w-paddingTopBottom02 w-flex">';
//            $ra = M('MemberFollo')->field('id')->where(array('member_id'=>$this->member['member_id'],'to_id'=>$vo['author']))->find();
//            if($ra['id'])
//            {
////                            $str .= '<span onclick="gzbtn('.$vo['author'].')">已关注</span>';
//            }else
//            {
//                $str .= '   <label   class="iconCollect">';
//                $str .= '   <input onclick="gzbtn('.$vo['author'].')" type="checkbox" />';
//                $str .= '    <span></span>';
//                $str .= ' </label>';
//            }
//
//            $str .= '   <div class="w-flexitem1 w-textalignR">';
//            $str .= '   <label   class="iconZan w-marginRight02">';
//            //当前用户是否点赞过
//            $ra = M('ArticleGive')
//                ->where(array('member_id'=>$this->member['member_id'],
//                    'a_id'=>$vo['aid'],'parent_id'=>0))->find();
//            if(!$ra)
//            {
//                $str .= '<input onclick="giveAction('.$vo['aid'].',this'.')" type="checkbox" />';
//            }else
//            {
//                $str .= '<input  checked type="checkbox" id="zan2">';
//            }
//            $str .= '         <span>'.$vo['dzs'].'</span>';
//            $str .= '      </label>';
//            $str .= '      <a href="javascript:;" class="iconTalk w-marginRight02">'.$vo['pls'].'</a>';
//            $str .= '   <a data-title="'.$vo['content'].'"  data-id="'.$vo['aid'].'"  onclick="shareFunc(this)" href="javascript:;" class="iconShare">'.$vo['fxs'].'</a>';
//            $str .= ' </div>';
//            $str .= ' </div>';
//            $str .= ' </div>';
//        }
//    }

    //图片上传
    public function upload_img(){
        $config = array(
            'maxSize' => 3145728,
            'savePath' => '',
            'saveName' => array('uniqid', 'ehecd'),
            'rootPath'   =>    './data/upload/',
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'subName'    =>    array('date','Ymd'),
        );
        $upload = new \Think\Upload($config);// 实例化上传类
        $images = $upload->upload();

        //判断是否有图
        if ($images) {
            $image = new \Think\Image();
            $img_list=array();
            $img_lists=array();
            //$siteurl=get_siteurl();
            foreach($images as $key=>$img){
                $url = '/data/upload/' . $img['savepath'] . $img['savename'];
                $image->open($_SERVER['DOCUMENT_ROOT'].$url);
                $image->thumb(640, 640)->save($_SERVER['DOCUMENT_ROOT'].'/data/upload/' . $img['savepath'].'thumb_'.$img['savename']);
                $img_list=array(
                    'url'=>$url,
                    'thumb_url'=>'/data/upload/' . $img['savepath'].'thumb_'.$img['savename']
                );
                $img_lists[] = $img_list;
            }
            $this->ajaxReturn(array('data'=>$img_lists,'status'=>100));
        } else {
            $this->error($upload->getError());//获取失败信息
        }
    }
    //登录
    public function login()
    {
        $site_options = getOptions('site_options');
        $this->content = $site_options['content'];
        //dump(ACTION_NAME);exit();
        if(ACTION_NAME=='login') {
            $tiaourl = I('tiaourl') ? I('tiaourl') : $_SERVER['HTTP_REFERER'];
            //dump($tiaourl);
            session("tiaourl", $tiaourl);
        }
        $this->display();
    }

    //关于我们
    public function about()
    {
        $site_options = getOptions('site_options');
        $this->about = $site_options['content2'];
        $this->display();
    }

    //帮助中心 详情
    public function helpInfo()
    {
        $id = I('id',0,'intval');
        if($id < 0)
        {
            $this->error('参数错误');
        }else
        {
            $info = M('Help')->where(array('id'=>$id))->field('content,create_time,title')->find();
            if($info)
            {
                M('Help')->where(array('id'=>$id))->setInc('shownum',1);
                $info['content'] = htmlspecialchars_decode($info['content']);
                $info['create_time'] = date('Y.m.d H:i:s',$info['create_time']);
                $this->info = $info;
            }

        }
        $this->display();
    }
    //帮助中心
    public function help()
    {
        if(IS_AJAX)
        {
            $p = I('p',1,'intval');
            $data = $this->article_model->get_list(array(),array(),$p,$this->pageNum,'title,id,content');
            $str .= '';
            if($data['list'])
            {
                foreach ($data['list'] as $datum)
                {
                    $str .= '<a href="/Index/helpInfo/id/'.$datum['id'].'" class="p-v-15">';
                    $str .= '<h4>'.$datum['title'].'</h4>';
                    $str .= '<p class="only_line">'.mb_substr(strip_tags(html_entity_decode($datum['content'])),0,40,'utf-8').'</p>';
                    $str .= '</a>';
                }
            }
            echo $str;
        }else
        {
            $this->display();
        }

//        var_dump($data['list']);


//        <a href="javascript:;" class="p-v-15">
//                <h4>帮助中心标题</h4>
//                <p class="only_line">文字文字文字文字文字文字文字文字文字文字</p>
//
//            </a>
//

    }


//    public function index()
//    {
//        //根据定位城市获取首页banner
//        $data['city']=$this->current_city_id?$this->current_city_id:322;
//        $data['type']=1;
//        $banner_1=$this->BannerModel->get_banner($data);
//        //dump($banner_1);die;
//        $data['type']=2;
//        $banner_2=$this->BannerModel->get_banner($data);
//        $this->assign('banner_1',$banner_1);
//        $this->assign('banner_2',$banner_2);
//        //获取限时秒杀
//        $seckill=$this->DiscountModel->get_lately_seckill();
//        $this->assign('seckill',$seckill);
//        //dump($seckill);die;
//        //获取推荐活动
//        $recommend_active=$this->TicketActiveModel->get_recommend_active();
//        $this->assign('recommend_active',$recommend_active);
//        //当地攻略
//        $strategy=$this->StrategyModel->get_list(array('destination_2'=>$this->current_destination_id),'',1,5);
//        if(!$strategy['list']){
//            $strategy=$this->StrategyModel->get_list(array('destination_2'=>12),'',1,5);
//        }
//        $this->assign('strategy',$strategy['list']);
//        $interlocution=$this->InterlocutionModel->get_index_list(array('city'=>$this->current_destination_id),'i.sort asc',1,5);
//        if(!$interlocution['list']){
//            $interlocution=$this->InterlocutionModel->get_index_list(array('city'=>12),'i.sort asc',1,5);
//        }
//        //dump($interlocution);die;
//        foreach($interlocution['list'] as $k=>$y){
//            if($y['ci_content']){
//                $content_01 = $y['ci_content'];//从数据库获取富文本content
//                $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
//                $content_03 = str_replace("&nbsp;","",$content_02);//将空格替换成空
//                $interlocution['list'][$k]['ci_content'] = strip_tags($content_03,'<br/><img/><p>');
//            }
//        }
//        $this->assign('interlocution',$interlocution['list']);
//        //AA组队
//        $data1['depart_city'] = $this->current_city_id;
//        $data1['first'] = 1;
//        $data1['p'] = 1;
//        $data1['pageNum'] = 5;
//        $a_item = $this->AItemModel->get_list($data1);
//        $this->assign('a_item',$a_item ['list']);
//        $this->assign('current_address',$this->current_address);
//        $this->display();
//    }
    //获取首页问答
    public function get_interlocution(){
        if(IS_AJAX){
            //推荐问答
            $interlocution=$this->InterlocutionModel->get_index_list(array('city'=>$this->current_destination_id),'i.sort asc',1,3);
            if(!$interlocution['list']){
                $interlocution=$this->InterlocutionModel->get_index_list(array('city'=>12),'i.sort asc',1,3);
            }
            //dump($interlocution);die;
            foreach($interlocution['list'] as $k=>$y){
                if($y['ci_content']){
                    $content_01 = $y['ci_content'];//从数据库获取富文本content
                    $content_02 = htmlspecialchars_decode($content_01);//把一些预定义的 HTML 实体转换为字符
                    $content_03 = str_replace("&nbsp;","",$content_02);//将空格替换成空
                    $interlocution['list'][$k]['ci_content'] = strip_tags($content_03,'<br/><img/><p>');
                }
            }
            $this->success($interlocution['list']);
        }
    }
    //获取首页门票
    public function get_ticket(){
        if(IS_AJAX){
            $type=I('type',5,'intval');
            $data['status']=1;
            $data['auditing_status']=2;
            $data['departure_city']=$this->current_city_id?$this->current_city_id:322;
            if($type==1){
                //熊猫趣玩
                $data['a_type']=$type;
                $all=$this->get_active($data,1,5);
            }elseif($type==2){
                //熊猫户外
                $data['a_type']=$type;
                $all=$this->get_active($data,1,5);
            }elseif($type==3){
                //熊猫游
                $data['a_type']=$type;
                $all=$this->get_active($data,1,5);
            }elseif($type==4){
                //景点门票
                $data['lng']=$this->current_location_lng;
                $data['lat']=$this->current_location_lat;
                $all1=$this->TicketScenicModel->get_index_list($data,'juli asc',1,5);
                $all=$all1['list'];
            }elseif($type==5){
                //附近的
                $data['lng']=$this->current_location_lng;
                $data['lat']=$this->current_location_lat;
                if($this->current_location_lng&&$this->current_location_lat){
                    $all=M()
                        ->table('naber_ticket')
                        ->field("*,GetDistance(lng,lat,$this->current_location_lng,$this->current_location_lat) as juli")
                        ->order('juli asc')
                        ->limit(5)
                        ->select();
                }
            }
            foreach($all as $k=>$v){
                $all[$k]['cover_pic']=get_img_path($v['cover_pic'],340);
                if($v['type']==1){
                    $all[$k]['min_price']=$this->TicketScenicModel->calculated_min_price($v['id']);
                }else{
                    if(!$v['a_type_name']){
                        $all[$k]['a_type_name']=M('TicketActiveType')->where(array('id'=>$v['a_type']))->getField('name');
                    }
                    $all[$k]['min_price']=$this->TicketActiveModel->calculated_min_price($v['id']);
                }
                if($type==5){
                    $all[$k]['juli']=number_format(($v['juli']/1000),2);
                }
                $label=M('Label')->where(array("id"=>array('in', $v['label'])))->getField('name',true);
                $all[$k]['label']=$label;
            }
            $this->success($all);
        }
    }

    //获取定位城市排序活动
    public function get_active($data,$p,$page){
        $all=$this->TicketActiveModel->get_index_list($data,'ts.sort asc',$p,$page);
        if(count($all['list'])<1){
            $data['departure_city']='';
            $all=$this->TicketActiveModel->get_index_list($data,'ts.sort asc',$p,$page);
        }
        //dump($all['list']);die;
        return $all['list'];
    }

}