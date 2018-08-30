<?php

/*
 * 操作日志
 * */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Model\AdveModel;

class AdveController extends AdminbaseController
{
	public function __construct()
	{
		parent::__construct();
        $this->adve_mode = new AdveModel();
        $this->pageNum = 10;
	}

	public function index()
	{

		if (IS_AJAX)
		{
            $p = I('p');
            $search = I('');
            $data = $this->adve_mode->get_list($search,10);
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

	public function add()
    {
        $data = I('post.');
        $row['links'] = $data['links'];
        $row['pic'] = $data['pic'];
        $row['lab'] = $data['label'];
        $row['is_delete'] = 0;
        $row['ggz'] = $data['ggz'];

        $row['st'] = strtotime($data['starttime1']);

        if(!$data['starttime1'])
        {
            $this->error('开始时间不能为空');
        }
        if(!$data['endtime1'])
        {
            $this->error('结束时间不能为空');
        }
        if(!$data['pic'])
        {
            $this->error('广告图片不能为空');
        }


        $row['ent'] = strtotime($data['endtime1'].' 23:59:59');
        if($row['st'] >= $row['ent'])
        {
            $this->error('开始时间不能大于结束时间');
        }
        $row['title'] = $data['title'];
        $newRow = array();
        if($data['sy'])
        {
            $newRow[] = $data['sy'];
        }
        if($data['sybq'])
        {
            $newRow[] = $data['sybq'];
        }
        if($data['wzxq'])
        {
            $newRow[] = $data['wzxq'];
        }
        $row['zsqy'] = implode(',',$newRow);
        if($row['st'] > time())
        {
            $row['status'] = 0;
        }elseif ($row['st'] < time())
        {
            $row['status'] = 1;
        }elseif ($row['end'] < time())
        {
            $row['status'] = 2;
        }
        if(intval($data['id']) > 0)
        {
            $res = M('Adve')->where(array('id'=>$data['id']))->save($row);
        }else
        {
            $res = M('Adve')->add($row);
        }
        if($res)
        {
            $this->success('操作成功');
        }else
        {
            $this->success('操作成功');
//            $this->error('操作失败');
        }
    }
    public function delete()
    {
        $id = I('id');
        if($id)
        {
//            ,'status'=>array('neq',0)
            $res = M('Adve')->where(array('id'=>array('in',$id)))->save(array('is_delete'=>1));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }
    //下架
    public function OffAction()
    {
        $id = I('id');
        if($id)
        {
//            $res = M('Adve')->where(array('id'=>array('in',$id)))->save(array('status'=>2));
            $res = M('Adve')->where(array('id'=>array('in',$id),'status'=>array('neq',0)))->save(array('status'=>2));
        }
        if($res)
        {
            $this->success('操作已成功');
        }else
        {
            $this->error('操作失败');
        }
    }









}