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
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill" >
    <ul class="nav nav-tabs">
        <li ><a href="javascript:history.back()">内容列表</a></li>
        <li class="active"><a><?php if(ACTION_NAME == 'add'): ?>添加<?php else: ?>编辑<?php endif; ?>内容</a></li>
    </ul>
    <form class="form-horizontal  js-ajax-form"  method="post" id="thisForm">

        <div class="control-group">
            <label class="control-label">消息标题：</label>
            <div class="controls">
                <input id='title' type="text" style="width: 320px" maxlength="20" placeholder="20个字以内"  name="title" value="<?php echo ($info["title"]); ?>"  >
                <span class="form-required">*</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">消息内容：</label>
            <div class="controls">
                <textarea type="text/plain" id="content" name="content"><?php echo ($info["content"]); ?></textarea>
            </div>
        </div>


        <div class="control-group" >
            <label class="control-label">选择接收对象：</label>
            <div class="controls">
                <input type="radio" class="sendobj" name="receive_member_type" value="all"   @click="show_user(1)"> 全部(<?php echo ($mcoun); ?>位用户)
                <input type="radio" class="sendobj" name="receive_member_type" value="2" id="receive_member_type" @click="show_user(3)" value="0"  > 医生&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" class="sendobj" name="receive_member_type" value="1"  @click="show_user(3)" > <span style="" >普通用户</span>
            </div>
        </div>

        <div style="margin-left: 11%;border-radius: 4px;border: 1px solid #e1e1e8;padding-top:10px;height: 600px;width:55%; " id="show_users">
            <div class="control-group" >
                <label class="control-label">类型：</label>
                <div class="controls">
                    <select class="select_2" id="type_s" name="cate_id"  style="width:70px">
                        <option value="all">全部</option>
                        <option value="1" >普通用户</option>
                        <option value="2">医生</option>
                    </select>
                </div>
            </div>
            <div class="control-group" >
                <label class="control-label">昵称：</label>
                <div class="controls">
                    <input type="text" name="nickname" style="width: 150px;" placeholder="昵称"  value="" id="nickname">
                    <input type="button" class="btn btn-primary" @click="get_user" value="查找" >
                </div>
            </div>
            <div class="container-fluid">
                <div class="selectbox">
                    <table  class="table table-hover table-bordered table-list">
                        <thead>
                    <tr id="tr">
                        <th style="width: 5%"><input style="margin-top:0px" v-on:click="select()" class="select" type="checkbox">全选</th>
                        <th>类型</th>
                        <th>用户昵称</th>
                    </tr>
                        </thead>
                        <tbody>
                        <tr id="tr2" v-cloak v-for="item in users">
                            <td><input  style="margin-top:0px" :value="item.id" class="checkbox" type="checkbox"></td>
                            <td>{{item.type}}</td>
                            <td>{{item.name}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <vue-pager :conf.sync="pagerConf"></vue-pager>
                </div>

            </div>
        </div>


        <input type="hidden" :value="all_id" name="receive_member_id">

        <div class="form-actions">
            <button  type="button"  @click="tjbtn('')" class="btn btn-primary">保存</button>
            <a  href="javascript:history.back();" class="btn btn-primary">取消</a>
        </div>
        <input id="id" type="hidden" name="id" value="<?php echo ($info["id"]); ?>"  >
    </form>

</div>
<input type="hidden" name="current_select_member_id" value="" id="current_select_member_id">
</body>
</html>

<script src="/public/js/vue.js"></script>
<script type="text/javascript" src="/public/js/content_addtop.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>

<script src="/public/js/common.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>

<script>
    String.prototype.Trim=function(){
        return  this.replace(/^,*|,*$/g,'')
    }
    setTimeout(function(){
        $(".checkbox").click(function(){
            var current_select_member_id=$("#current_select_member_id").val();
            var id=$(this).val();
            if(current_select_member_id.substring(0,1)!=','){
                current_select_member_id=','+current_select_member_id;
            }
            if($(this).is(":checked")){
                if(current_select_member_id.indexOf(","+id+",")>-1){
                }else{
                    current_select_member_id=current_select_member_id+id+',';
                }
            }else{
                current_select_member_id=current_select_member_id.replace(","+id+",",",");
            }
            $("#current_select_member_id").val(current_select_member_id);
        });
    },500);

    var vm = new Vue({
        el:'#seckill',
        data:{
            users:[],
            all_id:[],
            pagerConf:{
                totalPage : 0,
                currPage : 1,
                prevShow : 3,
                nextShow : 3,
                pageRange:[]
            }
        },
        watch:{
            'pagerConf.currPage':function ()
            {
                this.get_user(false);
            }
        },
        methods:{
            get_user:function(search){
                if(search){
                    $("#current_select_member_id").val('');
                }
                var nickname = $('#nickname').val();
                var type_s = $('#type_s').val();
                var p = search == true ? 1 : this.pagerConf.currPage;
                $.getJSON("<?php echo U('get_all_user');?>",{p:p,nickname:nickname,type_s:type_s},function(res){
                    vm.users = res.info.list,
                    vm.pagerConf.totalPage = res.info.total_page;
                    setTimeout(function(){
                        $(".checkbox").prop('checked',false);
                        var select_str=$("#current_select_member_id").val();
                        var select_arr=select_str.split(',');
                        $.each($(".checkbox"),function(j,item){
                            var id=$(item).val();
                            for(var i=0;i<select_arr.length;i++ ){
                                if(id==select_arr[i]){
                                    $(item).prop('checked',true);
                                }
                            }
                        });
                    },200);
                })
            },
            select:function () {
                if($(".select").attr('checked')){
                    $(".checkbox").attr("checked",true);
                    var select_id_str=",";
                    $.each($(".checkbox"),function(i,item){
                        select_id_str=select_id_str+$(item).val()+',';
                    });
                    $("#current_select_member_id").val(select_id_str);
                }else{
                    $(".checkbox").attr("checked",false);
                    $("#current_select_member_id").val('');
                }
            },

            tjbtn:function (id)
            {

                var sendobj = $('input:radio[name="receive_member_type"]:checked').val();


                var type_s = $('#type_s').val();
                if(sendobj == 'all')
                {
                    id=$("#current_select_member_id").val();
                    id=id.Trim(",");
//                    if(id.substring(0,1)==','){
//                        id=id.substring(1,id.length-1);
//                    }
//                    if(id.substring(id.length-1,1)==','){
//                        id=id.substring(0,id.length-1);
//                    }
//                    if (id == '') {
//                        $(".checkbox").each(function () {
//                            if ($(this).prop("checked")) {
//                                id += $(this).val() + ',';
//                            }
//                        });
//                    }
                }
                var title = $('#title').val();
                if(title.length == 0 || title.length > 20)
                {
                    $.dialog({id: 'popup', lock: true, icon: "warning", content: '标题请输入1-20个字', time: 2});
                    return false;
                }
                var contents = content.getContent();

                if(contents.length == 0)
                {
                    $.dialog({id: 'popup', lock: true, icon: "warning", content: '请输入消息内容', time: 2});
                    return false;
                }
                if(!sendobj)
                {
                    $.dialog({id: 'popup', lock: true, icon: "warning", content: '请选择接收对象', time: 2});
                    return false;
                }
                // if('all' == sendobj && !id)
                // {
                //     $.dialog({id: 'popup', lock: true, icon: "warning", content: '请选择接收对象', time: 2});
                //     return false;
                // }

                $.ajax({
                    type: 'POST',
                    url: '/Admin/Cont/sendMsg',
                    data: {title:title,content:contents,sendobj:sendobj,type_s:type_s,id:id},
                    success: function(e)
                    {
                        if(e.status == 1)
                        {
                            Wind.use('noty', 'noty', function () {
                                var n = noty({
                                    text: e.info,
                                    type: 'success'
                                });
                            });
                            setTimeout(function () {
                                location.href = "<?php echo U('index');?>";
                            }, 2000)
                        }else
                        {
                            $.dialog({id: 'popup', lock: true, icon: "warning", content: e.info, time: 2});
                            return false;
                        }
                    }
                });


            },


            show_user:function(type){
                if(type==3){
                    $('#show_users').hide();
                    $('#select2 option').appendTo('#select1');
                    if(vm.all_id.length>0){
                        vm.all_id.splice(0,vm.all_id.length);
                        console.log(vm.all_id)
                    }
                }else {
                    $('#show_users').show()
                }
            }
        },
        created:function()
        {
            this.get_user(true);
            $('#show_users').hide();
        }
    })

    var content = new baidu.editor.ui.Editor(
        {
            initialFrameWidth : 900,
            initialFrameHeight  : 400,
        });
    content.render('content');
    content.ready(function (editor) {
        try {
            content.sync();
        } catch (err) {

        }
    });
</script>
<script type="text/javascript" src="/public/js/common.js"></script>