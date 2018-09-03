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
</head>
<body>
<div class="wrap js-check-wrap" id="app">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('index');?>">内容列表</a></li>
        <li  style="cursor: pointer;"><a  href="<?php echo U('add');?>">发布站内信</a></li>
    </ul>
    <form class="well form-search" id="search_form" method="post">
        关键字：
        <input type="text" class="input" v-model="searchCon.keyword" style="width: 200px;" placeholder="关键字">
        发布送时间：
        <input type="text" id="st_time" v-model="searchCon.st_time" class="input js-date date" autocomplete="off" placeholder="开始时间">-
        <input type="text" id="end_time" v-model="searchCon.end_time" class="input js-date date" autocomplete="off" placeholder="结束时间">
        <input type="button" class="btn btn-primary" @click="mySearch(true)" value="搜索">
    </form>
    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <th style="width:100%;text-align: left" >
                <a class="btn btn-small" style="float:left;background:#ff0000; color: white" @click="deleteBatch('')" href="javascript:;">批量删除</a>
            </th>
        </tr>
        </thead>
    </table>
    <table class="table table-hover table-bordered table-list">
        <thead>
        <tr>
            <th style="width: 10%"><input style="margin-top:0px" v-on:click="select()" class="select" type="checkbox">全选</th>
            <th>发送时间</th>
            <th>操作员</th>
            <th>接收数量</th>
            <th>标题</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <tr v-cloak v-for="item in list">
            <td><input style="margin-top:0px" class="checkbox" type="checkbox" :value="item.id"></td>
            <td >{{item.create_time}}</td>
            <td>{{item.admin_name}}</td>
            <td>{{item.reads}}</td>
            <td>{{item.title}}</td>
            <td>
                <a  href="javascript:void(0);" @click="info(item.id)" class="btn btn-small">详情</a>
                <a  href="javascript:void(0);" @click="deleteBatch(item.id)" class="btn btn-small btn-danger">删除</a>
            </td>
        </tr>
        </tbody>
    </table>
    <vue-pager :conf.sync="pagerConf"></vue-pager>

</div>
<script src="/public/js/common.js"></script>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>
<script type="text/javascript">
    Wind.use("artDialog", function () {});
    var app = new Vue({
        el:"#app",
        data:{
            list:[],
            searchCon:{keyword:'',st_time:'',end_time:'',p:1},
            pagerConf:{
                totalPage : 0,
                currPage : 1,
                prevShow : 3,
                nextShow : 3,
                pageRange:[]
            }
        },
        watch:{
            'pagerConf.currPage':function () {
                this.mySearch(false);
            }
        },
        methods:{
            //全选
            select:function () {
                if($(".select").attr('checked')){
                    $(".checkbox").attr("checked",true);
                }else{
                    $(".checkbox").attr("checked",false);
                }
            },

            info:function (id) {
                window.location.href = '/Admin/Cont/info/id/'+id;
            },
            //删除 支持批量
            deleteBatch:function(id){
                if(id==''){
                    $(".checkbox").each(function(){
                        if($(this).prop("checked")){
                            id += $(this).val()+',';
                        }
                    });
                    if(id==''){
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: "请至少选择一条信息", time: 2});
                        return false;
                    }
                }
                var url = "/Admin/Cont/del_log/id/"+id;
                Wind.use('artDialog', function () {
                    art.dialog({
                        title: false,
                        icon: 'question',
                        content: "确定要删除记录吗？",
                        ok: function () {
                            $.getJSON(url,function (res) {
                                if(res.status == 1){
                                    app.mySearch(false);
                                    $.dialog({id: 'popup', lock: true,icon:"succeed", content: res.info, time: 2});
                                }else{
                                    $.dialog({id: 'popup', lock: true,icon:"error", content: res.info, time: 2});
                                }
                            })
                            $(".checkbox").removeAttr("checked");
                        },
                        cancelVal: '关闭',
                        cancel: true
                    });
                })
            },
            mySearch: function (search) {
                var data = this.searchCon;
                data.st_time = $("#st_time").val();
                data.end_time = $("#end_time").val();
                if (data.st_time!='' && data.end_time!='') {
                    if (data.st_time > data.end_time) {
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: "开始时间不能大于结束时间", time: 2});
                        return;
                    }
                }
                data.p = search == true ? 1 : this.pagerConf.currPage;
                $.ajax({
                    url:"<?php echo U('index');?>",
                    data:data,
                    type:"POST",
                    dataType:"json",
                    success: function (res)
                    {
                        if (res.status == 1)
                        {
                            app.list = res.info.list;
                            app.pagerConf.totalPage = res.info.total_page;
                        }
                    },
                    error: function ()
                    {
                        $.dialog({id: 'popup', lock: true,icon:"warning", content: "请求失败,请重试", time: 2});
                    }
                })
            }
        },
        created: function () {
            this.mySearch(true);
        }
    });
    var dateInput = $("input.js-date");
    if (dateInput.length) {
        Wind.use('datePicker', function () {
            dateInput.datePicker();
        });
    }
</script>
</body>
</html>