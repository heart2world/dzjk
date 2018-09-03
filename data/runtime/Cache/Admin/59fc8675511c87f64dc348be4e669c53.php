<?php if (!defined('THINK_PATH')) exit();?>    <!doctype html>
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
<style>
    .form-required{ color: red}
    body, ul, dl, dd, dt, ol, li, p, h1, h2, h3, h4, h5, h6, textarea, form, select, fieldset, table, td, div, input {margin:0;padding:0;-webkit-text-size-adjust: none}
    h1, h2, h3, h4, h5, h6{font-size:12px;font-weight:normal}
    body>div{margin:0 auto}
    div {text-align:left}
    a img {border:0}
    body { color: #333; text-align: center; font: 12px "宋体"; }
    ul, ol, li {list-style-type:none;vertical-align:0}
    a {outline-style:none;color:#535353;text-decoration:none}
    a:hover { color: #D40000; text-decoration: none}
    .selectbox{width:600px;height:220px;margin:0px auto;}
    .selectbox div{float:left;}
    .selectbox .select-bar{padding:0 20px;}
    .selectbox .select-bar select{width:200px;height:200px;border:1px #A0A0A4 solid;padding:4px;font-size:14px;font-family:"microsoft yahei";}
    .btn-bar{}
    .btn-bar p{margin-top:16px;}
    .btn-bar p .btn{width:50px;height:30px;cursor:pointer;font-family:simsun;font-size:14px;}
    .spec-sub {
        margin-right: 20px;
        float: left;
    }
    .controls span{
        margin-right: 10px;
    }
    .ueditorbox{
        height: auto;
    }
    .ueditorbox .ued
    {
        width: 800px;
        height: 500px;
        overflow-y:scroll;
        margin-bottom: 200px;
    }
    .delchildbox {
        color: red;
        cursor: pointer;
    }
    .inputboxlist{
        width: 100px;
    }
    .addhybtn
    {
        display: inline-block;padding: 4px 12px;margin-bottom: 0;font-size: 14px;
        line-height: 20px;color: #333;text-align: center;
        text-shadow: 0 1px 1px rgba(255,255,255,0.75);    vertical-align: middle;
        cursor: pointer;
        background-color: #2fa4e7;
        color: #FFF;
        margin-bottom: 1px;
    }
    .delchildbox {
        color: red;
        cursor: pointer;
    }
    #photos li{ float: left; margin: 10px;}
    .left,.right{
        margin-left: 10%;font-size: 14px;font-weight: normal;line-height: 20px;
    }
    .imglist{
        width: 160px;
        margin-left: 10px;
    }
    table{
        font-size: 14px;font-weight: normal;line-height: 20px;
        margin-left: 80px;
    }
    table td
    {
        height: 50px;
        padding-left: 10px;
        color: #333;
        font-size: 14px;font-weight: normal;line-height: 20px;
    }
    .textTit{
        text-align: right;
        padding-left: 200px;
    }
   .control-group  .fristtd{
        text-align: right;
    }
    .control-group .avatar{
        width: 80px;height: 80px;border-radius: 50%;
    }
    .rzxx .imglists
    {
        width: 150px;
        float: left;
        margin-left: 10px;
        padding-bottom: 20px;
    }
    .control-group .djopt{
        color: #0b6cbc;
        cursor: pointer;
    }
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li ><a href="javascript:history.back()">医生列表</a></li>
        <li class="active"><a>医生详情</a></li>
    </ul>
    <form  class="form-horizontal"  method="post" @submit.prevent="submit_form">
        <h1>医生详情</h1>
        <div class="control-group" >
            <table>
                <tr><td class="fristtd">头像：</td><td><img class="avatar" src="<?php echo ($info["avatar"]); ?>" alt=""></td>
                              <td class="textTit">状态:</td><td><span class="statusText"><?php echo ($info["statusText"]); ?></span>  <a class="djopt" data-status="<?php echo ($info["status"]); ?>" data-id="<?php echo ($info["pid"]); ?>">冻结/解冻</a></td> </tr>
                <tr><td class="fristtd">昵称：</td><td><?php echo ($info["nickname"]); ?></td > <td  class="textTit">手机号:</td> <td><?php echo ($info["mobile"]); ?></td> </tr>
                <tr><td class="fristtd">首次登录时间：</td><td><?php echo ($info["create_time"]); ?></td> <td  class="textTit">最后登录时间:</td> <td><?php echo ($info["last_login_time"]); ?></td> </tr>
                <tr><td class="fristtd">省市：</td><td><?php echo ($info["prov"]); ?></td> <td  class="textTit">专业:</td> <td><?php echo ($info["zy"]); ?></td> </tr>
                <tr><td class="fristtd">粉丝：</td><td><?php echo ($info["fss"]); ?></td> <td  class="textTit">关注:</td> <td><?php echo ($info["gzs"]); ?></td> </tr>
                <tr><td class="fristtd">文章：</td><td><?php echo ($info["wzs"]); ?></td> <td  class="textTit">动态:</td> <td><?php echo ($info["dts"]); ?></td> </tr>
                <tr><td class="fristtd">开启咨询：</td><td><?php echo ($info["iszx"]); ?></td></tr>
                <tr><td class="fristtd">简介：</td><td><?php echo ($info["grjs"]); ?></td></tr>
            </table>
        </div>

        <h1>认证信息</h1>

        <div class="control-group" >
            <table class="rzxx">
                <tr><td class="fristtd">姓名：</td><td><?php echo ($info["truename"]); ?></td> <td  class="textTit">医院:</td> <td><?php echo ($info["hosp"]); ?></td> </tr>
                <tr><td class="fristtd">科室：</td><td><?php echo ($info["ks"]); ?></td> <td  class="textTit">职位:</td> <td><?php echo ($info["zw"]); ?></td> </tr>
                <tr><td class="fristtd">身份证：</td><td>
                    <?php if(is_array($info["sfz"])): $i = 0; $__LIST__ = $info["sfz"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sf): $mod = ($i % 2 );++$i;?><img class="imglists" src="<?php echo ($sf); ?>" alt=""><?php endforeach; endif; else: echo "" ;endif; ?> </td></tr>
                <tr><td class="fristtd">职业资格证：</td><td>
                    <?php if(is_array($info["zyzgz"])): $i = 0; $__LIST__ = $info["zyzgz"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$zyzgz): $mod = ($i % 2 );++$i;?><img class="imglists" src="<?php echo ($zyzgz); ?>" alt=""><?php endforeach; endif; else: echo "" ;endif; ?>
                </td></tr>
                <tr><td class="fristtd">其他备注：</td><td><?php echo ($info["intro"]); ?></td>
                </tr>
                <tr><td colspan="2">
                    <?php if(is_array($info["grjsimg"])): $i = 0; $__LIST__ = $info["grjsimg"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$imgs): $mod = ($i % 2 );++$i;?><img width="100px" height="100px" src="<?php echo ($imgs); ?>" alt=""><?php endforeach; endif; else: echo "" ;endif; ?>
                </td>

                </tr>
            </table>
        </div>
    </form>
</div>
</body>
</html>
<script type="text/javascript" src="/public/js/common.js"></script>
    <script type="text/javascript">
        $(function () {
            $('.djopt').click(function () {
                var id = $(this).attr('data-id');
                var status = $(this).attr('data-status');
                $.ajax({
                    url: "<?php echo U('modifyStatus');?>",
                    type: "POST",
                    data: {id:id,status:status},
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1)
                        {
                            Wind.use('noty', 'noty', function () {
                                var n = noty({
                                    text: data.info,
                                    type: 'success'
                                });
                            });
                            if(status == 1)
                            {
                                $('.djopt').attr('data-status',0);
                                $('.statusText').html('冻结');
                            }else
                            {
                                $('.djopt').attr('data-status',1);
                                $('.statusText').html('正常');
                            }
                        } else {
                            Wind.use('artDialog', function () {
                                art.dialog({
                                    content: data.info,
                                    icon: 'error',
                                    id: 'popup',
                                    lock: true,
                                    time: 2
                                });
                            })
                        }
                    }
                })
            });
        })

    </script>