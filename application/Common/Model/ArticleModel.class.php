<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 *      会员类
 * Date: 2016/6/5 0005
 * Time: 上午 10:19
 */

namespace Common\Model;

use Common\Model\CommonModel;

class ArticleModel extends CommonModel
{

    protected $_validate = array(
        array('title','require','标题必须填写！',1),
        array('title','1,30','标题长度1-30字',1,'length'),
        array('title','check_unigue','标题已经存在！',0,'callback'),
        array('thumb','check_thumb','请上传图片！',0,'callback'),
    );

    protected $_auto = array (
        array('create_time','time',1,'function'),
        array('update_time','time',2,'function'),
    );


    //检查名称唯一
    public function check_unigue($title){
        $id=I('id',0,'intval');
        $where=array();
        $where['is_delete']=array('eq',0);
        $where['title']=$title;
        if($id){
            $where['id']=array('neq',$id);
        }
        $total=$this->where($where)->count();
        if($total){
            return false;
        }else{
            return true;
        }
    }



    public  function get_list_data($search=array(),$page=2,$pageSize=5)
    {
        if(empty($order))
        {
            $order = 'id desc';
        }
        $count = $this->alias('AC')
            ->join('LEFT JOIN __MEMBER__ M on AC.author = M.id')
            ->where($search)->count();
        if($pageSize == 0 && $count > 10000)
        {
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field='*';
        $start = ($page - 1) * $pageSize;
        $list = $this->alias('AC')
            ->field('AC.*,M.nickname,M.avatar,M.zy')
            ->join('LEFT JOIN __MEMBER__ M on AC.author = M.id')
            ->where($search)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        $str = '';
        if($list)
        {
            foreach($list as $key=>&$vo)
            {
                $lab = M('Label')->where(array('id'=>$vo['zy']))->getField('name');
                $vo['create_time'] = formatTime($vo['create_time']);
                $thumbs = explode(',',$vo['thumb']);

                if($vo['type'] == 1)  //动态
                {
                      $str .='<div  class="passage_item p-t-15 m-b-10 p_both15 back-fff">';
                      $str .='<div class="up_div flex_dom">';
                      $str .='<img src="'.$vo['avatar'].'"/>';
                      $str .='<div class="name flex_1">';
                      $str .='<h4>'.$vo['nickname'].'</h4>';
                      $str .='<p class="color-999">'.$vo['create_time'].'</p>';
                      $str .='</div>';
                      $str .='<div class="dele">';
                      $str .='<button  onclick="deleteWz('.$vo['id'].')"><img src="/themes/Public/Mobile/image/slice/dele2.png"/></button>';
                      $str .='</div>';
                      $str .='</div>';
                      $str .='<span class="down_div flex_dom m-t-10">';
                      $str .='<div class="left flex_1  flex_column flex_item_between">';


                    $str .='<div class="threeLine w-marginBottom01" >';
                    $str .='<a href="/Article/infoDyna/id/'.$vo['id'].'" >'.$vo['content'].'</a>';
                    $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
                    $str .='</div>';

//                      $str .='<p class="text-ellipsis-2line">'.$vo['content'].'</p>';



                      $str .='<div  style="position: relative" class="img_box swiper_clickLook flex_dom ">';




//                    if(count($thumbs) <= 1)
//                    {
//                        if($thumbs[0])
//                        {
//                            $str .='<div  class="w-imgBox"><img src="'.$thumbs[0].'" alt=""/></div>';
//                        }
//                    }else if(count($thumbs) >= 2)
//                    {
//                        $str .='<div class="w-font0 threeImgBox w-position w-marginBottom02">';
//                      foreach ($thumbs as $k=>$thumb)
//                        {
//
//                            if($k <= 2)
//                            {
//                                $str .='<div class="threeImg" style="mzx-width: "><img src="'.$thumb.'" alt=""/></div>';
//                            }
//                        }
//                        if (count($thumbs) > 3)
//                        {
//                            $cos = count($thumbs)-3;
//                            $str .='<div class="threeFixed"><span>+'.$cos.'</span></div>';
//                        }
//                        $str .='</div>';
//                    }





                if(count($thumbs) == 1)
                {
                    if($thumbs[0])
                    {
                        $str .=' <div class="oneImgBox">';
                        $str .=' <img onclick="jumpurls('.$vo['id'].')" src="'.$thumbs[0].'">';
                        $str .=' </div>';
                    }

                }else if(count($thumbs) > 1)
                {
                    foreach ($thumbs as $ks=>$thumb)
                    {
                        if($ks <= 2)
                        {
                            if($thumb)
                            {
                                $str .='<div onclick="jumpurls('.$vo['id'].')" class="img"><img src="'.$thumb.'"/></div>';
                            }
                        }
                    }

                    if (count($thumbs) > 3)
                    {
                        $cos = count($thumbs)-3;
                        $str .='<div onclick="jumpurls('.$vo['id'].')" class="threeFixed" style="top: 7%;"><span>+'.$cos.'</span></div>';
                    }
                }





                      $str .='</div>';
                      $str .='<div class="pingjia_about color-999">';
//                      $str .='<span>'.$lab.'</span><em class="m-l-10">'.$vo['nickname'].'</em><em class="m-l-10">112</em>';
                      $str .='</div>';
                      $str .='</div>';
                      $str .='</span>';
                      $str .='<div class="dothing text-right ">';
                      $str .='<label><input type="checkbox"  /><span>';
                      $str .='<img src="/themes/Public/Mobile/image/slice/dianzan1.png">';
                      $str .='<img src="/themes/Public/Mobile/image/slice/dianzan2.png">';
                      $str .='</span><b>'.$vo['dzs'].'</b></label>';
                      $str .='<span><img src="/themes/Public/Mobile/image/slice/huifu1.png"/><em>'.$vo['pls'].'</em></span>';
                      $str .='<em><a data-title="'.$vo['content'].'"  data-id="'.$vo['id'].'"  onclick="shareFunc(this)" href="javascript:;" ><img src="/themes/Public/Mobile/image/slice/fenxiang1.png"/></a><i>'.$vo['fxs'].'</i></em>';
                      $str .='</div>';
                      $str .='</div>';

                }else   if($vo['type'] == 0)  //文章
                {

                    if($vo['title'] && count($thumbs)<=1)
                    {
                        $str .='<div  class="passage_item p-v-15 back-fff p_both15 m-b-10">';
                        $str .='<div class="up_div flex_dom">';
                        $str .='<img src="'.$vo['avatar'].'"/>';
                        $str .='<div class="name flex_1">';
                        $str .='<h4>'.$vo['nickname'].'</h4>';
                        $str .='<p class="color-999">'.$vo['create_time'].'</p>';
                        $str .='</div>';
                        $str .='<div class="dele">';
                        $str .='<button onclick="deleteWz('.$vo['id'].')"><img src="/themes/Public/Mobile/image/slice/dele2.png"/></button>';
                        $str .='</div>';
                        $str .='</div>';
                        $str .='<a  href="/Article/info/id/'.$vo['id'].'"  class="down_div flex_dom m-t-10">';
                        $str .='<div class="left flex_1 m-r-15 flex_column flex_item_between">';
                        $str .='<p class="text-ellipsis-2line">'.$vo['title'].'</p>';
                        $str .='<div class="pingjia_about color-999">';
                        $str .='<span>'.$lab.'</span><em class="m-l-10">'.$vo['nickname'].'</em><em class="m-l-10">';
                        if($vo['pls']> 0)
                        {
                            $str .= $vo['pls'].'评论';
                        }
                        $str .='</em>';
                        $str .='</div>';
                        $str .='</div>';
                        $str .='<img src="'.$vo['thumb'].'" />';
                        $str .='</a>';
                        $str .='</div>';
                        
                        
                    }else if($vo['title'] && count($thumbs) > 1)
                    {
                        $str .='<div  class="passage_item p-v-15 back-fff p_both15 m-b-10">';
                        $str .='<div class="up_div flex_dom">';
                        $str .='<img src="'.$vo['avatar'].'"/>';
                        $str .='<div class="name flex_1">';
                        $str .='<h4>'.$vo['nickname'].'</h4>';
                        $str .='<p class="color-999">'.$vo['create_time'].'</p>';

                        $str .='</div>';
                        $str .='<div class="dele">';
                        $str .='<button onclick="deleteWz('.$vo['id'].')"><img src="/themes/Public/Mobile/image/slice/dele2.png"/></button>';
                        $str .='</div>';
                        $str .='</div>';
                        $str .='<a href="/Article/info/id/'.$vo['id'].'" class="down_div flex_dom m-t-10">';
                        $str .='<div class="left flex_1  flex_column flex_item_between">';
                        $str .='<p class="text-ellipsis-2line">'.$vo['title'].'</p>';
                        $str .='<div class="img_box swiper_clickLook flex_dom flex_item_between">';
                        foreach ($thumbs as $k=>$thumb)
                        {
                            if($k <= 2)
                            {
                                $str .='<div class="img"><img src="'.$thumb.'"/></div>';
                            }
                        }
                        $str .='</div>';
                        $str .='<div class="pingjia_about color-999">';
                        $str .='<span>'.$lab.'</span><em class="m-l-10">'.$vo['nickname'].'</em><em class="m-l-10">';
                        if($vo['pls']> 0)
                        {
                            $str .= $vo['pls'].'评论';
                        }
                        $str .='</em>';
                        $str .='</div>';
                        $str .='</div>';
                        $str .='</a>';
                        $str .='</div>';
                    }
                }
            }
        }
        return $str;
    }
    public  function get_list_phone($search=array(),$mid,$page=2,$pageSize=10)
    {
        if(empty($order))
        {
            $order = 'id desc';
        }
        $count = $this->where($search)->count();
        if($pageSize == 0 && $count > 10000)
        {
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field='*';
        $start = ($page - 1) * $pageSize;
        $list = $this->field($field)
            ->where($search)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        $str = '';
        if($list)
        {

            foreach($list as $key=>&$vo)
            {
                if($vo['type'] == 1)  //动态
                {
                    $lab = M('Label')->where(array('id'=>$vo['label']))->getField('name');
//                    $author = M('Member')->field('nickname,avatar')->where(array('id'=>$vo['author']))->find();
//                    $MemberIntro = M('MemberIntro')->field('zw,hosp')->where(array('pid'=>$vo['author']))->find();


                    $thumbs = explode(',',$vo['thumb']);
                    $info = M('Member')->alias('M')
                        ->field("M.nickname,M.avatar,MI.zw,MI.hosp")
                        ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
                        ->where(array('M.id'=>$vo['author'],'M.types'=>2,'M.is_delete'=>0))
                        ->find();
                    $str .='<div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">';
                    $str .='<div class="w-paddingTopBottom02 w-borderBeee">';
                    $str .='<div class="w-flex w-marginBottom01">';
                    $str .='<img src="'.$info['avatar'].'" class="header1 w-marginRight02" alt=""/>';
                    $str .='<div class="w-flexitem1">';
                    $str .='<h4 class="w-height03 w-font14 w-color000 w-onlyline">'.$info['nickname'].'</h4>';
                    $str .='<span class="w-block w-height03 w-font12 w-color999 w-onlyline">'.$info['hosp'].$info['zw'].'</span>';
                    $str .='</div>';
                    $str .='</div> ';
                    $str .='<div class="threeLine w-marginBottom01">';
                    $str .= '<a class="w-block" href="/Article/infoDyna/id/'.$vo['id'].'">'.$vo['content'].'</a>';
                    $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
                    $str .='</div>';
//                    $str .='';
                    if(count($thumbs) <= 1)
                    {
                        if($thumbs[0])
                        {
                            $str .='<a class="w-block" href="/Article/infoDyna/id/'.$vo['id'].'"><div  class="oneImgBox"><img src="'.$thumbs[0].'" alt=""/></div></a>';
                        }
                    }elseif (count($thumbs) >= 2)
                    {

                        $str .='<a class="w-block" href="/Article/infoDyna/id/'.$vo['id'].'"><div class="w-font0 threeImgBox w-position w-marginBottom02">';
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
                        $str .='</div></a>';
                    }

                    $str .='</div>';
                    $str .='<div class="w-paddingTopBottom02 w-flex">';
                    $str .='<div class="w-flexitem1 w-textalignR">';


                    $str .='<label   class="iconZan w-marginRight02">';

                    $ra = array();
                    $ra = M('ArticleGive')->field('id')
                        ->where(array('member_id'=>$mid,
                            'a_id'=>$vo['id'],'parent_id'=>0))->find();
                    if(!$ra['id'])  //如果没点赞，可以点，
                    {
                        $str .= '<input onclick="giveAction('.$vo['id'].',this'.')" type="checkbox" />';
                    }else  //已经点赞，不能点，
                    {
                        $str .= '<input  disabled checked type="checkbox" >';
                    }
                    $str .='<span>'.$vo['dzs'].'</span>';
                    $str .='</label>';
                    $str .='<a  class="iconTalk w-marginRight02">'.$vo['pls'].'</a>';
                    $str .='<a data-title="'.strip_tags(html_entity_decode($vo['content'])).'"  data-id="'.$vo['id'].'"  onclick="shareFunc(this)" href="javascript:;" class="iconShare">'.$vo['fxs'].'</a>';
                    $str .='</div>';
                    $str .='</div>';
                    $str .='</div>';

                }else   if($vo['type'] == 0)  //文章
                {

                   $lab = M('Label')->where(array('id'=>$vo['label']))->getField('name');
                   $author = M('Member')->where(array('id'=>$vo['author']))->getField('nickname');
                   $thumbs = explode(',',$vo['thumb']);
                    if($vo['title'] && count($thumbs)<=1)
                    {

                        $str .='<a class="w-block" href="/Article/info/id/'.$vo['id'].'"><div class="w-bgcolorFFF w-flex w-padding0302 w-marginBottom01">';
                        $str .='<div class="w-flexitem1">';

                        if($vo['isyc'] == 1)
                        {

//                            $str .='<div class="w-line04 w-font14 w-color333 w-marginBottom03 w-doubleline"><span class="w-btn1 w-marginRight02">转</span>'.$vo['title'].'</div>';
                            $str .='<div class="w-height08 w-line04 w-font14 w-color333 w-marginBottom03 w-doubleline"><span class="iszbox">[转]</span>'.$vo['title'].'</div>';
                        }else
                        {
                            $str .='<div class="w-height08 w-line04 w-font14 w-color333 w-marginBottom03 w-doubleline">'.$vo['title'].'</div>';
                        }



                        $str .='<div class="w-height03 w-onlyline w-font12 w-color999">';
                        $str .='<span class="w-btn1 w-marginRight02">'.$lab.'</span>';
                        $str .='<em class="w-marginRight02">'.$author.'</em>';
                        $str .='<em>'.$vo['pls'].'评论</em>';
                        $str .='</div>';
                        $str .='</div>';
                        $str .='<img src="'.$vo['thumb'].'" alt="" class="goods1">';
                        $str .='</div></a>';
                    }else if($vo['title'] && count($thumbs) > 1)
                    {
                        $str .='<a  class="w-block" href="/Article/info/id/'.$vo['id'].'"><div class="w-bgcolorFFF w-padding02 w-marginBottom01">';
                        
                        $str .='<div class="threeLine">';
                        if($vo['isyc'] == 1)
                        {
                            $str .='<span  class="iszbox">[转]</span>';
                        }
                        $str .= $vo['title'];
                        $str .='<span class="threeMark">...<em class="w-colorf81515">全文</em></span>';
                        $str .='</div>';
                        $str .='<div class="w-font0 threeImgBox w-position w-marginBottom02">';
                        foreach ($thumbs as $k=>$thumb)
                        {
                            if($k <= 2)
                            {
                                $str .='<div class="threeImg"><img src="'.$thumb.'" alt=""/></div>';
                            }
                        }
                        if(count($thumbs) > 3)
                        {
                            $str .='<div class="threeFixed"><span>+'.count($thumbs).'</span></div>';
                        }
                        $str .='</div>';
                        $str .='<div class="w-height03 w-onlyline w-font12 w-color999">';
                        $str .='<span class="w-btn1 w-marginRight02">'.$lab.'</span>';
                        $str .='<em class="w-marginRight02">'.$author.'</em>';
                        $str .='<em>'.$vo['pls'].'评论</em>';
                        $str .='</div>';
                        $str .='</div></a>';
                    }
                }
            }
        }
        return $str;
    }

    public  function get_list($search=array(),$order='',$page=1,$pageSize=10,$type=0){

        if(empty($order))
        {
            $order = 'a.sort asc,a.id desc';
        }
        $where = array();
        if($search['tj'] && $search['tj'] != 'a')
        {
            $where['a.tj'] = $search['tj'];
        }

        if($search['author'])
        {
            $where['a.author'] = $search['author'];
        }

        if($search['isyc'] && $search['isyc'] != 'a')
        {
            $where['a.isyc'] = intval($search['isyc']);
//            $where['a.isyc'] = 0;
        }

//        var_dump($search['isyc']);


//        if($search['isyc'] == 1)
//        {
//            $where['a.isyc'] = 1;
//        }
//        if($search['isyc'] == 0)
//        {
//            $where['a.isyc'] = 0;
//        }

        if($search['label'] && $search['label'] != 'a')
        {
            $where['a.label'] = $search['label'];
        }

        if($search['keywords'] == '平台')
        {
            $where['a.author'] = 0;
        }else
        {
            if($search['keywords'])
            {
                $where['a.content|a.title|M.nickname'] = array('like',"%{$search['keywords']}%");
            }
        }


        if($search['st_time']&&$search['end_time']){
            if($search['st_time']>$search['end_time']){
                $this->error = '开始时间大于结束时间';
                return false;
            }else{
                $search['end_time'] = strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
                $where['a.create_time'] = array(array('egt',$search['st_time']),array('elt',$search['end_time']));
                //有开始时间和结束时间
            }
        }elseif($search['st_time']&&!$search['end_time']){
            $where['a.create_time'] = array('egt',$search['st_time']);//有开始时间无结束时间
        }elseif(!$search['st_time']&&$search['end_time']){
            $search['end_time'] = strtotime(date('Y-m-d',$search['end_time']).' 23:59:59');
            $where['a.create_time'] = array('elt',$search['end_time']);//无开始时间有结束时间
        }
        $where['a.is_delete'] = 0;
        $where['a.type'] = $type;

        $count = $this->alias('a')
            ->join('LEFT JOIN __MEMBER__ M on M.id = a.author')
            ->where($where)
            ->count();



        if($pageSize == 0 && $count > 10000){
            $this->error='数据太多，请筛选后操作';
            return false;
        }
        $field='a.*,M.nickname';
        $list = $this->alias('a')
            ->join('LEFT JOIN __MEMBER__ M on M.id = a.author')
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($page,$pageSize)
            ->select();
        if($list)
        {
            foreach($list as $key=>&$vo)
            {



                $vo['create_time'] = date('Y-m-d H:i:s',$vo['create_time']);
                $vo['tj'] = $vo['tj'] == 1 ? '是' : '否';
                $vo['author'] = $vo['author'];
                if($vo['isyc'] == 1)
                {
                    $vo['title'] = '[转发]'.$vo['title'];
                }else
                {
                    $vo['title'] = '[原创]'.$vo['title'];
                }
                $vo['label'] = M('Label')->where(array('id'=>$vo['label'],'status'=>1))->getField('name');

                if($vo['author'] == 0)
                {
                    $vo['authorname'] = '平台';
                }
                if(!$vo['authorname'] && $vo['author']>0)
                {
                    $vo['authorname'] = M('Member')->where(array('id'=>$vo['author']))->getField('nickname');
                }

            }
        }

        $total_page = ceil($count / $pageSize);
        $result['list']=$list;
        $result['p']=$page;
        $result['total']=$count;
        $result['pagesize']=$pageSize;
        $result['total_page']=$total_page;
        return $result;
    }

    public function get_info($id){
        $info=$this->where(array('id'=>$id))->find();
        return $info;
    }

    //批量删除
    public function deleteBatch($ids){
        $ids=ids_to_ids($ids);
        if($ids){
            $res=$this->where(array('id'=>array('in',$ids)))->setField('is_delete',1);
            if($res){
                $res=$this->where(array('id'=>array('in',$ids)))->getField('title',true);
                return implode(',',$res);
            }else{
                $this->error="操作失败";
                return false;
            }

        }else{
            $this->error="请至少选择一条信息";
            return false;
        }
    }
}