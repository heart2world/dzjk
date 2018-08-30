<?php
/**
 * 区域管理
 */

namespace Common\Model;

use Common\Model\CommonModel;

class RegionModel extends CommonModel
{

    public function get_all()
    {
        $list = F('Region_all');
        if ($list) {
            return $list;
        } else {
            $list = $this->field('region_id as id,region_name as name')
                ->where(array('is_delete' => 0, 'parent_id' => 0, 'region_type' => 1))
                ->order('first_pinyin asc')
                ->select();
            foreach ($list as $key => $vo) {
                $list[$key]['child'] = $this->field('region_id as id,region_name as name')
                    ->where(array('is_delete' => 0, 'parent_id' => $vo['id'], 'region_type' => 2))
                    ->order('first_pinyin asc')
                    ->select();
            }
            F('Region_all', $list);
            return $list;
        }
    }

    public function get_all_three()
    {
        $list = F('Region_all_three');
        if ($list) {
            return $list;
        } else {
            $list = $this->field('region_id as id,region_name as name')
                ->where(array('is_delete' => 0, 'parent_id' => 0, 'region_type' => 1))
                ->order('first_pinyin asc')
                ->select();
            foreach ($list as $key => $vo) {
                $list[$key]['child'] = $this->field('region_id as id,region_name as name')
                    ->where(array('is_delete' => 0, 'parent_id' => $vo['id'], 'region_type' => 2))
                    ->order('first_pinyin asc')
                    ->select();
                foreach ($list[$key]['child'] as $k => $v) {
                    $list[$key]['child'][$k]['child'] = $this->field('region_id as id,region_name as name')
                        ->where(array('is_delete' => 0, 'parent_id' => $v['id'], 'region_type' => 3))
                        ->order('first_pinyin asc')
                        ->select();
                }
            }
            F('Region_all_three', $list);
            return $list;
        }
    }

    public function get_where_three($province, $city, $area)
    {
        $list = $this->field('region_id as id,region_name as name')
            ->where(array('is_delete' => 0, 'parent_id' => 0, 'region_type' => 1,'region_id'=>$province))
            ->order('first_pinyin asc')
            ->select();
        foreach ($list as $key => $vo) {
            $list[$key]['child'] = $this->field('region_id as id,region_name as name')
                ->where(array('is_delete' => 0, 'parent_id' => $vo['id'], 'region_type' => 2,'region_id'=>$city))
                ->order('first_pinyin asc')
                ->select();
            foreach ($list[$key]['child'] as $k => $v) {
                $list[$key]['child'][$k]['child'] = $this->field('region_id as id,region_name as name')
                    ->where(array('is_delete' => 0, 'parent_id' => $v['id'], 'region_type' => 3,'region_id'=>array('in',$area)))
                    ->order('first_pinyin asc')
                    ->select();
            }
        }
        return $list;
    }

    protected function _before_write(&$data)
    {
        F('Region_all', NULL);
    }
}