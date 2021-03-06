<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
	    <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
	    <meta name="format-detection" content="telephone=no,email=no,date=no,address=no">
		<title>城市选择</title>
	    <link rel="stylesheet" href="/public/app/lib/mui/mui.min.css" />
	    <link rel="stylesheet" href="/public/app/css/public.css" />
	    <link rel="stylesheet" href="/public/app/css/style.css" />
	    <script type="text/javascript" src="/public/app/lib/jq/jquery-1.10.2.js" ></script>
	    <script type="text/javascript" src="/public/app/lib/weui/jquery-weui.js" ></script>
	    <script type="text/javascript" src="/public/app/lib/mui/mui.js" ></script>
	    <script type="text/javascript" src="/public/app/js/v.min.js" ></script>
	    <script type="text/javascript" src="/public/app/js/common.js" ></script>
	</head>
	<style>
		.mui-row.mui-fullscreen>[class*="mui-col-"] {
			height: 100%;
		}
		.mui-col-xs-4,
		.mui-control-content {
			overflow-y: auto;
			height: 100%;
		}
		.mui-segmented-control .mui-control-item {
			line-height: 50px;
			width: 100%;
		}
		.mui-segmented-control.mui-segmented-control-inverted .mui-control-item.mui-active {
			background-color: #fff;
		}
		.mui-segmented-control.mui-segmented-control-inverted.mui-segmented-control-vertical .mui-control-item,
		 .mui-segmented-control.mui-segmented-control-inverted.mui-segmented-control-vertical .mui-control-item.mui-active{ border-bottom: none; position: relative;}
		.mui-segmented-control.mui-segmented-control-inverted.mui-segmented-control-vertical .mui-control-item:after,
		 .mui-segmented-control.mui-segmented-control-inverted.mui-segmented-control-vertical .mui-control-item.mui-active:after{ position: absolute;left: 10px; bottom: 0; right: 0; height: 1px; background: #f2f2f2; content: "";}
		.mui-segmented-control .mui-control-item{ text-align: left; padding-left: 10px; background: #f6f6f6;}
		.mui-segmented-control .mui-control-item.mui-active{ background: #fff !important;}
		.mui-segmented-control.mui-segmented-control-inverted .mui-control-item.mui-active{ color: #595959;}
		.mui-content{ background: #fff;}
		.mui-table-view:after,.mui-table-view:before{ display: none;}
		.mui-table-view-cell:after{ background-color: #f2f2f2;}
	</style>
	<body class="mui-ios mui-ios-11 mui-ios-11-0">
		<header>
			<div class="back"></div>
			<h5>城市选择</h5>
		</header>
		<section id="app" class="citySec">
			<div class="tit">当前定位城市：<span class="colorThem"><?php echo $_SESSION['current_cityName'];?></span></div>
			<div class="mui-content mui-row mui-fullscreen" style="margin-top: 86px;">
				<div class="mui-col-xs-4" style="background: #f6f6f6;"> 
					<div id="segmentedControls" class="mui-segmented-control mui-segmented-control-inverted mui-segmented-control-vertical">
						<a class="mui-control-item" :class="prop.name==current_provinceName?'mui-active':''" :href="prop.ids" v-for="prop,index in sheng">{{prop.name}}</a>
					</div>
				</div>
				<div id="segmentedControlContents" class="mui-col-xs-8">
					<div :id="prop.ids" class="mui-control-content" :class="prop.name==current_provinceName?'mui-active':''" v-for="prop,index in citys">
						<ul class="mui-table-view">
							<li class="mui-table-view-cell" v-for="item in prop.arr">{{item.name}}</li>
						</ul>
					</div>
				</div>
			</div>
		</section>
	</body>
</html>
<script>

	var app = new Vue({
		el:'#app',
		data:{
			sheng:[],   //省
			citys:[],    //市
			current_provinceName:'<?php echo ($current_provinceName); ?>',
			current_cityName:'<?php echo ($current_cityName); ?>'
		},
		methods:{			
		}
	})
	
	$(function(){
		$.ajax({
		   url: "<?php echo U('Mobile/Doctor/getprocitylist');?>",//json文件位置
		   type: "post",//请求方式为get
		   dataType: "json", //返回数据格式为json
		   success: function(data) { 
		       app.sheng =data.province;
		       app.citys =data.citystr;
		       console.log(JSON.stringify(data));
		   }
		})
	})
</script>
<script>
	mui.init({
		swipeBack: true //启用右滑关闭功能
	});
</script>