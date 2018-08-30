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
        <li class="active"><a href="<?php echo U('Question/index');?>">答题管理</a></li>
    </ul>
    <form class="well form-search" method="post" action="<?php echo U('Question/index');?>">    
        问题内容：
        <input type="text" name="questionname" style="width: 110px;"  placeholder="" value="<?php echo ((isset($formget["questionname"]) && ($formget["questionname"] !== ""))?($formget["questionname"]):''); ?>">
        提问人：
        <input type="text" name="mname" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["mname"]) && ($formget["mname"] !== ""))?($formget["mname"]):''); ?>">
       被提问人：
        <input type="text" name="doctornicename" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["doctornicename"]) && ($formget["doctornicename"] !== ""))?($formget["doctornicename"]):''); ?>">
        提问时间：
        <input type="text" name="st_time" autocomplete="off" placeholder="开始时间" style="width: 90px;" value="<?php echo ((isset($formget["st_time"]) && ($formget["st_time"] !== ""))?($formget["st_time"]):''); ?>" class="input js-date date">-
        <input type="text" name="ed_time" autocomplete="off" placeholder="结束时间" style="width: 90px;" value="<?php echo ((isset($formget["ed_time"]) && ($formget["ed_time"] !== ""))?($formget["ed_time"]):''); ?>" class="input js-date date">&nbsp;
        问题状态：
        <select name="status" style="width: 80px;">
            <option value="0">全部</option>
            <option value="1" <?php if($formget['status'] == 1): ?>selected<?php endif; ?>>待回应</option>
            <option value="2" <?php if($formget['status'] == 2): ?>selected<?php endif; ?>>咨询中</option>
            <option value="3" <?php if($formget['status'] == 3): ?>selected<?php endif; ?>>已取消</option>
            <option value="4" <?php if($formget['status'] == 4): ?>selected<?php endif; ?>>已结束</option>
        </select>
        <input type="submit" class="btn btn-primary" value="筛选"><br/>
    </form>
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">           
            <th>提问人</th>
            <th>问题内容</th>
            <th>被提问姓名</th>
            <th>被提问人昵称</th>
            <th>提问时间</th>
            <th>回应时间</th>
            <th>问题状态</th>
            <th>提问结束时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>               
                <td><?php echo ($val["mname"]); ?></td>
                <td><?php echo ($val["questionname"]); ?></td>
                <td><?php echo ($val["doctor"]); ?></td>
                <td><?php echo ($val["doctornicename"]); ?></td>
                <td><?php echo (date('Y-m-d H:i:s',$val["questiontime"])); ?></td>
                <td><?php echo (date('Y-m-d H:i:s',$val["createtime"])); ?></td>
                <td><?php echo ($val["statusname"]); ?></td>
                <td><?php if($val['endtime'] > 0): echo (date('Y-m-d H:i:s',$val["endtime"])); endif; ?></td>
                <td>
                    <a class="btn" href="<?php echo U('Question/detail',array('id'=>$val['id']));?>">查看详情</a>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
        <div class="pagination" style="float: right;"><?php echo ($page); ?></div>
    </table>
</div>

<script src="/public/js/common.js"></script>
</body>
</html>