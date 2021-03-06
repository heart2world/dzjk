<?php

/* * 
 * 公共模型
 */

namespace Common\Model;
use Think\Model;

class CommonModel extends Model {
    // 最近错误信息

    protected $code =   0;
    protected $errorMsg;

    public function setErrorCode($code){
        $this->error=$this->errorMsg[$code];
        $this->code=$code;
    }
    /**

     * 返回模型的错误信息

     * @access public

     * @return string

     */

    public function getCode(){

        return $this->code;

    }
    /**
     * 删除表
     */
    final public function drop_table($tablename) {
        $tablename = C("DB_PREFIX") . $tablename;
        return $this->query("DROP TABLE $tablename");
    }

    /**
     * 读取全部表名
     */
    final public function list_tables() {
        $tables = array();
        $data = $this->query("SHOW TABLES");
        foreach ($data as $k => $v) {
            $tables[] = $v['tables_in_' . strtolower(C("DB_NAME"))];
        }
        return $tables;
    }

    /**
     * 检查表是否存在 
     * $table 不带表前缀
     */
    final public function table_exists($table) {
        $tables = $this->list_tables();
        return in_array(C("DB_PREFIX") . $table, $tables) ? true : false;
    }

    /**
     * 获取表字段 
     * $table 不带表前缀
     */
    final public function get_fields($table) {
        $fields = array();
        $table = C("DB_PREFIX") . $table;
        $data = $this->query("SHOW COLUMNS FROM $table");
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }

    /**
     * 检查字段是否存在
     * $table 不带表前缀
     */
    final public function field_exists($table, $field) {
        $fields = $this->get_fields($table);
        return array_key_exists($field, $fields);
    }
    
    protected function _before_write(&$data) {
        
    }


    /**
     * 返回以二维数组的值为键的二维数组
     * @param $arr  //遍历的二维数组
     * @param $val    //值的键名
     * @return array    //返回新数组
     */
    final public function getKeyByVal($arr, $val)
    {
        $array = array();
        foreach ($arr as $v) {
            $array[$v[$val]] = $v;
        }
        return $array;
    }

    public function deleteBatch($ids,$field='id'){
        $ids=ids_to_ids($ids);
        if(!$ids){
            $this->error='参数错误';
            return false;
        }
        $res=$this->where(array($field=>array('in',$ids)))->setField('is_delete',1);
        if($res!==false){
            return true;
        }else{
            $this->error='删除失败';
            return false;
        }
    }
}

