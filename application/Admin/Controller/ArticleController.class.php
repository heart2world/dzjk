<?php
/**
 * 文章管理
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\ArticleModel;

class ArticleController extends AdminbaseController
{

    protected $article_model;

    function _initialize()
    {
        parent::_initialize();
        $this->article_model = new ArticleModel();
    }

    public function tjAction()
    {
        $id = I('id');
        if($id)
        {
            $res = M('Article')->where(array('id'=>array('in',$id)))->save(array('tj'=>1));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->success('操作已成功');
//            $this->error('操作失败');
        }
    }
    public function qxtjAction1()
    {
        $id = I('id');
        if($id)
        {
            $res = M('Article')->where(array('id'=>array('in',$id),'tj'=>array('eq',1)))->save(array('tj'=>0));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }
    public function qxtjAction()
    {
        $id = I('id');
        if($id)
        {
            $res = M('Article')->where(array('id'=>array('in',$id),'tj'=>array('eq',1)))->save(array('tj'=>0));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }
    public function delbtn()
    {
        $id = I('id');
        if($id)
        {
            $res = M('Article')->where(array('id'=>array('in',$id)))->save(array('is_delete'=>1));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }

    //内容详情
    public function detailDt(){
        $id = I('id',0,'intval');
        $info = M('Article')->where(array('id'=>$id))->find();
        if($info)
        {
            $info['content'] = html_entity_decode($info['content']);
//            $info['authorname'] = $info['authorname'];
            if($info['author'] == 0)
            {
                $info['authorname'] = '平台';
            }else
            {
                $info['authorname'] = M('Member')->where(array('id'=>$info['author']))->getField('nickname');
            }
            $info['label'] = M('Label')->where(array('id'=>$info['label'],'status'=>1))->getField('name');
            $info['tj'] = $info['tj'] == 1 ? '是': '否';
            $info['isyc'] = $info['isyc'] == 1 ? '转发': '原创';
            $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);
            $info['piclist'] = explode(',',$info['thumb']);
        }

        $datas = $this->getCommentList($id,1);
        $info['comment'] = $datas;
//var_dump($info['comment']);
        $this->assign('info',$info);
        $this->display();
    }


    /**
     * @param $id  文章id
     * @param $p  页数
     * @return mixed
     */
    public function getCommentList($id,$p)
    {
        $id = $id;
        $p = $p;
        $pagesize = 1000;
        $start = ($p-1) * $pagesize;
        $arti = M('ArticleComment')->alias('AC')
            ->field('AC.id,AC.member_id,AC.content,M.nickname,AC.dzs')
            ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
            ->order('AC.dzs desc,AC.id desc')
            ->limit($start,$pagesize)
            ->where(array('AC.a_id'=>$id,'AC.parent_id'=>0,'AC.is_delete'=>0))
            ->select();
        if($arti)
        {
            foreach ($arti as &$item)
            {
                $artiList = M('ArticleComment')->alias('AC')
                    ->field('AC.id,AC.lev,AC.parent_id,AC.a_id,AC.p_p_id,AC.member_id,AC.dzs,AC.content,M.avatar,M.nickname,AC.create_time')
                    ->join('LEFT JOIN __MEMBER__ M on AC.member_id = M.id')
                    ->order('AC.id desc')
                    ->where(array('AC.a_id'=>$id,'AC.parent_id'=>$item['id'],'AC.is_delete'=>0))
                    ->select();
                if($artiList)
                {

                    foreach ($artiList as &$iv) {
                        if($iv['lev'] == 3)   //如果是回复的评论
                        {
                            $member_id = M('ArticleComment')->where(array('id'=>$iv['p_p_id']))->getField('member_id');

                            $iv['nickname'] = $iv['nickname'].'回复'.M('Member')->where(array('id'=>$member_id))->getField('nickname');
                        }
                        $author =  M('Article')->where(array('id'=>$iv['a_id']))->getField('author');
                        if($iv['member_id'] == $author)  //如果是作者
                        {
                            $iv['nickname'] = '作者'.M('Member')->where(array('id'=>$author))->getField('nickname');
                            $iv['iszz'] = 1;
                        }
                    }
                    $item['lists'] = $artiList;
                }
            }
        }
        return $arti;
    }


    /**
     * 删除当前评论及以下所有。
     */
    public function delAllComm()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数错误');
        }

        $res = M('ArticleComment')->where(array('parent_id'=>$id))->save(array('is_delete'=>1));
        $resA = M('ArticleComment')->where(array('id'=>$id))->save(array('is_delete'=>1));

        $aid = M('ArticleComment')->where(array('id'=>$id))->getField('a_id');
        M('Article')->where(array('id'=>$aid))->setDec('pls',1);

        $this->success('操作已成功');
    }
    /**
     * 删除当前评论。
     */
    public function delMyComm()
    {
        $id = intval(I('id'));
        if($id < 0)
        {
            $this->error('参数错误');
        }

        $aid = M('ArticleComment')->field('a_id,parent_id')->where(array('id'=>$id))->find();
        if($aid['parent_id'] == 0)
        {
            M('Article')->where(array('id'=>$aid['a_id']))->setDec('pls',1);
        }
        $resA = M('ArticleComment')->where(array('id'=>$id))->save(array('is_delete'=>1));
        $this->success('操作已成功');
    }






    //内容详情
    public function detail(){
        $id = I('id',0,'intval');
        $info = M('Article')->where(array('id'=>$id))->find();
        if($info)
        {
            $info['content'] = html_entity_decode($info['content']);
//            $info['authorname'] = $info['authorname'];
            if($info['author'] == 0)
            {
                $info['authorname'] = '平台';
            }
            $info['label'] = M('Label')->where(array('id'=>$info['label'],'status'=>1))->getField('name');
            $info['tj'] = $info['tj'] == 1 ? '是': '否';
            $info['isyc'] = $info['isyc'] == 1 ? '转发': '原创';
            $info['create_time'] = date('Y-m-d H:i:s',$info['create_time']);
            $info['piclist'] = explode(',',$info['thumb']);
        }

        if($info['ad'])
        {
            $ads = M('Adve')->where(array('id'=>$info['ad']))->find();
            if($ads)
            {
                $info['adpic'] = $ads['pic'];
                $info['adname'] = $ads['title'];
                $info['adlinks'] = $ads['links'];
            }

        }


        $datas = $this->getCommentList($id,1);
        $info['comment'] = $datas;

        $info['authorname'] = M('Member')->where(array('id'=>$info['author']))->getField('nickname');
        $this->assign('info',$info);
        $this->display();
    }

    public function add()
    {
        if(IS_POST)
        {
            $data = I('post.');
            $row['thumb'] = implode(',',$data['goods']['photos']);
            if(strlen($row['thumb']) < 10)
            {
                $this->error('必须上传一张封面图');
            }

            $row['title'] = $data['title'];
            $row['create_time'] = time();
            $row['content'] = $data['contents'];
            $row['label'] = $data['label'];
            $row['is_delete'] = 0;
            $row['author'] = $data['zzp'];


            $row['tj'] = $data['tj'];
            $id = M('Article')->add($row);


//            d_pl_art



            if($id)
            {
                if($data['zzp'] > 0)
                {
                    $opt = getOptions('site_options');
                    $integral = $opt['d_pl_art'];
                    $integrals = M('Member')->where(array('id'=>$data['zzp']))->getField('integral');
                    $irow['member_id'] = $data['zzp'];
                    $irow['change'] = intval($integral);
                    $irow['change_type'] = 7;
                    $irow['change_status'] = 1;
                    $irow['after'] = $integrals + intval($integral);
                    $irow['create_time'] = time();
                    M('Member')->where(array('id'=>$data['zzp']))->setInc('integral',intval($integral));
                    M('IntegralLog')->add($irow);
                }



                $this->success('操作成功');
            }else
            {
                $this->error('no');
            }

        }else
        {
            $lablist = M('label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();

            $yslist  = M('Member')->field('id,nickname')->where(array('status'=>1,'is_delete'=>0,'types'=>2,'is_ok'=>1))->select();

            $this->yslist = $yslist;

            if($lablist)
            {
                $this->lab = $lablist;
            }else
            {
                $this->lab = array();
            }
            $this->display();
        }
    }
    public function index()
    {
        if(IS_AJAX){

            $search['keywords'] = I('keywords');
            $p = I('p',1,'intval');
            $search['st_time'] = I('st_time',0,'strtotime');
            $search['end_time'] = I('end_time',0,'strtotime');
            if(I('tj') == 1 && I('tj') != 'a')
            {
                $search['tj'] = 1;
            }else if(I('tj') == 0 && I('tj') != 'a')
            {
                $search['tj'] = array('neq',1);
            }


            $search['isyc'] = I('isyc');
            $search['label'] = I('label');

            $data = $this->article_model->get_list($search,'',$p,$this->pageNum);
            $this->success($data);
        }else{
            $lablist = M('label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();
            if($lablist)
            {
                $this->lab = $lablist;
            }else
            {
                $this->lab = array();
            }
            $this->display();
        }
    }

    //新增文章
    public function create(){
        if(IS_AJAX){
            if($data=$this->article_model->create()){
                $title=$this->article_model->title;
                if($this->article_model->add()){
                    write_log('添加文章:'.$title,'帮助中心管理');
                    $this->success('添加成功');
                }else{
                    $this->error($this->article_model->getError());
                }
            }else{
                $this->error($this->article_model->getError());
            }
        }else{
            $this->display();
        }
    }


    //删除
    public function delete(){
        if (IS_AJAX) {
            $ids=I('id');
            $res=$this->article_model->deleteBatch($ids);
            if ($res===false) {
                $this->error($this->article_model->getError());
            }else{
                write_log('删除文章:'.$res,'帮助中心管理');
                $this->success("删除成功");
            }
        }else{
            $this->error("参数错误");
        }
    }




    //编辑内容
    public function edit_content(){
        if(IS_POST){
            $data = I('post.');
            $row['thumb'] = implode(',',$data['goods']['photos']);
            if(strlen($row['thumb']) < 10)
            {
                $this->error('必须上传一张封面图');
            }
            $row['title'] = $data['title'];
            $row['content'] = $data['contents'];
            $row['label'] = $data['label'];
            $row['is_delete'] = 0;
            $row['author'] = $data['zzp'];
            $row['tj'] = $data['tj'];
            $id = M('Article')->where(array('id'=>$data['id']))->save($row);
            write_log('编辑文章:'.$row['title'],'内容管理');
            $this->success('编辑成功');
            /*if($data=$this->article_model->create($row)){
                $title=$this->article_model->title;
                if($this->article_model->save($data)!==false){
                    write_log('编辑文章:'.$title,'内容管理');
                    $this->success('编辑成功');
                }else{
                    $this->error($this->article_model->getError());
                }
            }else{
                $this->error($this->article_model->getError());
            }*/
        }else{
            $id=I('id',0,'intval');
            $info=$this->article_model->get_info($id);
            $athor=M('Member')->where(array('id'=>$info['author']))->find();
            $label=M('label')->where(array('id'=>$info['label']))->find();
            $this->assign('author',$athor);
            $this->assign('label',$label);
            $this->assign('info',$info);
            $this->assign('id',$id);
            if($info['thumb']){
                $picarr=explode(',',$info['thumb']);
            }
            else{
                $picarr=array();
            }
            $this->assign('picarr',$picarr);
            $lablist = M('label')->field('id,name')->where(array('is_del'=>0,'status'=>1))->select();

            $yslist  = M('Member')->field('id,nickname')->where(array('status'=>1,'is_delete'=>0,'types'=>2,'is_ok'=>1))->select();

            $this->yslist = $yslist;

            if($lablist)
            {
                $this->lab = $lablist;
            }else
            {
                $this->lab = array();
            }
            $this->display('edit');
        }
    }


}