<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\Auth\AuthGroupModel;
class DUserController extends AdminbaseController
{

    protected $users_model, $role_model;


    function _initialize()
    {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = new AuthGroupModel();
    }

    function index()
    {
        $id = M('Users')->where(array('id'=>$_SESSION['ADMIN_ID']))->getField('mid');

        $Member = M('Member')
            ->alias('M')
            ->field('*')
            ->join('LEFT JOIN __MEMBER_INTRO__ MI on M.id = MI.pid')
            ->where(array('M.id' => $id, 'M.types' => 2))->find();
        $Member['iszx'] = $Member['iszx'] == 1 ? '未开启' : '已开启';

        $Member['sfz'] = explode(',', $Member['sfz']);
        $Member['zyzgz'] = explode(',', $Member['zyzgz']);
        $Member['wzs'] = M('Article')
            ->where(array('author' => $Member['pid'], 'type' => 0))->count();
        $Member['dts'] = M('Article')
            ->where(array('author' => $Member['pid'], 'type' => 1))->count();
//        $Member['gzs'] = M('MemberFollo')
//            ->where(array('to_id' => $Member['to_id']))->count();

        $Member['gzs'] = M('MemberFollo')->where(array('member_id'=>$Member['pid']))->count();

        $Member['zy'] = M('Label')
            ->where(array('id' => $Member['zy']))->getField('name');

        if($Member['prov'])
        $Member['prov'] = M('Region')
            ->where(array('region_id' =>$Member['prov'],'region_type'=>1))->getField('region_name');

        if($Member['city'])
        $Member['city'] = M('Region')
            ->where(array('region_id' =>$Member['city'],'region_type'=>2))->getField('region_name');

        $Member['grjsimg'] = explode(',', $Member['grjsimg']);

        $this->info = $Member;
        $this->display();

    }
    //个人资料修改
    public function dusr_edit(){
        $name=trim(I("name"));
        $intro=I("intro");
        $id=I("id");
        $pid=I("pid");
        $Member = M('Member');
        $Member->startTrans();
        try{
            $re=$Member->where(array('id'=>$id))->setField(array('nickname'=>$name));
            $re2=M("MemberIntro")->where(array('id'=>$pid))->setField(array('grjs'=>$intro));

                $Member->commit();
                $this->success("保存成功");
        }catch(\Exception $e){
            $Member->rollback();
            $this->error("保存失败");
        }


    }


}