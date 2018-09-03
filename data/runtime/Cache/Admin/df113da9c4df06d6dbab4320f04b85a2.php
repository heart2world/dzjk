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
<style type="text/css">
    .delbtn{
       color: #FFFFFF;background-image: -webkit-gradient(linear,0 0,0 100%,from(#FF0000),to(#FF0000));
    }
</style>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li class="active"><a >文章列表</a></li>
    </ul>
    <form class="well form-search " >

        <span>发布时间：</span>
        <input type="text" id="st_time" style="width: 130px" name="st_time" class="js-date" autocomplete="off"
               placeholder="开始时间"/>到
        <input type="text" id="end_time" style="width: 130px" name="end_time" class="js-date" autocomplete="off"
               placeholder="结束时间"/>
         推荐状态：
        <select class="select_2" name="cate_id" v-model="search.tj" style="width:70px">
            <option value="a" checked>全部</option>
            <option value="1" >是</option>
            <option value="0">否</option>
        </select> &nbsp;
        标签：
        <select class="select_2" name="cate_id" v-model="search.label" style="width:70px">
            <option value="a" checked>全部</option>
            <?php if(is_array($lab)): $i = 0; $__LIST__ = $lab;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
        文章性质：
        <select class="select_2" name="cate_id" v-model="search.isyc" style="width:70px">
            <option value="a" checked>全部</option>
            <option value="0" >原创</option>
            <option value="1">转发</option>
        </select>
        <input type="text" name="keywords" style="width: 200px;" v-model="search.keywords" placeholder="医生/标题关键字" @keyup.enter="mySearch">&nbsp;&nbsp;
          <input type="button" class="btn btn-primary" @click="mySearch()" value="查询">
    </form>


    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <td style="text-align: left" >
                <input type="button" class="btn btn-info" @click="tj('')" value="设置推荐">
                <input type="button" class="btn btn-info" @click="qxtj('')" value="取消推荐">
                <input type="button" class="btn " @click="delbtn('')" value="删除">
                <a   class="btn" href="<?php echo U('add');?>" >添加文章</a>
            </td>
        </tr>
        </thead>
    </table>
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">
            <th style="width: 5%"><input style="margin-top:0px" v-on:click="select()" class="select" type="checkbox">全选</th>
            <th>发布时间</th>
            <th>作者</th>
            <th>所性标签</th>
            <th>标题</th>
            <th>浏览量</th>
            <th>评论数量</th>
            <th>点赞数量</th>
            <th>推荐</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <tr id="tr2" v-cloak v-for="item in list">
            <td><input  style="margin-top:0px" class="checkbox" type="checkbox" :value="item.id"></td>
            <td style="text-align: left;">{{item.create_time}}</td>
            <td style="text-align: center;">{{item.authorname}}</td>
            <td style="text-align: center;">{{item.label}}</td>
            <td style="text-align: center;">{{item.title}}</td>
            <td style="text-align: center;">{{item.shownum}}</td>
            <td >{{item.pls}}</td>
            <td>{{item.dzs}}</td>
            <td>{{item.tj}}</td>
            <td>
                <a class="btn btn-small btn-info" :href="'/Admin/Article/detail/id/'+ item.id">详情</a>
                <a class="btn btn-small btn-warning" :href="'/Admin/Article/edit_content/id/'+ item.id">编辑</a>
            </td>
        </tr>
        </tbody>
    </table>
    <vue-pager :conf.sync="pagerConf" ></vue-pager>
    <input type="hidden" value="1" id="currPage" />
</div>
<script src="/public/js/common.js"></script>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>
<script type="text/javascript">
    Wind.use("artDialog", function () {});
    var vm = new Vue({
        el:"#seckill",
        data: {
            pagerConf:{
                totalPage : 0,
                currPage : $("#currPage").val()?$("#currPage").val():1,
                prevShow : 3,
                nextShow : 3,
                pageRange:[]
            },
            list: null,
            search: { p: 1,st_time:'',end_time:'',tj:'a',isyc:'a',label:'a'}
        },
        watch:{
            'pagerConf.currPage':function () {
                this.getData();
            }
        },
        methods:{
            tj:function(id) {
                if (id == '') {
                    $(".checkbox").each(function () {
                        if ($(this).prop("checked")) {
                            id += $(this).val() + ',';
                        }
                    });
                    if (id == '') {
                        $.dialog({id: 'popup', lock: true, icon: "warning", content: "请选择要操作的数据", time: 2});
                        return false;
                    }
                }
                var url = "/Admin/Article/tjAction/" + "/id/" + id;
                $.getJSON(url, function (res)
                {
                    if (res.status == 1)
                    {
                        vm.getData();
                        $.dialog({id: 'popup', lock: true, icon: "succeed", content: res.info, time: 2});
                    } else {
                        $.dialog({id: 'popup', lock: true, icon: "error", content: res.info, time: 2});
                    }
                })
                $(".checkbox").attr("checked",false);
            },
            qxtj:function(id) {
                if (id == '') {
                    $(".checkbox").each(function () {
                        if ($(this).prop("checked")) {
                            id += $(this).val() + ',';
                        }
                    });
                    if (id == '') {
                        $.dialog({id: 'popup', lock: true, icon: "warning", content: "请选择要操作的数据", time: 2});
                        return false;
                    }
                }
                var url = "/Admin/Article/qxtjAction/" + "/id/" + id;
                $.getJSON(url, function (res)
                {
                    if (res.status == 1)
                    {
                        vm.getData();
                        $.dialog({id: 'popup', lock: true, icon: "succeed", content: res.info, time: 2});
                    } else {
                        $.dialog({id: 'popup', lock: true, icon: "error", content: res.info, time: 2});
                    }
                })
                $(".checkbox").attr("checked",false);
            },
            delbtn:function(id) {
                if (id == '') {
                    $(".checkbox").each(function () {
                        if ($(this).prop("checked")) {
                            id += $(this).val() + ',';
                        }
                    });
                    if (id == '') {
                        $.dialog({id: 'popup', lock: true, icon: "warning", content: "请选择要操作的数据", time: 2});
                        return false;
                    }
                }
                var url = "/Admin/Article/delbtn/" + "/id/" + id;
                Wind.use('artDialog', function () {
                    art.dialog({
                        title: false,
                        icon: 'question',
                        content: "确定要删除这些文章吗？",
                        ok: function () {
                            $.getJSON(url, function (res)
                            {
                                if (res.status == 1)
                                {
                                    vm.getData();
                                    $.dialog({id: 'popup', lock: true, icon: "succeed", content: res.info, time: 2});
                                } else {
                                    $.dialog({id: 'popup', lock: true, icon: "error", content: res.info, time: 2});
                                }
                            })
                            $(".checkbox").attr("checked",false);
                        },
                        cancelVal: '关闭',
                        cancel: true
                    });
                })


            },

            getData:function () {
                var data = this.search;
                data.st_time=$("#st_time").val();
                data.end_time=$("#end_time").val();
                data.p = this.pagerConf.currPage;
                $.getJSON("<?php echo U('index');?>",data,function (res) {
                    if(res.status==1){
                        vm.list = res.info.list;
                        vm.pagerConf.totalPage = res.info.total_page;
                        $("#currPage").val(res.info.p);

                    }else{
                        $.dialog({id: 'popup',icon: 'error', lock: true, content: res.msg, ok:function(){}});
                    }
                })
            },
            mySearch:function(){
                this.getData(true);
            },
            //全选
            select:function () {
                if($(".select").attr('checked')){
                    $(".checkbox").attr("checked",true);
                }else{
                    $(".checkbox").attr("checked",false);
                }
            },



        },
        created: function () {
            this.mySearch();
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