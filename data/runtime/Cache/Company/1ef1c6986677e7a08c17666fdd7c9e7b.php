<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh_CN" style="overflow: hidden;">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
	<meta charset="utf-8">
	<title> 番茄医学后台</title>
	<meta name="description" content="This is page-header (.page-header &gt; h1)">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="/public/simpleboot/themes/flat/theme.min.css" rel="stylesheet">
	<link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
	<link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
	<link rel="stylesheet" href="/public/simpleboot/themes/flat/simplebootadminindex.min.css?">
	<!--[if lte IE 8]>
	<link rel="stylesheet" href="/public/simpleboot/css/simplebootadminindex-ie.css?" />
	<![endif]-->
	<style>
		.navbar .nav_shortcuts .btn{margin-top: 5px;}
		.macro-component-tabitem{width:101px;}
		/*-----------------导航hack--------------------*/
		.nav-list>li.open{position: relative;}
		.nav-list>li.open .back {display: none;}
		.nav-list>li.open .normal {display: inline-block !important;}
		.nav-list>li.open a {padding-left: 7px;}
		.nav-list>li .submenu>li>a {background: #fff;}
		.nav-list>li .submenu>li a>[class*="fa-"]:first-child{left:20px;}
		.nav-list>li ul.submenu ul.submenu>li a>[class*="fa-"]:first-child{left:30px;}
		/*----------------导航hack--------------------*/
		.mgt-10 {
			margin-bottom: 10px
		}
	</style>
	<script>
        //全局变量
        var GV = {
            HOST:"<?php echo ($_SERVER['HTTP_HOST']); ?>",
            DIMAUB: "",
            JS_ROOT: "/public/js/",
            TOKEN: ""
        };
	</script>
	<?php if(APP_DEBUG): ?><style>
			#think_page_trace_open{left: 0 !important;
				right: initial !important;}
		</style><?php endif; ?>
</head>
<body style="min-width:900px;" screen_capture_injected="true">
<div id="loading"><i class="loadingicon"></i><span>加载中...</span></div>
<div id="right_tools_wrapper">
	<span id="refresh_wrapper" title="<?php echo L('REFRESH_CURRENT_PAGE');?>" ><i class="fa fa-refresh right_tool_icon"></i></span>
</div>
<div class="navbar">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a href="<?php echo U('index/index');?>" class="brand"> <small>
				<img src="/public/js/assets/images/logo-18.png">
				番茄医学后台
			</small>
			</a>
			<div class="pull-left nav_shortcuts" >
				<?php if(sp_auth_check(sp_get_current_admin_id(),'admin/setting/clearcache')): ?><a class="btn btn-small btn-danger" href="javascript:openapp('<?php echo U('admin/setting/clearcache');?>','index_clearcache','<?php echo L('ADMIN_SETTING_CLEARCACHE');?>');" title="<?php echo L('ADMIN_SETTING_CLEARCACHE');?>">

						<i class="fa fa-trash-o"></i>

					</a><?php endif; ?>
			</div>
			<ul class="nav simplewind-nav pull-right">
				<li class="light-blue">
					<a data-toggle="dropdown" href="#" class="dropdown-toggle">

						<?php if($admin['avatar']): ?><img class="nav-user-photo" width="30" height="30" src="<?php echo sp_get_user_avatar_url($admin['avatar']);?>" alt="<?php echo ($admin["userlogin"]); ?>">

							<?php else: ?>

							<img class="nav-user-photo" width="30" height="30" src="/public/js/assets/images/logo-18.png" alt="<?php echo ($admin["userlogin"]); ?>"><?php endif; ?>

						<span class="user-info">

								欢迎，<?php echo ($admin["userlogin"]); ?>

							</span>

						<i class="fa fa-caret-down"></i>

					</a>

					<ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">

						

						<li><a href="javascript:openapp('<?php echo U('setting/password');?>','index_password','修改密码');"><i class="fa fa-sign-out"></i> 修改密码</a></li>

						<li><a href="<?php echo U('Public/logout');?>"><i class="fa fa-sign-out"></i> <?php echo L('LOGOUT');?></a></li>

					</ul>

				</li>

			</ul>

		</div>

	</div>

</div>



<div class="main-container container-fluid">
	<div class="sidebar" id="sidebar">
		<div id="nav_wraper">
			<ul class="nav nav-list">
				<li>
					<a href="javascript:openapp('/index.php?g=Company&amp;m=Report&amp;a=index&amp;menuid=8756','8756Admin','报告管理',true);">
						<i class="fa fa-file-excel-o"></i>
						<span class="menu-text">报告管理</span>
					</a>	
				</li>
			</ul>
		</div>
	</div>
	<div class="main-content">
		<div class="breadcrumbs" id="breadcrumbs">
			<a id="task-pre" class="task-changebt">←</a>
			<div id="task-content">
				<ul class="macro-component-tab" id="task-content-inner">
					<li class="macro-component-tabitem noclose" app-id="0" app-url="<?php echo U('main/index');?>" app-name="首页">
						<span class="macro-tabs-item-text"><?php echo L('HOME');?></span>
					</li>
				</ul>
				<div style="clear:both;"></div>
			</div>
			<a id="task-next" class="task-changebt">→</a>
		</div>
		<div class="page-content" id="content">
			<iframe src="<?php echo U('Main/index');?>" style="width:100%;height: 100%;" frameborder="0" id="appiframe-0" class="appiframe"></iframe>
		</div>
	</div>
</div>

<input type="hidden" class="bid" value="<?php echo ($admin['id']); ?>">
<script src="/public/js/jquery.js"></script>
<script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
<script>

    var ismenumin = $("#sidebar").hasClass("menu-min");

    $(".nav-list").on( "click",function(event) {

        var closest_a = $(event.target).closest("a");

        if (!closest_a || closest_a.length == 0) {

            return

        }

        if (!closest_a.hasClass("dropdown-toggle")) {

            if (ismenumin && "click" == "tap" && closest_a.get(0).parentNode.parentNode == this) {

                var closest_a_menu_text = closest_a.find(".menu-text").get(0);

                if (event.target != closest_a_menu_text && !$.contains(closest_a_menu_text, event.target)) {

                    return false

                }

            }

            return

        }

        var closest_a_next = closest_a.next().get(0);

        if (!$(closest_a_next).is(":visible")) {

            var closest_ul = $(closest_a_next.parentNode).closest("ul");

            if (ismenumin && closest_ul.hasClass("nav-list")) {

                return

            }

            closest_ul.find("> .open > .submenu").each(function() {

                if (this != closest_a_next && !$(this.parentNode).hasClass("active")) {

                    $(this).slideUp(150).parent().removeClass("open")

                }

            });

        }

        if (ismenumin && $(closest_a_next.parentNode.parentNode).hasClass("nav-list")) {

            return false;

        }

        $(closest_a_next).slideToggle(150).parent().toggleClass("open");

        return false;

    });

</script>

<script src="/public/js/assets/js/index.js"></script>
<script src="/public/assets/js/jquery.gritter.min.js"></script>
<link href="/public/assets/css/jquery.gritter.css" rel="stylesheet" type="text/css">
</body>

</html>