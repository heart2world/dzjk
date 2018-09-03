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
.pagination {float: right;}
</style>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('index');?>">医生列表</a></li>
        <li  style="cursor: pointer;"><a  href="<?php echo U('add');?>">添加医生</a></li>
    </ul>
    <div class="well form-search " >
        模糊查询：
        <input type="text" name="nickname" style="width: 200px;" v-model="search.nickname" placeholder="姓名/昵称/电话"
               @keyup.enter="mySearch()">&nbsp;&nbsp;
         状态：
        <select class="select_2" name="cate_id" v-model="search.status" style="width:70px">
            <option value="100">全部</option>
            <option value="1" >正常</option>
            <option value="0">冻结中</option>
        </select> &nbsp;
        省 ：
        <select class="select_2 prov" id="departure_province" name="prov" style="width:70px" v-model="search.prov"   @change="get_city()">
            <option value="all">全部</option>
            <?php if(is_array($province)): $i = 0; $__LIST__ = $province;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["region_id"]); ?>"><?php echo ($vo["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
        市：
        <select class="select_2 city" name="city" id="departure_city" style="width:70px">
            <option value="all">全部</option>
            <option v-for="it in city" :value="it.region_id">{{it.region_name}}</option>
        </select>

        专业：
        <select class="select_2" name="zy" v-model="search.zy" style="width:70px">
            <option value="all">全部</option>
            <?php if(is_array($label)): $i = 0; $__LIST__ = $label;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>


        </select>
        开启咨询：
        <select class="select_2" name="iszx" v-model="search.iszx" style="width:70px">
            <option value="all">全部</option>
            <option value="1" >是</option>
            <option value="2">否</option>
        </select>

          <input type="button" class="btn btn-primary" @click="mySearch()" value="查询">
    </div>
   
    <table  class="table table-hover table-bordered table-list">
        <thead>
        <tr id="tr">
            <!--<th style="width: 5%"><input style="margin-top:0px" v-on:click="select()" class="select" type="checkbox">全选</th>-->
            <th>姓名</th>
            <th>头像</th>
            <th>昵称</th>
            <th>电话</th>
            <th>专业</th>
            <th>积分</th>
            <th>省（直辖市）</th>
            <th>市</th>
            <th>最后登录时间</th>
            <th>状态</th>
            <th>开启咨询</th>
            <th style="text-align: center;">医生余额</th>
            <th style="text-align: center;">答题数量</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <tr id="tr2" v-cloak v-for="item in list">
            <td style="text-align: left;">{{item.truename}}</td>
            <td><img :src="item.avatar" width="60" style="height: 60px !important;"></td>
            <td>{{item.nickname}}</td>
            <td>{{item.mobile}}</td>
            <td>{{item.zy}}</td>
            <td>{{item.integral}}</td>
            <td>{{item.prov}}</td>
            <td>{{item.city}}</td>
            <td>{{item.last_login_time}}</td>
            <td>{{item.status==1?'正常':'冻结中'}}</td>
            <td>{{item.iszx_text}}</td>
            <td style="text-align: center;">{{item.coin}}</td>
            <td style="text-align: center;">{{item.number}}</td>
            <td>
                <a class="btn btn-small btn-info" :href="'Admin/Doctor/detail/member_id/'+ item.id">详情</a>
                <a v-if="item.status==0" href="javascript:void(0);" @click="openBatch(item.id)" class="btn btn-small btn-warning">解冻</a>
                <a v-if="item.status==1" href="javascript:void(0);" @click="frozenBtn(item.id)" class="btn btn-small btn-warning">冻结</a>
                <a  href="javascript:;" @click="deldoctor(item.id)" class="btn btn-small btn-danger">删除</a>
                <a class="btn btn-small btn-info" :href="'Admin/Doctor/answerlist/doctorid/'+ item.id">查看答题记录</a>
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
            city:[],
            pagerConf:{
                totalPage : 0,
                currPage : $("#currPage").val()?$("#currPage").val():1,
                prevShow : 3,
                nextShow : 3,
                pageRange:[]
            },
            list: null,
            search: { p: 1,nickname:'',iszx:'all',status:100,zy:'all',city:'all',prov:'all' }
        },
        watch:{
            'pagerConf.currPage':function () {
                this.getData();
            }
        },
        methods:{
            get_city:function(){
                $.getJSON("/Admin/Doctor/get_city",{parent_id:$('#departure_province').val()},function (res) {
                    if(res.status==1){
                        vm.city = res.list;
                        if(!res.list){
                            $.dialog({id: 'popup',icon: 'error', lock: true, content: '未获取到城市', ok:function(){}});
                        }
                    }else{
                        $.dialog({id: 'popup',icon: 'error', lock: true, content: res.msg, ok:function(){}});
                    }
                })
            },
            deldoctor:function (id) {
                if(!id)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '参数不存在', time: 2});
                }else{
                    var url = "/Admin/Member/deldoctor/"+"/id/"+id;
                    $.getJSON(url,function (res) {
                        if(res.status == 1){
                            vm.getData();
                            $.dialog({id: 'popup', lock: true,icon:"succeed", content: res.info, time: 2});
                        }else{
                            $.dialog({id: 'popup', lock: true,icon:"error", content: res.info, time: 2});
                        }
                    })
                }
            },
            frozenBtn:function (id) {
                if(!id)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '参数不存在', time: 2});
                }else{
                    var url = "/Admin/Member/frozenAction/"+"/id/"+id;
                    $.getJSON(url,function (res) {
                        if(res.status == 1){
                            vm.getData();
                            $.dialog({id: 'popup', lock: true,icon:"succeed", content: res.info, time: 2});
                        }else{
                            $.dialog({id: 'popup', lock: true,icon:"error", content: res.info, time: 2});
                        }
                    })
                }
            },

            export_excel: function () {
                var data = vm.search;
                var str='';
                for(var key in data){
                    str+=key+'='+data[key]+'&';
                }
                location.href="<?php echo U('export');?>?"+str;
            },
            sort_order:function(order){
                vm.search.order=order;
                this.getData();
            },

            //启用支持批量
            openBatch:function(id){
                if(id==''){
                    id=vm.get_select_id();
                    if(id==''){
                        return false;
                    }
                }
                var url = "/Admin/Member/open_member/"+"/id/"+id;
                Wind.use('artDialog', function () {
                    $.getJSON(url,function (res) {
                        if(res.status == 1){
                            vm.getData();
                            setTimeout(function(){
                                $(".checkbox").prop('checked',false)
                            },2000);
                            $.dialog({id: 'popup', lock: true,icon:"succeed", content: res.info, time: 2});

                        }else{
                            $.dialog({id: 'popup', lock: true,icon:"error", content: res.info, time: 2});
                        }
                    })
                })
            },
            get_select_id:function(){
                var id="";
                $(".checkbox").each(function(){
                    if($(this).prop("checked")){
                        id += $(this).val()+',';
                    }
                });
                if(id==''){
                    $.dialog({id: 'popup', lock: true,icon:"warning", content: "请至少选择一条信息", time: 2});
                    return false;
                }
                return id;
            },
            getData:function () {
                var data = this.search;
                data.st_time = $("#st_time").val();
                data.end_time = $("#end_time").val();
                data.prov = $(".prov").val();
                data.city = $(".city").val();

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