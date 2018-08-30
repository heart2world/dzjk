<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/8
 * Time: 14:01
 */

namespace Common\Model;


class DestinationModel extends CommonModel
{

    public function get_list($p){
        $result=$this
            ->field("id,name,pid as parentid,logo,sort,main_pic")
            ->where(array('is_delete'=>0))
            ->select();



        $flag = array();

        foreach($result as $v){
            $flag[] = $v['sort'];
        }

        array_multisort($flag, SORT_ASC, $result);

        $totals = count($result);
        $pageSize = 999;
        $countPage = ceil($totals/$pageSize);
        $start = ($p-1)*$pageSize;  //开始

        $pageData = array_slice($result,$start,$pageSize);



       // var_dump($pageData);
        $list['total'] = $totals;
        $list['total_page'] = $countPage;

        $list['p'] = $p;


        import("Tree");
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';



        //var_dump($result);
        foreach ($pageData as $n=> $r) {
            $pageData[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-' . $r['parentid'] . '"' : '';
            $id = $r['id'];
            $name = $r['name'];
            if($r['parentid'] == 0){
                $pageData[$n]['str_manage'] = ' <a href="javascript:;" class="btn btn-small btn-success" onclick="add_child('.$id.',\''.$name.'\')"> '.'添加子分类 '.'</a>'
                    .' <a href="javascript:;" class="btn btn-small btn-info" onclick="edit('.$id.')">'.'编辑 '.'</a> '.
                    ' <a href="javascript:;" class="btn btn-small btn-danger" onclick="delete_one('.$id.')"> '.'删除 '.' </a>';
            }else{
                $pageData[$n]['str_manage'] = ' <a href="javascript:;" class="btn btn-small btn-info" onclick="edit('.$id.',true)">'.'编辑 '.'</a> '.
                    ' <a href="javascript:;" class="btn btn-small btn-danger" onclick="delete_one('.$id.')"> '.'删除 '.' </a>';
            }
        }

        $tree->init($pageData);
        $str = "<tr id='node-\$id' \$parentid_node>

                     <td><input  style='margin-top:0px' class='checkbox' type='checkbox' value='\$id'></td>

                     <td style='width: 40%;padding-left: 130px;'><img src='\$logo' width='50px' class='images' name='images'><span>\$spacer\$name</span></td>

                    <td>\$sort</td>

					<td >\$str_manage</td>

				</tr>";
        $categorys = $tree->get_tree(0, $str);

        $list['tree'] = $categorys;

        return $list;

    }

    //前端首页特惠列表
    public function get_f_discount($city,$search,$all=false){
        $data['p'] = empty($data['p'])?1:$data['p'];
        $data['pageNum'] = empty($data['pageNum'])?0:$data['pageNum'];
        if(!empty($search['a_type'] ) && $search['a_type']>0){
            $where['a_type'] = $search['a_type'];
        }
        if($all){
            $limit = 999;
        }else{
            $limit = 5;
        }
        $where['status'] = 1;
        $where['city'] = $city;
        //$where['title'] = array('neq','');
        $where['_string'] = "title is not null or title <> ''";

        $count =   M()
            ->table('get_discount')
            ->field("*")
            ->where($where)
            ->count();


        $all= M()
            ->table('get_discount')
            ->field("*")
            ->where($where)
            ->limit($limit)
            ->page($search['p'],$search['pageNum'])
            ->select();

        $Result['p'] = $search['p'];
        $Result['list'] = $all;
        $Result['totalPage'] = ceil($count/$search['pageNum']);
        return  $Result;

    }
    //前端首页周边
    public function get_lat_lng_d($city){
        $arr = $this->where(array('id'=>$city))->find();
        $province = $this->where(array('id'=>$city))->getField('province');

        $lng = $arr['lng'];
        $lat = $arr['lat'];
        $list = $this
            ->alias('d')
            ->field("d.id,d.name,d.main_pic,d.city,d.lng,d.lat,GetDistance(lng,lat,$lng,$lat) as juli")
            ->where(array('d.pid'=>array('neq',0),'d.is_delete'=>0,'d.id'=>array('neq',$city),'d.province'=>$province))
            ->order('juli asc')
            ->limit(6)
            ->select();
        //echo $this->getLastSql();
        foreach($list as $k=>$y){
            $list[$k]['juli'] = round(($y['juli']/1000),1);
        }
        return $list;

    }

    public function add_class($data){
        if(!isset($data['parent_id'])){
            $this->error = "系统错误";
            return false;
        }
        if(empty($data['name'])){
            $this->error = "请填写分类名";
            return false;
        }
        $result = $this->where(array('name'=>$data['name'],'is_delete'=>0))->select();
        if($result){
            $this->error = "该分类名已存在";
            return false;
        }

        if($data['parent_id']==0){
            $arr['pid'] = $data['parent_id'];
            $arr['name'] = $data['name'];
            $arr['sort'] = $data['sort'];
            if(empty($arr['sort'])){
                $arr['sort'] = 99;
            }
            $res = $this->add($arr);
            if($res){
                return true;
            }else{
                $this->error = "添加失败";
                return false;
            }
        }else{
                if(empty($data['lng']) || empty($data['lat'])){
                $this->error = "您填写的地址没有解析到结果";
                return false;
            }
            $arr['province'] = $data['province'];
            $arr['city'] = $data['city'];
            $arr['lng'] = $data['lng'];
            $arr['lat'] = $data['lat'];
            $arr['logo'] = $data['logo'];
            $arr['main_pic'] = $data['main_pic'];
            $arr['pid'] = $data['parent_id'];
            $arr['name'] = $data['name'];
            $arr['sort'] = $data['sort'];
            if(empty($arr['sort'])){
                $arr['sort'] = 99;
            }
            $res = $this->add($arr);
            if($res){
                return true;
            }else{
                $this->error = "添加失败";
                return false;
            }

        }
    }

    /**
     * 获取所有一级地区
     * @return mixed
     */
    public function get_destination_one(){
        $list=S('one_all');
        if($list){
            return $list;
        }else{
            $list=$this->where(array('pid'=>0,'is_delete'=>0))->order("sort asc,id desc")->select();
            S("one_all",$list);
            return $list;
        }
    }

    /**
     * 获取二级
     * @return mixed
     */
    public function get_destination_two($id){
        $list=S('child_'.$id);
        if($list){
            return $list;
        }else{
            $list=$this->where(array('pid'=>$id,'is_delete'=>0))->order("sort asc,id desc")->select();
            S("child_".$id,$list);
            return $list;
        }
    }

    /**
     * @param $city int类型 城市ID
     * @return mixed  array数组 区域ID
     */
    public function get_destination_id_by_city($city,$lng = '',$lat = ''){
        if($lng&&$lat){
            $id=$this->where(array('city'=>$city,'is_delete'=>0,'pid'=>array('gt',0)))->order("GetDistance(lng,lat,'$lng','$lat') asc")->getField('id');
            return $id;
        }else{
            $list=S("get_did_by_$city");
            if($list){
                return $list;
            }else{
                $list=$this->where(array('city'=>$city,'is_delete'=>0,'pid'=>array('gt',0)))->order('sort asc')->getField('id');
                S("get_did_by_$city",$list);
                return $list;
            }
        }
    }

    /**
     * @param $id 目的地ID
     * @return mixed 目的地信息
     */
    public function get_info($id){
        $info=S('info_'.$id);
        if($info){
            return $info;
        }else{
            $info=$this->where(array('id'=>$id))->find();
            S('info_'.$id,$info);
            return $info;
        }
    }


    //目的地列表  $is_img是否需要图片
    public function get_d_list($parent,$is_img=false){
        if($is_img!=false){
            $field = 'id,name,main_pic';
        }else{
            $field = 'id,name';
        }
        $where['is_delete'] = 0;
        $where['pid'] = 0;
        if($parent){
            $where['pid'] = $parent;
        }
        $list = $this
            ->field($field)
            ->where($where)
            ->order('sort asc')
            ->select();

        return $list;
    }

    /**
     * 获取全部城市数据 目的地表destination
     * @return mixed
     */
    public function get_destination_tree(){
        $list=S('all_tree');
        if($list){
            return $list;
        }else{
            $list=$this->field("id,name")->where(array('pid'=>0,'is_delete'=>0))->order("sort asc,id desc")->select();
            foreach($list as $key=>$vo){
                $child=$this->field("id,name")->where(array('pid'=>$vo['id'],'is_delete'=>0))->order("sort asc,id desc")->select();
                if(!$child){
                    $child[]=array(
                        'id'=>0,
                        'name'=>'暂无城市'
                    );
                }
                $list[$key]['child']=$child;
            }
            S("all_tree",$list);
            return $list;
        }
    }
    /**
     * 获取全部城市数据 region
     * @return mixed
     */
    public function get_region_tree(){
        $list=S('all_region_tree');
        if($list){
            return $list;
        }else{
            $list=M('Region')->field("region_id as id,region_name as name")->where(array('parent_id'=>0,'is_delete'=>0))->select();
            foreach($list as $key=>$vo){
                $child=M('Region')->field("region_id as id,region_name as name")->where(array('parent_id'=>$vo['id'],'is_delete'=>0))->select();
                $list[$key]['child']=$child;
            }
            S("all_region_tree",$list);
            return $list;
        }
    }
    /**
     * 获取全部目的地 目的地表destination
     * 用首字母排序
     * @return mixed
     */
    public function get_destination_sort(){
        $list=S('destination_sort');
        if($list){
            return $list;
        }else{
            $list = $this->field('id,name')->where(array('pid'=>array('neq',0),'is_delete'=>0))->select();
            $arr = array();
            foreach($list as $k=>$y){
                $list[$k]['char'] = getFirstCharter($y['name']);
                $arr[getFirstCharter($y['name'])][] = $y;
            }
            ksort($arr);
            foreach($arr as $k=>$y){
                if(empty($k)){
                    unset($arr[$k]);
                    array_push($arr,$y);
                }
            }
            S('destination_sort',$arr);
            return $arr;
        }
    }



    //清除缓存
    function _before_write(){
        $cache=\Think\Cache::getInstance();
        $cache->clear();
    }
}