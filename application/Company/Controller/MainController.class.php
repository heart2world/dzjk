<?php

namespace Company\Controller;

use Think\Controller;

class MainController extends BaseController {

	

    public function index(){
        
            //系统信息
            $mysql= M()->query("select VERSION() as version");
            $mysql=$mysql[0]['version'];
            $mysql=empty($mysql)?L('UNKNOWN'):$mysql;
            //server infomaions
            $info = array(
                '操作系统' => PHP_OS,
                '运行环境' => $_SERVER["SERVER_SOFTWARE"],
                'PHP运行方式' => php_sapi_name(),
                'MYSQL版本' =>$mysql,
                '程序版本' => SIMPLEWIND_CMF_VERSION . "&nbsp;&nbsp;&nbsp;",
                '上传附件限制' => ini_get('upload_max_filesize'),
                '执行时间限制' => ini_get('max_execution_time') . "s",
                '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            );
            $this->assign('server_info', $info);
            $this->display();


    }

}