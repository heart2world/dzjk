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
        display:none;position: fixed;  top: 15%;border-radius: 3px;  left: 28%; width: 30%; overflow:hidden; overflow-y: auto;  padding: 8px;  border: 1px solid #E8E9F7;  background-color: white;  z-index:1980;
    }
    .row-fluid2{
        display:none;position: fixed;  top: 15%;border-radius: 3px;  left: 28%; width: 30%; overflow:hidden; overflow-y: auto;  padding: 8px;  border: 1px solid #E8E9F7;  background-color: white;  z-index:1980;
    }
    #bg{ display: none;  position: fixed;  top: 0%;  left: 0%;  width: 100%;  height: 100%;  background-color: black;  z-index:1001;  -moz-opacity: 0.7;  opacity:.70;  filter: alpha(opacity=70);}
    #addmessage{text-align:center;color:red;}
    #editmessage{text-align:center;color:red;}
  
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('Report/index');?>">报告管理</a></li>
    </ul>
    <form class="well form-search" method="post" action="<?php echo U('Report/index');?>">    
        报告名：
        <input type="text" name="reportname" style="width: 110px;"  placeholder="" value="<?php echo ((isset($formget["reportname"]) && ($formget["reportname"] !== ""))?($formget["reportname"]):''); ?>">
        检查机构名：
        <input type="text" name="companyname" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["companyname"]) && ($formget["companyname"] !== ""))?($formget["companyname"]):''); ?>">
       用户手机号：
        <input type="text" name="mobile" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["mobile"]) && ($formget["mobile"] !== ""))?($formget["mobile"]):''); ?>">
        用户昵称：
        <input type="text" name="nicename" style="width: 100px;"  placeholder="" value="<?php echo ((isset($formget["nicename"]) && ($formget["nicename"] !== ""))?($formget["nicename"]):''); ?>">
        上传人身份：
        <select name="type" style="width: 80px;">
            <option value="0">全部</option>
            <option value="1" <?php if($formget['type'] == 1): ?>selected<?php endif; ?>>平台</option>
            <option value="2" <?php if($formget['type'] == 2): ?>selected<?php endif; ?>>检查机构</option>
            <option value="3" <?php if($formget['type'] == 3): ?>selected<?php endif; ?>>用户</option>
        </select><br/><br/>
        报告状态：
        <select name="status" style="width: 80px;">
            <option value="0">全部</option>
            <option value="1" <?php if($formget['status'] == 1): ?>selected<?php endif; ?>>前端展示中</option>
            <option value="2" <?php if($formget['status'] == 2): ?>selected<?php endif; ?>>前端已删除</option>
        </select>
        检查时间：
        <input type="text" name="st_time" autocomplete="off" placeholder="开始时间" style="width: 90px;" value="<?php echo ((isset($formget["st_time"]) && ($formget["st_time"] !== ""))?($formget["st_time"]):''); ?>" class="input js-date date">-
        <input type="text" name="ed_time" autocomplete="off" placeholder="结束时间" style="width: 90px;" value="<?php echo ((isset($formget["ed_time"]) && ($formget["ed_time"] !== ""))?($formget["ed_time"]):''); ?>" class="input js-date date">&nbsp;
       报告上传时间：
        <input type="text" name="crst_time" autocomplete="off" placeholder="开始时间" style="width: 90px;" value="<?php echo ((isset($formget["crst_time"]) && ($formget["crst_time"] !== ""))?($formget["crst_time"]):''); ?>" class="input js-date date">-
        <input type="text" name="cred_time" autocomplete="off" placeholder="结束时间" style="width: 90px;" value="<?php echo ((isset($formget["cred_time"]) && ($formget["cred_time"] !== ""))?($formget["cred_time"]):''); ?>" class="input js-date date">&nbsp;
        <input type="submit" class="btn btn-primary" value="筛选"><br/>
    </form>
    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <td style="text-align: left" >
                <a class="btn" href="javascript:;" onclick="show_add();">上传报告</a>
                <a class="btn" href="javascript:;" onclick="show_pitch();">批量上传</a>
            </td>
        </tr>
        </thead>
    </table>
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">           
            <th>报告名</th>
            <th>检测机构名</th>
            <th>上传人身份</th>
            <th>用户手机号</th>
            <th>用户昵称</th>
            <th>检查时间</th>
            <th>报告上传时间</th>
            <th>报告状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>               
                <td><?php echo ($val["reportname"]); ?></td>
                <td><?php echo ($val["companyname"]); ?></td>
                <td><?php echo ($val["typename"]); ?></td>
                <td><?php echo ($val["mobile"]); ?></td>
                <td><?php echo ($val["nicename"]); ?></td>
                <td><?php echo (date('Y-m-d',$val["checktime"])); ?></td>
                 <td><?php echo (date('Y-m-d H:i:s',$val["createtime"])); ?></td>
                <td><?php if($val['status'] == 1): ?>前端展示中<?php else: ?>前端已删除<?php endif; ?></td>
                <td>
                    <?php if($val['file_hz'] == 1): ?><a class="btn" href="<?php echo U('Report/download',array('id'=>$val['id']));?>">下载报告文件</a><?php endif; ?>
                    <a class="btn" href="javascript:;" onclick="delitem('<?php echo ($val["id"]); ?>')">删除</a>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>
        <div class="pagination" style="float: right;"><?php echo ($page); ?></div>
    </table>
    <div class="control-group">
        <div class="row-fluid" id="company_add" style="display: none">
            <form id="tagforms" method="post" enctype="multipart/form-data">
            <fieldset class="form-horizontal">
                <div class="control-group" style="margin-top: 50px;">
                    <label class="control-label" style="width: 25%;"><span style="color:red;">*</span>报告名：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="reportname" value="" maxlength='200' id="reportname" placeholder=""/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;"><span style="color:red;">*</span>用户手机号：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="mobile" value="" maxlength='11' id="mobile" placeholder=""/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;"><span style="color:red;">*</span>检查时间：</label>
                    <div class="controls" style="margin-left: 50px;">
                        <input type="text" name="checktime" value="" id="checktime" autocomplete="off" class="js-date"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" style="width: 25%;"></label>
                    <div class="controls" style="margin-left: 50px;">
                        <ul id="grjsimg" class="pic-list unstyled">
                        </ul>
                        <ul id="filelist" class="pic-list unstyled">
                        </ul>
                        <input type="hidden" name="fileurl" id="fileurlstr">
                        <input type="hidden" name="filenamestr" id="filenamestr">
                        <input type="hidden" name="file_hz" id="file_hz">
                        <a href="javascript:;" style="margin-top: 25px;margin-left: 5px;" id="photos_upload_btn_grjsimg" class="btn btn-small">选择图片</a>
                        <a href="javascript:;" style="margin-top: 25px;margin-left: 5px;" id="photos_upload_btn_file" class="btn btn-small">上传文件</a>
                    </div>
                </div>
            </fieldset>
            <div id="addmessage"></div>
            <div style="height: 30px;border-bottom: 1px solid #ccc;"></div>
            <div style="text-align: center;margin-top: 10px;">
                <a href="javascript:;" class="btn btn-primary" onclick="close_div()">取消</a>&nbsp;&nbsp;&nbsp;
                <a href="javascript:;" class="btn btn-primary" onclick="add_post()">确认</a>
            </div>
            </form>
        </div>

        <div class="row-fluid2" id="reportpitch" style="display: none">
             <form id="tagforms2" method="post" enctype="multipart/form-data">
                <fieldset class="form-horizontal">
                    <div class="control-group" style="margin-top: 50px;">
                        <label class="control-label" style="width: 25%;"><span style="color:red;">*</span>报告名：</label>
                        <div class="controls" style="margin-left: 50px;">
                            <input type="text" name="reportname2" value="" maxlength='200' id="reportname2" placeholder=""/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" style="width: 25%;"><span style="color:red;">*</span>检查时间：</label>
                        <div class="controls" style="margin-left: 50px;">
                            <input type="text" name="checktime2" value="" id="checktime2" autocomplete="off" class="js-date"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" style="width: 25%;"></label>
                        <div class="controls" style="margin-left: 50px;">
                            <ul id="filelist2" class="pic-list unstyled">
                            </ul>
                            <input type="hidden" name="fileurlstr2" id="fileurlstr2">  
                            <input type="hidden" name="filenamestr2" id="filenamestr2">
                        </div>
                    </div>
                </fieldset>
                <div id="editmessage"></div>
                <div style="height: 30px;border-bottom: 1px solid #ccc;"></div>
                <div style="text-align: center;margin-top: 10px;">
                    <a href="javascript:;" class="btn btn-primary" onclick="close_div()">取消</a>&nbsp;&nbsp;&nbsp;
                    <a href="javascript:;" class="btn btn-primary" onclick="pitch_post()">确认</a>
                </div>
            </form>
        </div> 
    </div>
</div>
<div id="bg" onclick="close_div()"></div>
<script src="/public/js/common.js"></script>
<script src="/public/js/artDialog/artDialog.js"></script>
<script src="/public/js/content_addtop.js"></script>
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
        $("#photos_upload_btn_grjsimg").show();
        $("#photos_upload_btn_file").show();
        $("#bg").css('display','block');
        $('#company_add').css('display','block');
    }
    function show_pitch()
    {
        $("#fileurlstr2").val('');
        $("#filenamestr2").val('');
        var args = "20,pdf,0";
        flashupload('albums_images', '文件上传', 'photos', change_images6, args, '', '', '','file')
    }
    function change_images4(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            var contents = in_content.split('|');
            console.log(in_content);
            var filenames = in_filename.split('|');
            $('#' + returnid + '_tips').css('display', 'none');
            if (contents == '') return true;
            $("#fileurlstr").val(in_content);
            $("#file_hz").val('0');    
            for(i in contents)
            {
                var html='<li style="float: left;margin-left: 10px;">\
                                <img src="'+contents[i]+'"  class="ttt"  style="cursor: hand;width: 90px;height: 90px;margin-bottom:3px;/>\
                                <input type="hidden" name="fileurl[]" value="'+contents[i]+'">\
                            </li>';
                $("#grjsimg").append(html);
            }
    }
    // 多图上传
    $(function (){
        $("#photos_upload_btn_grjsimg").click(function () {
            $("#photos_upload_btn_file").hide();
            var num= $("#grjsimg li").length;
            if(num>=9)
            {
                Wind.use('artDialog', function () {
                    art.dialog({
                        content: '最多只能添加九张',
                        icon: 'error',
                        id: 'popup',
                        lock: true,
                        time: 2
                    });
                })
                return false;
            }
            var ready_photos_num = 9- $("#grjsimg li").length;
            var args = ready_photos_num + ",gif|jpg|jpeg|png|bmp,0";
            flashupload('albums_images', '图片上传', 'photos', change_images4, args, '', '', '','image')
        });
        $("#photos_upload_btn_file").click(function () {
            $("#photos_upload_btn_grjsimg").hide();
            var num= $("#filelist li").length;
            if(num>=1)
            {
                Wind.use('artDialog', function () {
                    art.dialog({
                        content: '最多只能添加一个文件',
                        icon: 'error',
                        id: 'popup',
                        lock: true,
                        time: 2
                    });
                })
                return false;
            }
            var ready_photos_num = 1- $("#filelist li").length;
            var args = ready_photos_num + ",pdf,0";
            flashupload('albums_images', '文件上传', 'photos', change_images5, args, '', '', '','file')
        });
    })
    // 单个文件回调函数
    function change_images5(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            
            $('#' + returnid + '_tips').css('display', 'none');
            if (in_content == '') return true;  
            $("#fileurlstr").val(in_content); 
            $("#filenamestr").val(in_filename); 
            $("#file_hz").val('1');        
            var html='<li style="float: left;margin-left: 10px;">\
                            <img src="/data/upload/pdf.png" style="width:90px;">\
                            <span style="position: absolute;">'+in_filename+'</span>\
                            <input type="hidden" name="fileurl[]" value="'+in_content+'">\
                        </li>';
            $("#filelist").append(html);
            
    }
    // 多文件回调函数
    function change_images6(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            var contents = in_content.split('|');
            console.log(in_content);
            var filenames = in_filename.split('|');
            $('#' + returnid + '_tips').css('display', 'none');
            if (contents == '') return true;
            $("#bg").css('display','block');
            $('#reportpitch').css('display','block');
            // 赋值
            $("#fileurlstr2").val(in_content); 
            $("#filenamestr2").val(filenames); 
           
            for(i in contents)
            {
                var html='<li style="float: left;margin-left: 10px;">\
                            <img src="/data/upload/pdf.png" style="width:90px;">\
                            <span style="position: absolute;">'+filenames[i]+'</span>\
                            <input type="hidden" name="fileurl[]" value="'+contents[i]+'">\
                          </li>';
                $("#filelist2").append(html);
            }
    }
    function add_post()
    {
        if(istap==1){return};
        var reportname =$("#reportname").val();
        var mobile =$("#mobile").val();
        var checktime =$("#checktime").val();
        var fileimg =$("#grjsimg li").length;
        var filestr =$("#filelist li").length;
        console.log(reportname);
        if(reportname =='' || mobile=='' || checktime=='' || (fileimg==0 && filestr ==0))
        {
            $("#addmessage").html('请完善必填信息');
            istap=1;
            setTimeout(function(){
                $("#addmessage").html('');
                istap=0;
            },2000);
        }else{
            var tagvals=$('#tagforms').serialize();     
            $.ajax({
                url:"<?php echo U('Report/add_post');?>",
                data:tagvals,
                type:'POST',
                success:function(data)
                {
                    if(data.status == 0)
                    {
                        location.href="<?php echo U('Report/index');?>";
                    }else{
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: data.msg, time: 2});
                    }
                }
            })
        }
    }
    function pitch_post()
    {
        if(istap==1){return};
        var reportname =$("#reportname2").val();
        var checktime =$("#checktime2").val();
        console.log(reportname);
        if(reportname =='' || checktime=='')
        {
            $("#editmessage").html('请完善必填信息');
            istap=1;
            setTimeout(function(){
                $("#editmessage").html('');
                istap=0;
            },2000);
        }else{
            var tagvals=$('#tagforms2').serialize();     
            $.ajax({
                url:"<?php echo U('Report/pitch_post');?>",
                data:tagvals,
                type:'POST',
                success:function(data)
                {
                    if(data.status == 0)
                    {
                        location.href="<?php echo U('Report/index');?>";
                    }else{
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: data.msg, time: 2});
                    }
                }
            })
        }
    }
    // 删除
    function delitem(id)
    {
        $.dialog({id: 'popup', lock: true,icon:"question", content: '是否确认删除该报告？',cancel: true, ok: function () {                
            $.ajax({
                url: "<?php echo U('Report/delete');?>",
                type: 'POST',
                data: {id:id},
                success:function (res) {
                    if(res.status == 0){  
                        
                        location.href='<?php echo U("Report/index");?>';                        
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