<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>番茄医学后台管理系统</title>
	<meta name="author" content="DeathGhost" />
	<link rel="stylesheet" type="text/css" href="/public/js/assets/login/css/style.css" />
	<style>
		body{height:100%;background:#16a085;overflow:hidden;}
		canvas{z-index:-1;position:absolute;}
	</style>
	<script>
		if (window.parent !== window.self) {
			document.write = '';
			window.parent.location.href = window.self.location.href;
			setTimeout(function () {
				document.body.innerHTML = '';
			}, 0);
		}
	</script>
</head>
<body>
<dl class="admin_login">
	<dt>
		<strong>番茄医学后台管理系统</strong>
		<em>Management System</em>
	</dt>
	<form method="post" name="login"  autoComplete="off" class="js-ajax-form">
	<dd class="user_icon">
		<input type="text" name="username" placeholder="账号" class="login_txtbx"/>
	</dd>
	<dd class="pwd_icon">
		<input type="password" name="password" placeholder="密码" class="login_txtbx"/>
	</dd>
	<dd class="val_icon">
		<div class="checkcode">
			<input type="text"  name="verify" placeholder="验证码" id="J_codetext"   class="login_txtbx">
		</div>
		<img class="ver_btn" src="/index.php?g=api&m=checkcode&a=index&is_admin=1&length=4&font_size=14&width=108&height=42&use_noise=1&use_curve=0"
			 style="cursor: pointer" title="点击刷新"
			 onclick="this.src='/index.php?g=api&m=checkcode&a=index&is_admin=1&length=4&font_size=14&width=108&height=42&use_noise=1&use_curve=0&time='+Math.random();"/>
		<!--<input type="button" value="验证码" class="ver_btn" onClick="validate();">-->
	</dd>
	<dd>
		<input type="button" value="登录" class="submit_btn" /><br/>
		<!--<input type="button" value="忘记密码" class="btn btn-warning" />-->
	</dd>
	<dd>

		<p onclick="forgetpassword()" style="color: white">忘记密码</p>
		<!--<p>© 2015-2016 jq22 版权所有</p>-->
		<!--<p>陕B2-8998988-1</p>-->
	</dd>
	</form>
</dl>
<script src="/public/js/jquery.js"></script>
<script src="/public/js/assets/login/js/Particleground.js" ></script>
<script src="/public/layer/layer.js"></script>
<script>
	function forgetpassword() {
		layer.open({ content: '请联系平台，电话18502870629', time: 2 });
	}
	$(document).ready(function() {
		//粒子背景特效
		$('body').particleground({
			dotColor: '#5cbdaa',
			lineColor: '#5cbdaa'
		});
		var is_logining=false;
		function submit_login(){
			is_logining=true;
			$.ajax({
				type: 'POST',
				url: "/index.php?g=Company&m=Public&a=dologin",
				dataType: 'json',
				data: $('.js-ajax-form').serialize(),
				success: function (res) {
					is_logining=false;
					if(res.status==1){
						layer.open({ content: res.info, time: 2 });
						setTimeout(function(){
							location.href = "<?php echo U('Index/index');?>";
						},2000);
					}else{
						layer.open({content:res.info,time:2});
					}
				},
			});
		}

		$(".submit_btn").click(function(){
			submit_login();
		});
		$("body").keydown(function() {
			if (event.keyCode == "13") {
				submit_login();
			}
		});
	});
</script>
</body>
</html>