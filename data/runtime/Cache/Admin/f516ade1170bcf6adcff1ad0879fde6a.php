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
        display:none;position: fixed;  top: 20%;border-radius: 3px;  left: 28%; width: 30%; overflow:hidden; overflow-y: auto;  padding: 8px;  border: 1px solid #E8E9F7;  background-color: white;  z-index:10003;
    }
    .row-fluid2{
        display:none;position: fixed;  top: 20%;border-radius: 3px;  left: 28%; width: 30%; overflow:hidden; overflow-y: auto;  padding: 8px;  border: 1px solid #E8E9F7;  background-color: white;  z-index:10003;
    }
    #bg{ display: none;  position: fixed;  top: 0%;  left: 0%;  width: 100%;  height: 100%;  background-color: black;  z-index:1001;  -moz-opacity: 0.7;  opacity:.70;  filter: alpha(opacity=70);}
    #addmessage{text-align:center;color:red;}
    #editmessage{text-align:center;color:red;}
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('Company/index');?>">检测公司管理</a></li>
    </ul>
    <form class="well form-search" method="post" action="<?php echo U('Company/index');?>">    
        公司账号：
        <input type="text" name="userlogin" style="width: 120px;"  placeholder="公司账号" value="<?php echo ((isset($formget["userlogin"]) && ($formget["userlogin"] !== ""))?($formget["userlogin"]):''); ?>">&nbsp;&nbsp;
        公司名称：
        <input type="text" name="companyname" style="width: 120px;"  placeholder="公司名称" value="<?php echo ((isset($formget["companyname"]) && ($formget["companyname"] !== ""))?($formget["companyname"]):''); ?>">&nbsp;&nbsp;
        联系人：
        <input type="text" name="linkname" style="width: 120px;"  placeholder="联系人" value="<?php echo ((isset($formget["linkname"]) && ($formget["linkname"] !== ""))?($formget["linkname"]):''); ?>">&nbsp;&nbsp;
        联系电话：
        <input type="text" name="mobile" style="width: 100px;"  placeholder="联系电话" value="<?php echo ((isset($formget["mobile"]) && ($formget["mobile"] !== ""))?($formget["mobile"]):''); ?>">&nbsp;&nbsp;        
        <input type="submit" class="btn btn-primary" value="筛选"><br/>
    </form>
    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <td style="text-align: left" >
                <a class="btn" href="javascript:;" onclick="show_add();">添加检查机构</a>
            </td>
        </tr>
        </thead>
    </table>
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">           
            <th>公司账号</th>
            <th>公司名称</th>
            <th>联系人</th>
            <th>联系电话</th>
            <th>添加时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
            <?php if(is_array($company)): $i = 0; $__LIST__ = $company;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>               
                <td><?php echo ($val["userlogin"]); ?></td>
                <td><?php echo ($val["companyname"]); ?></td>
                <td><?php echo ($val["linkname"]); ?></td>
                <td><?php echo ($val["mobile"]); ?></td>
                <td><?php echo (date('Y-m-d H:i:s',$val["createtime"])); ?></td>
                <td>
                    <a class="btn btn-small btn-primary" href="javascript:;" onclick="show_edit('<?php echo ($val["id"]); ?>')">编辑</a>
                    <?php if($val['status'] == 1): ?><a href="javascript:void(0);" onclick="changestatus('<?php echo ($val["id"]); ?>',0)" class="btn btn-small" style="background: red;color:white;">解冻</a>
                    <?php else: ?>
                    <a href="javascript:void(0);" onclick="changestatus('<?php echo ($val["id"]); ?>',1)"  class="btn btn-small" style="background: red;color:white;">冻结</a><?php endif; ?>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
        <div class="pagination"><?php echo ($page); ?></div>
    </table>
    <div class="control-group">
        <div class="row-fluid" id="company_add" style="display: none">
            <fieldset class="form-horizontal">
                <div class="control-group" style="margin-top: 50px;">
                    <label class="control-label" style="width: 25%;">公司名称：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="companyname" value="" maxlength='20' id="companyname" placeholder="不超过二十个汉字"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">联系人：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" type="text" name="linkname" value="" maxlength='5' id="linkname" placeholder="最多五个汉字"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">联系电话：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="mobile" value="" maxlength='11' id="mobile" placeholder="最多十一位" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">公司账号：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="userlogin" value="" id="userlogin" maxlength='30' placeholder="字母与数字组合"/>
                    </div>
                </div>
                <div class="control-group" >
                    <label class="control-label" style="width: 25%;">密码：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="password" name="userpass" value="" id="userpass" maxlength='30' placeholder=""/>
                    </div>
                </div>
                <div class="control-group" style="margin-bottom: 10px;">
                    <label class="control-label" style="width: 25%;">确认密码：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="password" name="userpass2" value="" id="userpass2" maxlength='30' placeholder=""/>
                    </div>
                </div>         
            </fieldset>
            <div id="addmessage" style="display:none;"></div>
            <div style="height: 30px;border-bottom: 1px solid #ccc;"></div>
            <div style="text-align: center;margin-top: 10px;">
                <a href="javascript:;" class="btn btn-primary" onclick="close_div()">取消</a>&nbsp;&nbsp;&nbsp;
                <a href="javascript:;" class="btn btn-primary" onclick="add_post()">确认</a>
            </div>
            <div class="row" id="page-info">
            </div>
        </div>

        <div class="row-fluid2" id="company_edit" style="display: none">
            <fieldset class="form-horizontal">
                <div class="control-group" style="margin-top: 50px;">
                    <label class="control-label" style="width: 25%;">公司名称：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="companyname" value="" maxlength='20' id="edit_companyname" placeholder="不超过二十个汉字"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">联系人：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" type="text" name="linkname" value="" maxlength='5' id="edit_linkname" placeholder="最多五个汉字"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">联系电话：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="mobile" value="" maxlength='11' id="edit_mobile" placeholder="最多十一位" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;">公司账号：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="userlogin" value="" id="edit_userlogin" maxlength='30' placeholder="字母与数字组合"/>

                    </div>
                </div>
                <div class="control-group" >
                    <label class="control-label" style="width: 25%;">密码：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="password" name="userpass" value="" id="edit_userpass" maxlength='30' placeholder="******"/>
                    </div>
                </div>
                <div class="control-group" style="margin-bottom: 10px;">
                    <label class="control-label" style="width: 25%;">确认密码：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="password" name="userpass2" value="" id="edit_userpass2" maxlength='30' placeholder="******"/>
                    </div>
                </div>         
            </fieldset>
            <input type="hidden" id="edit_company_id" value="">
            <input type="hidden" id='edit_olduserlogin' value="">
            <div id="editmessage" style="display:none;"></div>
            <div style="height: 30px;border-bottom: 1px solid #ccc;"></div>
            <div style="text-align: center;margin-top: 10px;">
                <a href="javascript:;" class="btn btn-primary" onclick="close_div()">取消</a>&nbsp;&nbsp;&nbsp;
                <a href="javascript:;" class="btn btn-primary" onclick="edit_post()">确认</a>
            </div>
        </div> 
    </div>
</div>
<div id="bg" onclick="close_div()"></div>
<script src="/public/js/common.js"></script>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>
<script src="/public/js/artDialog/artDialog.js"></script>
<script type="text/javascript">
    var istap = 0;
    function close_div() {      
        $("#addmessage").hide();
        $("#editmessage").hide();  
        $('.row-fluid').css('display','none');
        $('.row-fluid2').css('display','none');
        $('#bg').css('display','none');
    }
    function show_add() {
        $("#companyname").val('');
        $("#linkname").val('');
        $("#mobile").val('');
        $("#userlogin").val('');
        $("#userpass").val('');
        $("#userpass2").val('');
        $("#bg").css('display','block');
        $('#company_add').css('display','block');
    }
    function add_post()
    {
        if(istap==1){return;}
        var companyname = $('#companyname').val();        
        var linkname = $('#linkname').val();
        var mobile = $('#mobile').val();
        var userlogin = $('#userlogin').val();
        var userpass = $('#userpass').val();
        var userpass2 = $('#userpass2').val();
        if(companyname == '' || userlogin=='' || userpass=='' || linkname=='' || mobile=='' || userpass2=='')
        {           
            $("#addmessage").show();
            $("#addmessage").html('请输入完整信息');
            istap=1;
            setTimeout(function(){
                $("#addmessage").hide();
                istap=0;
            },2000)
        }else if(userpass !=userpass2)
        {
            $("#addmessage").show();
            $("#addmessage").html('密码不一致，请重新输入');
            istap=1;
            setTimeout(function(){
                $("#addmessage").hide();
                istap=0;
            },2000)
        }
        else{
            $.ajax({
                url: "<?php echo U('Company/add_post');?>",
                type: 'POST',
                data: {companyname:companyname,userlogin:userlogin,userpass:userpass,linkname:linkname,mobile:mobile},
                dataType:"json",
                success:function (res) {
                    if(res.status == 0){ 
                        close_div();
                        location.href='<?php echo U("Company/index");?>';                    
                    } else {
                        $("#addmessage").show();
                        $("#addmessage").html(res.msg);
                        istap=1;
                        setTimeout(function(){
                            $("#addmessage").hide();
                            istap=0;
                        },2000)
                    }
                }
            });
        }        
    }
    function show_edit(id) {
        $.ajax({
            url: "<?php echo U('Company/edit');?>",
            type: 'POST',
            data: {id:id},
            dataType:"json",
            success:function (res) {
                if(res.status == 0){                   
                    $("#bg").css('display','block');
                    $('#company_edit').css('display','block');
                    document.getElementById('edit_companyname').value = res.data.companyname;
                    document.getElementById('edit_linkname').value = res.data.linkname;
                    document.getElementById('edit_mobile').value = res.data.mobile;
                    document.getElementById('edit_userlogin').value = res.data.userlogin;
                    document.getElementById('edit_olduserlogin').value = res.data.userlogin;
                    document.getElementById('edit_company_id').value = res.data.id;
                } else {
                    $.dialog({id: 'popup', lock: true,icon:"warning", content: res.msg, time: 2000});
                }
            }
        });
    }
    function edit_post()
    {
        if(istap==1){return;}
        var companyname = $('#edit_companyname').val();        
        var linkname = $('#edit_linkname').val();
        var mobile = $('#edit_mobile').val();
        var userlogin = $('#edit_userlogin').val();
        var userpass = $('#edit_userpass').val();
        var userpass2 = $('#edit_userpass2').val();
        var company_id =$("#edit_company_id").val();
        var olduserlogin =$("#edit_olduserlogin").val();
        if(companyname == '' || userlogin=='' || linkname=='' || mobile=='')
        {           
            $("#editmessage").show();
            $("#editmessage").html('请输入完整信息');
            istap=1;
            setTimeout(function(){
                $("#addmessage").hide();
                istap=0;
            },2000)
        }
        else if(userpass !='' && userpass2 !='')
        {
            if(userpass !=userpass2)
            {
                $("#editmessage").show();
                $("#editmessage").html('密码不一致，请重新输入');
                istap=1;
                setTimeout(function(){
                    $("#editmessage").hide();
                    istap=0;
                },2000)
            } else{
                $.ajax({
                url: "<?php echo U('Company/edit_post');?>",
                type: 'POST',
                data: {id:company_id,companyname:companyname,userlogin:userlogin,olduserlogin:olduserlogin,userpass:userpass,linkname:linkname,mobile:mobile},
                dataType:"json",
                success:function (res) {
                    if(res.status == 0){ 
                        close_div();
                        location.href='<?php echo U("Company/index");?>';                    
                    } else {
                        $("#editmessage").show();
                        $("#editmessage").html(res.msg);
                        istap=1;
                        setTimeout(function(){
                            $("#editmessage").hide();
                            istap=0;
                        },2000)
                    }
                }
            });
            }           
        }
        else
        {
            $.ajax({
                url: "<?php echo U('Company/edit_post');?>",
                type: 'POST',
                data: {id:company_id,companyname:companyname,userlogin:userlogin,olduserlogin:olduserlogin,userpass:userpass,linkname:linkname,mobile:mobile},
                dataType:"json",
                success:function (res) {
                    if(res.status == 0){ 
                        close_div();
                        location.href='<?php echo U("Company/index");?>';                    
                    } else {
                        $("#editmessage").show();
                        $("#editmessage").html(res.msg);
                        istap=1;
                        setTimeout(function(){
                            $("#editmessage").hide();
                            istap=0;
                        },2000)
                    }
                }
            });
        }        
    }
    function changestatus(id,status){
        if(status == 1)
        {
            var content ='是否确认冻结该公司？';
        }else{
            var content ='是否确认解冻该公司？';
        }
        $.dialog({id: 'popup', lock: true,icon:"question", content: content,cancel: true, ok: function () {                
            $.ajax({
                url: "<?php echo U('Company/changestatus');?>",
                type: 'POST',
                data: {id:id,status:status},
                success:function (res) {
                    if(res.status == 0){  
                        
                        location.href='<?php echo U("Company/index");?>';                        
                    } else {
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: res.msg, time: 2});
                    }
                }
            });
        }})
    }
</script>
</body>
</html>