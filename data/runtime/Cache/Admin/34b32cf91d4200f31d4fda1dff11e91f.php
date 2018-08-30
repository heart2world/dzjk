<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
	<link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
    <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
		.length_3{width: 180px;}
		form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
		.table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
        .table-list{margin-bottom: 0px;}
		[v-cloak] {  display: none;  }
	</style>
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
    <script type="text/javascript">
    //全局变量
    var GV = {
        DIMAUB: "/",
        JS_ROOT: "public/js/",
        TOKEN: ""
    };
    </script>
    <script src="/public/js/jquery.js"></script>
    <script src="/public/js/wind.js"></script>
    <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
<?php if(APP_DEBUG): ?><style>
		#think_page_trace_open{
			z-index:9999;
		}
	</style><?php endif; ?>

	<style type="text/css">
		/*移除HTML5 input在type="number"时的上下小箭头*/
		/*在chrome下：*/
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button{
			-webkit-appearance: none !important;
			margin: 0;
		}

		/*Firefox下：*/
		input[type="number"]{
			-moz-appearance:textfield;
		}

	</style>
    <script>
        <?php if($upload_type == 'local'): ?>var upload_server_path="../../../index.php?g=Asset&m=Ueditor&a=upload";
        <?php else: ?>
            <?php $upload_path=C('UPLOAD_ACTION_HOST').'&appKey='.C('UPLOAD_ACTION_KEY').'&module='.CONTROLLER_NAME.'&field='.ACTION_NAME; ?>
            var upload_server_path="<?php echo ($upload_path); ?>";<?php endif; ?>
    </script>
<style type="text/css">
   .table  tr th{text-align: center;}
   .table  tr td{text-align: center;}
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('Finance/index');?>">财务列表</a></li>
    </ul>
    <form class="well form-search" method="post" action="<?php echo U('Finance/index');?>">    
        时间：
        <input type="text" name="st_time" autocomplete="off" placeholder="开始时间" style="width: 90px;" value="<?php echo ((isset($formget["st_time"]) && ($formget["st_time"] !== ""))?($formget["st_time"]):''); ?>" class="input js-date date">-
        <input type="text" name="ed_time" autocomplete="off" placeholder="结束时间" style="width: 90px;" value="<?php echo ((isset($formget["ed_time"]) && ($formget["ed_time"] !== ""))?($formget["ed_time"]):''); ?>" class="input js-date date">
        用户名：
        <input type="text" name="username" style="width: 110px;"  placeholder="" value="<?php echo ((isset($formget["username"]) && ($formget["username"] !== ""))?($formget["username"]):''); ?>">        
        用户类别：
        <select name="type" style="width: 80px;">
            <option value="">全部</option>
            <option value="医生" <?php if($formget['type'] == '医生'): ?>selected<?php endif; ?>>医生</option>
            <option value="普通用户" <?php if($formget['type'] == '普通用户'): ?>selected<?php endif; ?>>普通用户</option>
        </select>
        手机号：
        <input type="text" name="mobile" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["mobile"]) && ($formget["mobile"] !== ""))?($formget["mobile"]):''); ?>">
        操作类别：
        <select name="handeltype" style="width: 80px;">
            <option value="">全部</option>
            <option value="提现" <?php if($formget['handeltype'] == '提现'): ?>selected<?php endif; ?>>提现</option>
            <option value="退款" <?php if($formget['handeltype'] == '退款'): ?>selected<?php endif; ?>>退款</option>
            <option value="提问" <?php if($formget['handeltype'] == '提问'): ?>selected<?php endif; ?>>提问</option>
        </select>
        <input type="submit" class="btn btn-primary" value="筛选">
    </form>
    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <td style="text-align: left" >
                收入：<?php echo ($total["tiwen"]); ?>元  &nbsp;&nbsp;退款：<?php echo ($total["tuikuan"]); ?>元&nbsp;&nbsp;提现：<?php echo ($total["tixian"]); ?>元
            </td>
        </tr>
        </thead>
    </table>
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">           
            <th>日期</th>
            <th>用户昵称</th>
            <th>用户类别</th>
            <th>用户手机号</th>
            <th>操作</th>
            <th>金额</th>
        </tr>
        </thead>
        <tbody>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>               
                <td><?php echo (date('Y-m-d H:i:s',$val["createtime"])); ?></td>
                <td><?php echo ($val["username"]); ?></td>
                <td><?php echo ($val["type"]); ?></td>
                <td><?php echo ($val["mobile"]); ?></td>
                <td><?php echo ($val["handeltype"]); ?></td>               
                <td><?php echo ($val["coin"]); ?></td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
        <div class="pagination" style="float: right;"><?php echo ($page); ?></div>
    </table>
</div>
<script src="/public/js/common.js"></script>
</body>
</html>