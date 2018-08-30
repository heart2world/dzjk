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
use Common\Model\ArticleModel;
class ArticleController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
        $this->LabelModel = new LabelModel();
        $this->artiModel = new ArticleModel();
    }
    public function index()
    {

        $this->display();
    }


    public function search()
    {

        $p = I('p')?I('p'):1;
        $pagesize = 10;
        $start = ($p - 1) * $pagesize;
        $kw = trim(I('kw'));
        if(!$kw)
        {
            $info = array();
        }else
        {
//            $where['a.title|a.content'] = array('like',"%$kw%");
            $where['a.title'] = array('like',"%$kw%");
            $where['a.is_delete'] = 0;
            $info = M('Article')->alias('a')
                ->where($where)
                ->field('MI.hosp,MI.zw,M.nickname,M.avatar,a.*')
                ->join('LEFT JOIN __MEMBER__ M on a.author = M.id')
                ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id= MI.pid')
                ->limit($start,$pagesize)
                ->select();

            $count = M('Article')->alias('a')
//                ->join('LEFT JOIN __MEMBER__ M on m.id = a.author')
//                ->join('LEFT JOIN __MEMBER_INTRO__ MI on MI.pid = M.id')
                ->where($where)
                ->count();
        }


        if($info)
        {
            foreach ($info as &$item)
            {
                $item['thumb'] = explode(',',$item['thumb']);
//                if(strlen($item['thumb']) > 30)
//                {
//
//                }
                $item['tpzs'] = count($item['thumb']);
                $item['tpzsShow'] = count($item['thumb']) - 3;
                $new =   array();
                foreach ($item['thumb'] as $ks=>$itemAA)
                {
                    if($ks <= 2)
                    {
                        $new[]  = $itemAA;
                    }
                }
                $item['thumb'] = $new;
                $item['content'] = strip_tags(html_entity_decode($item['content']));

                $item['label'] = M('Label')->where(array('id'=>$item['label']))->getField('name');

                //当前用户是否点赞过
                $ra = M('ArticleGive')->field('id')
                    ->where(array('member_id'=>$this->member['member_id'],'parent_id'=>0,
                        'a_id'=>$item['id']))->find();
                $item['isz'] = 0;
                if($ra)
                {
                    $item['isz'] = 1;
                }
                //当前用户是否关注
                $isgz = M('MemberFollo')->field('id')
                    ->where(array('member_id'=>$this->member['member_id'],'to_id'=>$item['author']))->find();
                $opt = getOptions('site_options');

                if($item['author'] == 0)
                {
//                    $opt = getOptions('site_options');
//                    $item['grjs'] = $opt['intro'];
//                    $item['avatar'] = $opt['pic'];
//                    $item['hosp'] = '番茄健康官方平台';

                }

                $item['isgz'] = 0;
                if($isgz)
                {
                    $item['isgz'] = 1;
                }
            }
        }

        $total_page = ceil($count / $pagesize);
        $result['list'] = $info;
        $result['p'] = $p;
        $result['total'] = $count;
        $result['pagesize'] = $pagesize;
        $result['total_page'] = $total_page;

        $this->success($result);
    }

    //文章和动态
    public function arti_and_dyna()
    {
        if(IS_AJAX)
        {
            $data = I('');
            if($data['status'] == 'wz')
            {
                $where['AC.type'] = 0;
            }else  if($data['status'] == 'dt')
            {
                $where['AC.type'] = 1;
            }
            $where['AC.is_delete'] = 0;
            $where['author'] = $this->member['member_id'];
            $data = $this->artiModel->get_list_data($where, $data['p']);

            if($data)
            {
                echo $data;
            }

        }else
        {
            $this->display();
        }

    }

    public function infoDyna()
    {
        $id = intval(I('id'));
        if($id <= 0)
        {
            $this->error('参数错误');
        }

        $setRds = M('Article')->where(array('id'=>$id))->setInc('shownum',1);

        $mid = $this->member['member_id'];
        $data = M('Article')->where(array('id'=>$id))->find();
        $info = M('Member')->alias('M')
            ->field("M.nickname,M.avatar,MI.zw,MI.hosp,M.iszx")
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where(array('M.id'=>$data['author'],'M.types'=>2,'M.is_delete'=>0))
            ->find();
        $data['avatar'] = $info['avatar'];
        $data['nickname'] = $info['nickname'];
        $data['hosp'] = $info['hosp'].$info['zw'];
        if($mid != $data['author'])
        {
            $data['showgz'] = 1;
        }

        $data['showgz'] = 1;  //测试用。

        $isgz = M('MemberFollo')->field('id')->where(array('member_id'=>$this->member['member_id'],'to_id'=>$data['author']))->find();

        if($isgz)
        {
            $memberInfo['isgz'] = 1;
        }else
        {
            $memberInfo['isgz'] = 0;
        }
        $data['content'] = html_entity_decode($data['content']);
        $data['content1'] = strip_tags(html_entity_decode($data['content']));

        $data['thumb'] = explode(',',$data['thumb']);
        $ra = M('ArticleGive')->where(array('member_id'=>$this->member['member_id'],'a_id'=>$id,'parent_id'=>0))->find();
        $data['isz'] = 0;
        if($ra)
        {
            $data['isz'] = 1;
        }

        $isgz = M('MemberFollo')
            ->where(array('member_id'=>$this->member['member_id'],'to_id'=>$data['author']))->find();
        if($isgz)
        {
            $data['isgz'] = 1;
        }else
        {
            $data['isgz'] = 0;
        }

        $minfo = M('Member')->field('status')->where(array('id'=>$mid))->find();
        $this->isdj = $minfo['status'];

//        $issc = M('MemberCollection')
//            ->where(array('member_id'=>$this->member['member_id'],'type'=>1,
//                'to_id'=>$id))->find();
//        $data['issc'] = 0;
//        if($issc)
//        {
//            $data['issc'] = 1;
//        }

        $this->data = $data;
        $this->display();
    }




    public function info()
    {
        $id = intval(I('id'));
        if($id <= 0)
        {
            $this->error('参数错误');
        }
         M('Article')->where(array('id'=>$id))->setInc('shownum',1);

        $mid = $this->member['member_id'];
        $data = M('Article')->where(array('id'=>$id))->find();
        //dump($mid);die;
        if($mid){
            M('article_relog')->add(array('member_id'=>$mid,'article_id'=>$id));
        }
        $info = M('Member')->alias('M')
            ->field("M.id as mid,M.nickname,M.mobile,M.types,M.is_ok,M.avatar,MI.zw,MI.hosp,M.iszx")
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where(array('M.id'=>$data['author'],'M.types'=>2,'M.is_delete'=>0))
            ->find();

        if($info['mid'] == 0)
        {
            $pic = getOptions('site_options');
            $data['avatar'] = $pic['pic'];

        }else
        {
            $data['avatar'] = $info['avatar'];
        }
        $thumarr=explode(',',$data['thumb']);
        $data['thumarr']=$thumarr;
        if($thumarr){
            $data['firstthum']=$thumarr[0];
        }
        
        $data['nickname'] = $info['nickname'];
        $data['hosp'] = $info['hosp'].$info['zw'];
        $data['iszx'] = $info['iszx'];
        $opti = getOptions('site_options');
        $this->tel = $opti['order_cancel_time'];
//var_dump( $data['iszx']);
        //平台联系电话

        if($data['author'] == 0)
        {
            $data['nickname'] = '官方发布';
        }


        if($mid != $data['author'])
        {
            $data['showgz'] = 1;
        }

        //当前用户是否关注
        $isgz = M('MemberFollo')->field('id')
            ->where(array('member_id'=>$this->member['member_id'],'to_id'=>$data['author']))->find();

        $data['isgz'] = 0;
        if($isgz)
        {
            $data['isgz'] = 1;
        }
        $data['content'] = html_entity_decode($data['content']);
        $data['contentT'] = strip_tags(html_entity_decode(str_replace("<br>","",$data['content'])));

        $ra = M('ArticleGive')->where(array('member_id'=>$this->member['member_id'],'a_id'=>$id,'parent_id'=>0))->find();
        $data['isz'] = 0;
        if($ra)
        {
            $data['isz'] = 1;
        }
        $issc = M('MemberCollection')
            ->where(array('member_id'=>$this->member['member_id'],'type'=>1,
                'to_id'=>$id,'is_delete'=>0))->find();
        $data['issc'] = 0;
        if($issc)
        {
            $data['issc'] = 1;
        }



        //广告

//        $data = M('Article')->where(array('id'=>$id))->find();

        if($data['ad'] > 0)  //如果有
        {
            $res = M('Adve')
                ->where(array('id'=>$data['ad']))
                ->field('id,links,pic,title')
                ->find();
            M('Adve')->where(array('id'=>$res['id']))->setInc('visit',1);
            $data['ad'] = $res;
        }else   //如果没有
        {
            $adve = $this->getAD($data['label']);
            if($adve)
            {
                $data['ad'] = $adve;
                M('Adve')->where(array('id'=>$data['id']))->setInc('visit',1);
                M('Article')->where(array('id'=>$id))->save(array('ad'=>$adve['id']));
            }

        }


        $data['label'] = M('Label')->where(array('id'=>$data['label']))->getField('name');
//var_dump($info['types']);

        $minfo = M('Member')->field('types,is_ok,status')->where(array('id'=>$mid))->find();
        $this->isdj = $minfo['status'];
//var_dump($minfo);
        if($minfo['types'] == 2 && $minfo['is_ok'] == 1 && $data['author'] != $this->member['member_id'])
        {
            $data['iszf'] = 1;
        }

        //如果已转发过
        $id = M('Article')->where(array('author'=>$this->member['member_id'],'pid'=>$id))->getField('id');
        if($id)
        {
            $data['iszf'] = 0;
        }







        $opt = getOptions('site_options');
        $this->zxinto = $opt['zxinto'];
        $this->data = $data;
        $this->display();
    }

    public function getAD($label)
    {
        $whereAD['zsqy'] = array('like','%3%');
//        $whereAD['lab'] = array('like',"%$label%");
        $whereAD['lab'] = $label;
        $whereAD['st'] = array('lt',time());
        $whereAD['ent'] = array('gt',time());
        $whereAD['is_delete'] = 0;
        $whereAD['status'] = 1;

        $res = M('Adve')
            ->where($whereAD)
            ->field('id,links,pic,title')
            ->order('rand()')
            ->find();
        return $res;
    }

    public function zfAction()
    {
        $id = intval(I('id'));
        if($id <= 0)
        {
            $this->error('参数错误');
        }

        $arti = M('Article')->field('title,content,label,thumb,tj')->where(array('id'=>$id))->find();
        $row['title'] = $arti['title'];
        $row['create_time'] = time();
        $row['content'] = $arti['content'];
        $row['label'] = $arti['label'];
        $row['is_delete'] = 0;
        $row['thumb'] = $arti['thumb'];
//        $row['tj'] = $arti['tj'];
        $row['authorname'] = M('Member')->where(array('id'=>$this->member['member_id']))->getField('nickname');
        $row['author'] = $this->member['member_id'];
        $row['isyc'] = 1;
        $row['pid'] = $id;

        $opt = getOptions('site_options');
        $integral = $opt['d_zf_art'];
        $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
        $irow['member_id'] = $this->member['member_id'];
        $irow['change'] = intval($integral);
        $irow['change_type'] = 6;
        $irow['change_status'] = 1;
        $irow['after'] = $integrals + intval($integral);
        $irow['create_time'] = time();
        M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',intval($integral));
        M('IntegralLog')->add($irow);




        $res = M('Article')->add($row);
        if($res)
        {
            $this->success('转发成功');
        }else
        {
            $this->error('转发失败');
        }
    }

    //删除文章
    public function deleteWz()
    {
        $id = intval(I('id'));
        if($id <= 0)
        {
            $this->error('参数错误');
        }
        $res = M('Article')->where(array('id'=>$id))->save(array('is_delete'=>1));
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->error('操作失败');
        }
    }
    //点赞三级
    public function giveActionSj()
    {

        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $id = I('id');
        $pid = I('pid');

        $ra = M('ArticleGive')
            ->where(array('member_id'=>$this->member['member_id'],
                'a_id'=>$id,'parent_id'=>$pid))->find();
        if(!$ra)
        {
            $row['member_id'] = $this->member['member_id'];
            $row['create_time'] = time();
            $row['a_id'] = $id;
            $row['parent_id'] = $pid;
            $res = M('ArticleGive')->add($row);
            if($res)
            {
                //推送消息
                $author = M('ArticleComment')->where(array('id'=>$pid))->getField('member_id');
                if($author != $this->member['member_id'])
                {
                    $rowMess['send_users_id'] = $this->member['member_id'];
                    $rowMess['receive_member_id'] = $author;
                    $rowMess['create_time'] = time();
                    $rowMess['type'] = 8;
                    $rowMess['oid'] = $pid;
                    $rowMess['types'] = 0;
                    $rowMess['arti_comm_id'] = $res;
                    M('Message')->add($rowMess);
                }

//                $ress = M('Article')->where(array('id'=>$id))->setInc('dzs',1);
                $reas = M('ArticleComment')->where(array('id'=>$pid))->setInc('dzs',1);

                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    $integral = $opt['travels_integral'];
                }else if($types == 2) //医生
                {
                    $integral = $opt['d_t_integral'];
                }

                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 2;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);

                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('点赞成功+'.$integral.'积分');
            }
        }else
        {

        }
    }
    //点赞二级
    public function giveActionEj()
    {

        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $id = I('id');
        $pid = I('pid');

        $ra = M('ArticleGive')
            ->where(array('member_id'=>$this->member['member_id'],
                'a_id'=>$id,'parent_id'=>$pid))->find();
        if(!$ra)
        {
            $row['member_id'] = $this->member['member_id'];
            $row['create_time'] = time();
            $row['a_id'] = $id;
            $row['parent_id'] = $pid;
            $res = M('ArticleGive')->add($row);

            if($res)
            {

                //推送消息
                $author = M('ArticleComment')->where(array('id'=>$pid))->getField('member_id');
                if($author != $this->member['member_id'])
                {
                $rowMess['send_users_id'] = $this->member['member_id'];
                $rowMess['receive_member_id'] = $author;
                $rowMess['create_time'] = time();
                $rowMess['type'] = 8;
                $rowMess['oid'] = $pid;
                $rowMess['types'] = 0;
                $rowMess['arti_comm_id'] = $res;
                M('Message')->add($rowMess);
                }
//                $ress = M('Article')->where(array('id'=>$id))->setInc('dzs',1);
                $reas = M('ArticleComment')->where(array('id'=>$pid))->setInc('dzs',1);
                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    $integral = $opt['travels_integral'];
                }else if($types == 2) //医生
                {
                    $integral = $opt['d_t_integral'];
                }

                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 2;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);

                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('点赞成功+'.$integral.'积分');
            }
        }else
        {

        }
    }

    public function giveAction()
    {
        $id = I('id');
        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $ra = M('ArticleGive')->where(array('member_id'=>$this->member['member_id'],'a_id'=>$id,'parent_id'=>0))->find();
        if(!$ra)
        {
            $row['member_id'] = $this->member['member_id'];
            $row['create_time'] = time();
            $row['a_id'] = $id;
            $row['parent_id'] = 0;
            $res = M('ArticleGive')->add($row);
            if($res)
            {


                //推送消息
                $author = M('Article')->where(array('id'=>$id))->getField('author');
                if($author != $this->member['member_id']) {
                    $rowMess['send_users_id'] = $this->member['member_id'];
                    $rowMess['receive_member_id'] = $author;
                    $rowMess['create_time'] = time();
                    $rowMess['type'] = 8;
                    $rowMess['oid'] = $id;
                    $rowMess['types'] = 1;
                    $rowMess['arti_comm_id'] = $res;
                    M('Message')->add($rowMess);
                }


                $ress = M('Article')->where(array('id'=>$id))->setInc('dzs',1);
                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    $integral = $opt['travels_integral'];
                }else if($types == 2) //医生
                {
                    $integral = $opt['d_t_integral'];
                }
                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 2;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);
                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('点赞成功+'.$integral.'积分');
            }
        }else
        {

        }
    }

    //收藏
    public function collAction()
    {

        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $id = I('id');
        $ra = M('MemberCollection')
            ->where(array('member_id'=>$this->member['member_id'],'type'=>1,
                'to_id'=>$id))->find();
        if(!$ra)
        {
            $row['member_id'] = $this->member['member_id'];
            $row['type'] = 1;
            $row['to_id'] = $id;
            $row['create_time'] = time();

            $res = M('MemberCollection')->add($row);
            if($res)
            {
                $this->success('已添加到收藏');
            }else
            {
                $this->error('收藏失败');
            }
        }else
        {
            $this->error('已经收藏');
        }


    }

    //关注
    public function folloAction()
    {

        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }


        $minfo = M('Member')->field('status')->where(array('id'=>$this->member['member_id']))->find();
        if($minfo['status'] == 0)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数错误');
        }

        if(I('types') == 'zjgz')  //直接关注医生
        {
            $doctId = $id;
        }else
        {
            $doctId = M('Article')->where(array('id'=>$id))->getField('author');
        }
        $ra = M('MemberFollo')
            ->where(array('member_id'=>$this->member['member_id'],'to_id'=>$doctId))->find();

        if(!$ra)
        {
            $row['member_id'] = $this->member['member_id'];
            $row['to_id'] = $doctId;
            $row['create_time'] = time();
            $res = M('MemberFollo')->add($row);
            M('Member')->where(array('id'=>$doctId))->setInc('fss',1);
            if($res)
            {
                $this->success('关注成功');
            }else
            {
                $this->error('关注失败');
            }
        }else
        {
            $ras = M('MemberFollo')
                ->where(array('member_id'=>$this->member['member_id'],
                    'to_id'=>$doctId))->delete();
            M('Member')->where(array('id'=>$doctId))->setDec('fss',1);
            if($ras)
            {
                $this->success('取消关注成功');
            }else
            {
                $this->error('取消关注失败');
            }
        }
    }


    public function commentComment()
    {
        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }


        if(I('mid'))
        {
            M('Message')->where(array('id'=>I('mid')))->save(array('isback'=>1));
        }


        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $data = I('');
        $row['member_id'] = $this->member['member_id'];
        $row['create_time'] = time();
        $row['content'] = $data['pltext'];
        $row['a_id'] = $data['aid'];
        $row['parent_id'] = $data['id'];
        $res = M('ArticleComment')->add($row);
        if($res)
        {
//            $ress = M('Article')->where(array('id'=>$data['aid']))->setInc('pls',1);
//            if($ress)
//            {


            $author = M('ArticleComment')->where(array('id'=>$data['id']))->getField('member_id');

            $rowMess['send_users_id'] = $this->member['member_id'];
            $rowMess['receive_member_id'] = $author;
            $rowMess['create_time'] = time();
            $rowMess['content'] = $data['pltext'];
            $rowMess['type'] = 9;
            $rowMess['oid'] = $data['id'];
            $rowMess['types'] = 0;
            $rowMess['arti_comm_id'] = $res;
            M('Message')->add($rowMess);





                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    // var_dump($opt['comment_integral']);  //c_t_order
                    $integral = $opt['c_t_order'];
                }else if($types == 2) //医生
                {
                    // var_dump($opt['d_c_integral']);  //d_ba_c
                    $integral = $opt['d_ba_c'];
                }
                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 5;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);

                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('评论成功+'.$integral.'积分');
            }
//        }
    }

    public function commentArti()
    {
        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $status = M('Member')->where(array('id'=>$this->member['member_id']))->getField('status');
        if($status != 1)
        {
            $this->error('已被冻结，不能执行此操作!');
        }

        $data = I('');
        $row['member_id'] = $this->member['member_id'];
        $row['create_time'] = time();
        $row['content'] = $data['pltext'];
        $row['a_id'] = $data['id'];
        $res = M('ArticleComment')->add($row);
        if($res)
        {

            $author = M('Article')->where(array('id'=>$data['id']))->getField('author');

            if($author > 0)
            {
            //推送消息
                $rowMess['send_users_id'] = $this->member['member_id'];
                $rowMess['receive_member_id'] = $author;
                $rowMess['create_time'] = time();
                $rowMess['content'] = $data['pltext'];
                $rowMess['type'] = 9;
                $rowMess['oid'] = $data['id'];
                $rowMess['types'] = 1;
                $rowMess['arti_comm_id'] = $res;
                M('Message')->add($rowMess);
            }



            $ress = M('Article')->where(array('id'=>$data['id']))->setInc('pls',1);
            if($ress)
            {
                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    // var_dump($opt['comment_integral']);  //c_t_order
                    $integral = $opt['comment_integral'];
                }else if($types == 2) //医生
                {
                    // var_dump($opt['d_c_integral']);  //d_ba_c
                    $integral = $opt['d_c_integral'];
                }

                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 1;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);

                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('评论成功+'.$integral.'积分');
            }
        }
    }

    public function getArtiComment()
    {
        $id = I('id');
        $p = I('p');
        $pagesize = 10;
        $start = ($p-1) * $pagesize;
        $arti = M('ArticleComment')->alias('AC')
            ->field('AC.id,AC.member_id,AC.content,M.avatar,M.nickname,AC.create_time')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
            ->order('AC.dzs desc,AC.id desc')
//            AC.dzs desc,
            ->limit($start,$pagesize)
            ->where(array('AC.a_id'=>$id,'AC.parent_id'=>0,'AC.is_delete'=>0))
            ->select();
        $str = '';

        $count = M('ArticleComment')->alias('AC')
        ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
        ->where(array('AC.a_id'=>$id,'AC.parent_id'=>0,'AC.is_delete'=>0))
        ->count();
        if($count > $pagesize)
        {
            $showMore = 1;
        }else
        {
            $showMore = 2;
        }


        if($arti)
        {
            foreach ($arti as $item) {

                $str .= '<div class="w-bgcolorFFF w-padding02 w-marginBottom01 w-flex">';
                $str .= '<img src="'.$item['avatar'].'" class="header1 w-marginRight02" alt="">';
                $str .= '<div class="w-flexitem1 w-overflowH">';
                $str .= '<h4 class="w-height04 w-flex">';
                $str .= '<span class="w-font14 w-color333 w-flexitem1 w-onlyline">'.$item['nickname'].'</span>';
                $str .= '<label  class="iconZan">';

                //当前用户是否点赞过
                $ra = M('ArticleGive')
                    ->where(array('member_id'=>$this->member['member_id'],
                        'a_id'=>$id,'parent_id'=>$item['id']))->find();
                if(!$ra)
                {
                    $str .= '<input  onclick="zanerji('.$item['id'].',this'.')" type="checkbox" >';
                }else
                {
                    $str .= '<input  checked disabled type="checkbox" id="zan2">';
                }
                //点赞数量
                $ras = M('ArticleGive')
                    ->where(array('a_id'=>$id,'parent_id'=>$item['id']))->count();
//                echo $ras;
                if($ras > 0)
                {
                    $str .= '<span data-id="'.$ras.'">'.$ras.'</span>';
                }else
                {
                    $str .= '<span data-id="0"></span>';
                }

                $str .= '</label>';
                $str .= '</h4>';

                $str .= '<a href="/Article/commentInfo/id/'.$item['id'].'/aid/'.$id.'">';
                $str .= '<div class="w-line04 w-font14 w-color333 w-threeLine">'.$item['content'].'</div></a>';

                $artiList = M('ArticleComment')->alias('AC')
                    ->field('AC.member_id,AC.content,M.avatar,M.nickname,AC.create_time')
                    ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
                    ->order('AC.id desc')
                    ->where(array('AC.a_id'=>$id,'AC.parent_id'=>$item['id'],'AC.is_delete'=>0))
                    ->select();

                $str .= '<h4 class="w-height04 w-font13 w-color999 w-marginBottom01">'.date('m-d',$item['create_time'])
                    .'&nbsp;'.date('H:i',$item['create_time']).'&nbsp;&nbsp;&nbsp;'.count($artiList).
                    '<a href="/Article/commentInfo/id/'.$item['id'].'/aid/'.$id.'"';
                $str .= 'class="xhLine1">回复</a></h4>';
                $str .= '<div class="w-bgcolorf7f7f7">';
                if($artiList)
                {
                    foreach ($artiList as $k=>$itema) {
                        if($k <= 2)
                        {
                            $str .= '<h4 class="w-height06 w-borderBfff w-font14 w-onlyline w-color999 w-paddingLeftRight02">';
                            $str .= '<em class="w-color000">'.$itema['nickname'].'：</em>'.$itema['content'].'';
                            $str .= '</h4>';
                        }
                    }
                }
                if(count($artiList) >= 3)
                {
                    $str .= '<h4 class="w-height06 w-borderBfff w-textalignC w-paddingLeftRight02">';
                    $str .= '<a href="/Article/commentInfo/id/'.$item['id'].'/aid/'.$id.'" class="xhLine1">查看全部评论</a>';
                    $str .= '</h4>';
                }

                $str .= '</div>';
                $str .= '</div>';
                $str .= '</div>';

            }
        }
        if($str)
        {
            $this->success(array('info'=>$str,'showMore'=>$showMore));
        }else
        {
            $this->error('nodata');
        }
//        echo $str;
    }

    public function commentInfo()
    {
        $id = I('id');
        $arti = M('ArticleComment')->alias('AC')
            ->field('AC.id,AC.member_id,AC.content,M.avatar,M.nickname,AC.create_time')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
            ->order('AC.id asc')
            ->where(array('AC.id'=>$id,'AC.parent_id'=>0,'AC.is_delete'=>0))
            ->find();
        $arti['day'] = date('m-d',$arti['create_time']);
        $arti['times'] = date('H:i',$arti['create_time']);

        $arti['counts'] = M('ArticleComment')->alias('AC')
            ->field('AC.member_id,AC.content,M.avatar,M.nickname,AC.create_time')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
            ->order('AC.id desc')
            ->where(array('AC.parent_id'=>$id,'AC.is_delete'=>0))
            ->count();


        //当前用户是否点赞过
        $ra = M('ArticleGive')
            ->where(array('member_id'=>$this->member['member_id'],
                'a_id'=>I('aid'),'parent_id'=>$id))->find();
        $this->comments = M('ArticleGive')->where(array('a_id'=>I('aid'),'parent_id'=>$id))->count();

        if(!$ra)
        {
            $this->isz = 0;
        }else
        {
            $this->isz = 1;
        }

        $this->data = $arti;
        $this->display();
    }





    public function commentMore()
    {
        $id = I('id');
        $aid = I('aid');
        $pagesize = 10;
        $start = (I('p') - 1) * $pagesize;
        $arti = M('ArticleComment')->alias('AC')
            ->field('AC.dzs,AC.a_id,AC.p_p_id,AC.member_id,AC.id,AC.content,M.avatar,M.nickname,AC.create_time')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
//            ->order('Ac.dzs desc,AC.id desc')
            ->order('AC.dzs desc , AC.id desc')
            ->limit($start,$pagesize)
            ->where(array('AC.parent_id'=>$id,'AC.a_id'=>$aid,'AC.is_delete'=>0))
            ->select();
        $str = '';
        if($arti)
        {
            foreach ($arti as $item) {
                $str .= '<div class="w-paddingTopBottom02 w-flex w-borderBeee">';
                $str .= '<img src="'.$item['avatar'].'" class="header1 w-marginRight02" alt="">';
                $str .= '<div class="w-flexitem1 w-overflowH">';
                $str .= '    <h4 class="w-height04 w-flex">';
                $str .= '        <span class="w-font14 w-color333 w-flexitem1 w-onlyline">';
                $str .= '            <em class="w-verticalMiddle">'.$item['nickname'].'</em>';

                $artiId = M('Article')->where(array('id'=>$aid))->getField('author');
                $lzId = M('ArticleComment')->where(array('id'=>$id))->getField('member_id');
                if($item['member_id'] == $artiId)
                {
                    $isyc = M('Article')->where(array('id'=>$item['a_id']))->getField('isyc');
                    if($isyc == 0)
                    {
                        $str .= '<em class="w-btn1 w-marginLeft01">作者</em>';
                    }else
                    {
//                        $str .= '<em class="w-btn1 w-marginLeft01"></em>';
                    }
                }else if($item['member_id'] == $lzId)
                {
                    $str .= '<em class="w-btn1 w-marginLeft01">楼主</em>';
                }


                $str .= '    </span>';
                $str .= '    <label class="iconZan">';


                //当前用户是否点赞过
                $ra = M('ArticleGive')
                    ->where(array('member_id'=>$this->member['member_id'],
                        'a_id'=>$aid,'parent_id'=>$item['id']))->find();
                if(!$ra)
                {
                    $str .= '<input onclick="zanersanji('.$item['id'].',this'.')" type="checkbox"/>';
                }else
                {
                    $str .= '<input type="checkbox" disabled checked id="zan2"/>';
                }

//                $ras = M('ArticleGive')->where(array('a_id'=>$aid,'parent_id'=>$item['id']))->count();
                $ras = M('ArticleGive')->where(array('a_id'=>$aid,'parent_id'=>$item['id']))->count();

                if($ra)
                {
                    $str .= '<span>'.$ras.'</span>';
                }else
                {
                    $str .= '<span data-id="'.$ras.'"></span>';
                }


                $str .= '</label>';
                $str .= '</h4>';


                if($item['p_p_id'])
                {
                    $stra = '';
                    $artis = array();
                    $artis = M('ArticleComment')->alias('AC')
                        ->field('AC.content,M.nickname')
                        ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
                        ->where(array('AC.id'=>$item['p_p_id'],'AC.is_delete'=>0))
                        ->find();
                    if($artis)
                    {
                        $stra = '<span class="tomember">//@'.$artis['nickname'].':</span>'.$artis['content'];
                    }
                    $str .= '    <div class="w-line04 w-font14 w-color333">'.$item['content'].$stra.'</div>';
                }else
                {
                    $str .= '    <div class="w-line04 w-font14 w-color333">'.$item['content'].'</div>';
                }




                $times = formatTime($item['create_time']);

                if($this->member['member_id'])
                {
                    $str .= '    <h4 class="w-height04 w-font13 w-color999">'.$times.'&nbsp;&nbsp;&nbsp;
                <a href="/Article/commentCom/id/'.$item['id'].'" class="xhLine1">回复</a></h4>';
                }else
                {
                    $str .= '    <h4 class="w-height04 w-font13 w-color999">'.$times.'&nbsp;&nbsp;&nbsp;
                <a href="/Index/login" class="xhLine1">回复</a></h4>';
                }



                $str .= '</div>';
                $str .= '</div>';
            }
        }
        echo $str;
    }


    // 回复评论页面
    public function commentCom()
    {
        if(!$this->member['member_id'])
        {
            $this->error('请先登录');
        }

        $id = I('id');
        $arti = M('ArticleComment')->alias('AC')
            ->field('AC.id,AC.member_id,AC.content,M.avatar,M.nickname,AC.create_time')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
            ->order('AC.id asc')
            ->where(array('AC.id'=>$id,'AC.is_delete'=>0))
            ->find();
        $this->data = $arti;
        $this->display();
    }

    public function addCommentToComm()
    {

        if(I('mid'))
        {
            M('Message')->where(array('id'=>I('mid')))->save(array('isback'=>1));
        }

        $content = I('content');
        $id = I('id');
        $aid = M('ArticleComment')->field('a_id,parent_id')->where(array('id'=>$id))->find();
        $row['member_id'] = $this->member['member_id'];
        $row['create_time'] = time();
        $row['content'] = $content;
        $row['a_id'] = $aid['a_id'];

        $row['parent_id'] = $aid['parent_id'];
        $row['p_p_id'] = $id;
        $row['lev'] = 3;
        $res = M('ArticleComment')->add($row);
        if($res)
        {


            //推送消息
            $author = M('ArticleComment')->where(array('id'=>$id))->getField('member_id');
            $rowMess['send_users_id'] = $this->member['member_id'];
            $rowMess['receive_member_id'] = $author;
            $rowMess['create_time'] = time();
            $rowMess['content'] = $content;
            $rowMess['type'] = 9;
            $rowMess['oid'] = $id;
            $rowMess['types'] = 0;
            $rowMess['arti_comm_id'] = $res;
            M('Message')->add($rowMess);

//            $ress = M('Article')->where(array('id'=>$aid['a_id']))->setInc('pls',1);
//            if($ress)
//            {
                $types = M('Member')->where(array('id'=>$this->member['member_id']))->getField('types');
                $opt = getOptions('site_options');
                if($types == 1)  //用户
                {
                    // var_dump($opt['comment_integral']);  //c_t_order
                    $integral = $opt['c_t_order'];
                }else if($types == 2) //医生
                {
                    // var_dump($opt['d_c_integral']);  //d_ba_c
                    $integral = $opt['d_ba_c'];
                }

                $integrals = M('Member')->where(array('id'=>$this->member['member_id']))->getField('integral');
                $irow['member_id'] = $this->member['member_id'];
                $irow['change'] = $integral;
                $irow['change_type'] = 5;
                $irow['change_status'] = 1;
                $irow['after'] = $integrals + $integral;
                $irow['create_time'] = time();
                M('IntegralLog')->add($irow);
                M('Member')->where(array('id'=>$this->member['member_id']))->setInc('integral',$integral);
                $this->success('评论成功+'.$integral.'积分');
            }
//        }

    }
    public function thumimg(){
        $imgpath=I('imgpath');

        if($imgpath){
            //$this->success($imgpath);
            if(file_exists('./'.$imgpath)){
                $patharr=explode('.',$imgpath);
                $newpath=$patharr[0].'thumb.png';
                if(!file_exists($newpath)){
                    $image = new \Think\Image();
                    $image->open('./'.$imgpath);
                    $image->thumb(100, 100)->save('./'.$newpath);
                }
                $this->success($newpath);
            }
            else{
                $this->success($imgpath);
            }
        }
        else{
            $this->success($imgpath);
        }
    }
}