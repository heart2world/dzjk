<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class MainController extends AdminbaseController {

	

    public function index(){

        $group_id = M('AuthGroupAccess')->where(array('uid'=>$_SESSION['ADMIN_ID']))->getField('group_id');
        if($group_id == 23)
        {
            $mid = M('Users')->field('mid,last_login_time')->where(array('id'=>$_SESSION['ADMIN_ID']))->find();
            $nickname = M('Member')->where(array('id'=>$mid['mid']))->getField('nickname');

            echo '<div style="margin-left: 30%;font-size: 20px;">'.$nickname.' 欢迎登录'.'<br>';
            echo '上次登录时间:'.$mid['last_login_time'].'</div>';



        }else
        {
            //系统信息
            $mysql= M()->query("select VERSION() as version");
            $mysql=$mysql[0]['version'];
            $mysql=empty($mysql)?L('UNKNOWN'):$mysql;
            //server infomaions
            $info = array(
                L('OPERATING_SYSTEM') => PHP_OS,
                L('OPERATING_ENVIRONMENT') => $_SERVER["SERVER_SOFTWARE"],
                L('PHP_RUN_MODE') => php_sapi_name(),
                L('MYSQL_VERSION') =>$mysql,
                L('PROGRAM_VERSION') => SIMPLEWIND_CMF_VERSION . "&nbsp;&nbsp;&nbsp;",
                L('UPLOAD_MAX_FILESIZE') => ini_get('upload_max_filesize'),
                L('MAX_EXECUTION_TIME') => ini_get('max_execution_time') . "s",
                L('DISK_FREE_SPACE') => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            );
            $this->assign('server_info', $info);
            $this->display();
        }



    }

}