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
    .row-fluid{
        display:none;position: fixed;  top: 35%;border-radius: 3px;  left: 40%; width: 15%;height: 150px; overflow:hidden; overflow-y: auto;  padding: 8px;  border: 1px solid #E8E9F7;  background-color: white;  z-index:10003;
    }
    #bg{ display: none;  position: fixed;  top: 0%;  left: 0%;  width: 100%;  height: 100%;  background-color: black;  z-index:1001;  -moz-opacity: 0.7;  opacity:.70;  filter: alpha(opacity=70);}
</style>
</head>
<body>
    <div class="wrap js-check-wrap" id="seckill">
        <ul class="nav nav-tabs">
            <li class="active"><a href="<?php echo U('Finance/cash');?>">提现申请</a></li>
        </ul>
        <form class="well form-search" method="post" action="<?php echo U('Finance/cash');?>">    
            姓名：
            <input type="text" name="username" style="width: 110px;"  placeholder="" value="<?php echo ((isset($formget["username"]) && ($formget["username"] !== ""))?($formget["username"]):''); ?>">        
            昵称：
            <input type="text" name="nicename" style="width: 110px;"  placeholder="" value="<?php echo ((isset($formget["nicename"]) && ($formget["nicename"] !== ""))?($formget["nicename"]):''); ?>">
            手机号：
            <input type="text" name="mobile" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["mobile"]) && ($formget["mobile"] !== ""))?($formget["mobile"]):''); ?>">
            申请时间：
            <input type="text" name="st_time" autocomplete="off" placeholder="开始时间" style="width: 90px;" value="<?php echo ((isset($formget["st_time"]) && ($formget["st_time"] !== ""))?($formget["st_time"]):''); ?>" class="input js-date date">-
            <input type="text" name="ed_time" autocomplete="off" placeholder="结束时间" style="width: 90px;" value="<?php echo ((isset($formget["ed_time"]) && ($formget["ed_time"] !== ""))?($formget["ed_time"]):''); ?>" class="input js-date date">
            审核状态：
            <select name="status" style="width: 80px;">
                <option value="-1">全部</option>
                <option value="0" <?php if($formget['status'] == '0'): ?>selected<?php endif; ?>>待审核</option>
                <option value="1" <?php if($formget['status'] == '1'): ?>selected<?php endif; ?>>已通过</option>
                <option value="2" <?php if($formget['status'] == '2'): ?>selected<?php endif; ?>>已拒绝</option>
            </select>
            <input type="submit" class="btn btn-primary" value="筛选"/>
        </form>
         <table  class="table table-hover table-bordered table-list">
            <thead>
                <tr id="tr">           
                    <th>姓名</th>
                    <th>昵称</th>
                    <th>手机号</th>
                    <th>提现金额</th>
                    <th>申请时间</th>
                    <th>审核状态</th>
                    <th>操作员</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>               
                        <td><?php echo ($val["username"]); ?></td>
                        <td><?php echo ($val["nicename"]); ?></td>
                        <td><?php echo ($val["mobile"]); ?></td>
                        <td><?php echo ($val["coin"]); ?></td>
                        <td><?php echo (date('Y-m-d H:i:s',$val["createtime"])); ?></td>
                        <td><?php echo ($val["statusname"]); ?></td>               
                        <td><?php echo ($val["adminname"]); ?></td>
                        <td>
                            <?php if($val['status'] == '0'): ?><a href="javascript:void(0);" onclick="show_add('<?php echo ($val["id"]); ?>')" class="btn" style="padding: 0px 8px;">审核</a><?php endif; ?>
                        </td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
            <div class="pagination" style="float: right;"><?php echo ($page); ?></div>
        </table>
        
        <div class="control-group">
            <div class="row-fluid" id="company_add" style="display: none">
                <fieldset class="form-horizontal" style="margin-top: 20px;">
                   <span style="text-align: center;font-size: 16px; margin-left: 40px;">确认通过本条提现申请？</span>
                   <input type="hidden" id="pjid">       
                </fieldset>
                <div style="height: 60px;border-bottom: 1px solid #ccc;"></div>
                <div style="text-align: center;margin-top: 10px;">
                    <a href="javascript:;" class="btn btn-primary" onclick="changestatus('1')">通过审核</a>&nbsp;&nbsp;&nbsp;
                    <a href="javascript:;" class="btn btn-primary" onclick="changestatus('2')">拒绝</a>
                </div>
            </div>
        </div>
    </div>
<div id="bg" onclick="close_div()"></div>
<script src="/public/js/common.js"></script>
<script type="text/javascript">
    function close_div() {  
        $('.row-fluid').css('display','none');
        $('#bg').css('display','none');
    }
    function show_add(ids) {
        $("#pjid").val(ids);
        $("#bg").css('display','block');
        $('#company_add').css('display','block');
    }
 function changestatus(status){
        var pjid =$("#pjid").val();     
        $.ajax({
            url: "<?php echo U('Finance/changestatus');?>",
            type: 'POST',
            data: {id:pjid,status:status},
            success:function (res) {
                if(res.status == 0){ 
                    
                    location.href='<?php echo U("Finance/cash");?>';                        
                } else {
                    $.dialog({id: 'popup', lock: true,icon:"warning", content: res.msg, time: 2});
                }
            }
        });
    }
</script>
</body>
</html>