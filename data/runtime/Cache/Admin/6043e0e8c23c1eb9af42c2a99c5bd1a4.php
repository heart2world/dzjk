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
<style>
    #model_table tr td{
        white-space: nowrap;
        border: none;
    }
</style>
<body>
<div class="wrap js-check-wrap" id="banner_type1">
    <ul class="nav nav-tabs">
        <li class="active"><a href="<?php echo U('index');?>" >广告管理</a></li>

    </ul>
    <form class="well form-search" id="search_form" method="post">
        关键字：
        <input type="text" class="input" v-model="search.kw" style="width: 200px;" placeholder="广告主">

        标签：
        <select class="select_2" name="label" v-model="search.label">
            <option value="a">请选择</option>
            <?php if(lab != null): if(is_array($lab)): $i = 0; $__LIST__ = $lab;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
        </select>
        展示区域：
        <select class="select_2" name="zsqy" v-model="search.zsqy">
            <option value="a">全部</option>
            <option value="1" >首页</option>
            <option value="2">首页标签</option>
            <option value="3">文章详情</option>
        </select>
        状态：
        <select class="select_2" name="status" v-model="search.status">
            <option value="a">全部</option>
            <option value="1" >上架</option>
            <option value="2" >下架</option>
            <option value="3" >未开始</option>
        </select>
        上架时间：
        <input type="text" id="starttime"  name="starttime" v-model="search.starttime"  class="input js-date date" autocomplete="off" placeholder="">-
        <input type="text" id="endtime"   name="endtime" v-model="search.endtime"  class="input js-date date" autocomplete="off" placeholder="">

        <input type="button" class="btn btn-primary" @click="mySearch(true)" value="搜索">
    </form>
    <table class="table table-hover table-bordered table-list" style="width:100%;">
        <thead>
        <tr>
            <th style="width:80%;text-align: left;border-right: none" >
                <a class="btn btn-small btn-danger"  @click="deleteBatch('')" href="javascript:;">删除</a>
                <a class="btn btn-small btn-danger"  @click="xiajiaBatch('')" href="javascript:;">下架</a>
            </th>
            <th style="border-left:none">
                <input type="button" class="btn btn-primary"  value="添加广告" @click="show_model" >
            </th>
        </tr>
        </thead>
    </table>
    <div class="control-group">
        <div class="controls">
            <table class="table table-hover table-bordered table-list">
                <thead>
                <tr>
                    <th style="width: 10%"><input style="margin-top:0px" v-on:click="select()" class="select" type="checkbox">全选</th>
                    <th>上架时间</th>
                    <th>广告主</th>
                    <th>图片</th>
                    <th>跳转地址</th>
                    <th>关联标签</th>
                    <th>展示区域</th>
                    <th>下架时间</th>
                    <th>当前状态</th>
                    <th>访问量</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-cloak v-for="item in list">
                    <td><input style="margin-top:0px" class="checkbox" type="checkbox" :value="item.id"></td>
                    <td >{{item.st}}</td>
                    <td>{{item.ggz}}</td>
                    <td><img style="width: 100px;" :src="item.pic" alt=""></td>
                    <td>{{item.links}}</td>
                    <td>{{item.lab}}</td>
                    <td>{{item.zsqy}}</td>
                    <td>{{item.ent}}</td>
                    <td>{{item.status}}</td>
                    <td>{{item.visit}}</td>
                    <td>
                        <a  href="javascript:void(0);" @click="edit(item.id,item.ggz,item.st,item.ent,item.title,item.pic,item.links,item.lab_n,item.zsqy_n)" class="btn btn-small btn-danger">编辑</a></td>
                </tr>
                </tbody>
            </table>
            <vue-pager :conf.sync="pagerConf" ></vue-pager>
        </div>
    </div>

    <div id="bannerModal" style="width: 580px"  class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">
                <span class="showtitle" id="model_title" v-if="!edit_list">添加</span>广告
                <span class="showtitle"  v-else>编辑</span>广告
            </h3>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" id="bannerForm">
                <table class="table" id="model_table">
                    <tr>
                        <td  style="text-align: right">广告主：</td>
                        <td >
                            <input type="text" value="" class="ggz"  name="ggz" placeholder="选填，15字以内">
                        </td>
                    </tr>

                    <tr>
                        <td  style="text-align: right">上架时间：</td>
                        <td >
                            <!--js-datetime-->
                            <input type="text" id="starttime1" id="kssj" name="starttime1" class="input  starttime1 js-date date" autocomplete="off" placeholder="开始时间">-
                            <input type="text" id="endtime1" id="jssj"   name="endtime1"    class="input  endtime1 js-date date" autocomplete="off" placeholder="结束时间">

                        <!--<input type="number" value=""  name="sort" id="sort" @blur="check_num($event)">-->
                        </td>
                    </tr>

                    <tr>
                        <td  style="text-align: right">广告标题：</td>
                        <td >
                            <input type="text" value=""  class="title" name="title" placeholder="">
                        </td>
                    </tr>

                    <tr>
                        <td style="text-align: right">广告图片：<br>建议尺寸(640*320)</td>
                        <td v-if="!edit_list">
                            <input type="hidden"  name="pic" value=""   id="pic" >
                            <a href="javascript:void(0);"
                               onclick="flashupload('thumb_images', '附件上传','pic',thumb_images2,'1,jpg|jpeg|gif|png|bmp,1200','banner','','','image');return false;">
                                <img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png"
                                     onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'" name="pic_preview"  id="pic_preview" width="80"
                                     style="cursor: hand; height: 80px !important;"/>
                            </a>
                        </td>
                        <td v-else>
                            <input type="hidden"  name="pic" :value="edit_list.pic"  id="pic" >
                            <a href="javascript:void(0);"
                               onclick="flashupload('thumb_images', '附件上传','pic',thumb_images2,'1,jpg|jpeg|gif|png|bmp,1200','banner','','','image');return false;">
                                <img :src="edit_list.pic"
                                     onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'" name="pic_preview"  id="pic_preview" width="80"
                                     style="cursor: hand; height: 80px !important;"/>
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td  style="text-align: right">跳转链接：</td>
                        <td v-if="!edit_list">
                            <input type="text" value="" class="links" name="links" id="links" >
                        </td>

                    </tr>
                    <tr>
                        <td  style="text-align: right">关联标签：</td>
                        <td v-if="!edit_list">
                            <select class="select_2 glbq" name="label">
                                <option value="a">请选择</option>
                                <?php if(lab != null): if(is_array($lab)): $i = 0; $__LIST__ = $lab;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td  style="text-align: right">展示区域：</td>
                        <td v-if="!edit_list">
                            <input type="checkbox" class="sy" name="sy" value="1"> 首页
                            <input type="checkbox" class="sybq" name="sybq" value="2"> 首页标签
                            <input type="checkbox" class="wzxq" name="wzxq" value="3"> 文章详情
                        </td>
                    </tr>
                </table>

                <div class="alert alert-error hide" id="alert_error">
                </div>
                 <input type="hidden" name="id" id="edit_id"/>
            </form>
        </div>
        <div class="modal-footer text-center">
            <button class="btn" id="bannerCancel" @click="close_model(1)">取消</button>
            <button class="btn btn-success" id="bannerSubmit" @click="bannerSubmit">保存</button>
        </div>
    </div>

</div>
</body>
</html>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>
<script type="text/javascript" src="/public/js/content_addtop.js"></script>



<script>
    $(document).on('click','.nav-tabs li',function(){
        $(this).addClass('active').siblings().removeClass('active');
    })
</script>
<script>
    Wind.use("artDialog", function () {});
    var vm = new Vue({
        el: '#banner_type1',
        data: {
            pagerConf: {
                totalPage: 0,
                currPage: 1,
                prevShow: 3,
                nextShow: 3,
                pageRange: []
            },
            list: null,
            province:[],

            add_province:-1,
            edit_list:'',
            search:{
                p:1,
                zsqy:'a',
                label:'a',
                status:'a'
            }
        },
        watch:{
            'pagerConf.currPage':function () {
                this.get_list();
            },

        },
        methods:{

            // case_time:function(){
            //     //日期+时间选择器
            //     var dateTimeInput = $("input.js-datetime");
            //     if (dateTimeInput.length) {
            //         Wind.use('datePicker', function () {
            //             dateTimeInput.datePicker({
            //                 time: true
            //             });
            //         });
            //     }
            // },

            edit:function (id,ggz,st,ent,title,pic,links,lab,zsqy) {
              // alert(id);

                $('.showtitle').html('编辑');
                $('#edit_id').val(id);
                $('.ggz').val(ggz);
                $('#starttime1').val(st);
                $('#endtime1').val(ent);
                $('.title').val(title);
                $('#pic_preview').attr('src',pic);
                $('#pic').val(pic);
                $('.links').val(links);
                $(".glbq").val(lab);

                if(zsqy.indexOf("1")!=-1){
                    $('.sy').attr("checked","true");
                }else
                {
                    $('.sy').removeAttr("checked");
                }
                if(zsqy.indexOf("2")!=-1){
                    $('.sybq').attr("checked","true");
                }else
                {
                    $('.sybq').removeAttr("checked");
                }
                if(zsqy.indexOf("3")!=-1){
                    $('.wzxq').attr("checked","true");
                }else
                {
                    $('.wzxq').removeAttr("checked");
                }
                $('#bannerModal').modal('show');
            },

            xiajiaBatch:function(id) {
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
                var url = "/Admin/Adve/OffAction/" + "/id/" + id;
                $.getJSON(url, function (res) {
                    if (res.status == 1)
                    {
                        vm.get_list();
                        $.dialog({id: 'popup', lock: true, icon: "succeed", content: res.info, time: 2});
                    } else {
                        $.dialog({id: 'popup', lock: true, icon: "error", content: res.info, time: 2});
                    }
                })
                $(".checkbox").attr("checked",false);

            },

            deleteBatch:function(id) {
                if (id == '') {
                    $(".checkbox").each(function () {
                        if ($(this).prop("checked")) {
                            id += $(this).val() + ',';
                        }
                    });
                    if (id == '') {
                        $.dialog({id: 'popup', lock: true, icon: "warning", content: "请至少选择一条数据", time: 2});
                        return false;
                    }
                }
                var url = "/Admin/Adve/delete/" + "/id/" + id;
                Wind.use('artDialog', function () {
                    art.dialog({
                        title: false,
                        icon: 'question',
                        content: "确定要删除这些广告吗？",
                        ok: function () {
                            $.getJSON(url, function (res) {
                                if (res.status == 1)
                                {
                                    vm.get_list();
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
            mySearch:function(){
                this.pagerConf.currPage = 1;
                this.get_list();
            },
            get_list:function(from){
                var data = this.search;
                data.starttime = $('#starttime').val();
                data.endtime = $('#endtime').val();
                data.p = from == undefined ? this.pagerConf.currPage : 1;
                $.getJSON("<?php echo U('index');?>",data,function(res){
                    if(res){
                        vm.list = res.info.list; 
                        vm.pagerConf.totalPage = res.info.total_page
                    }
                })
            },

            //全选
            select:function () {
                if($(".select").attr('checked')){
                    $(".checkbox").attr("checked",true);
                }else{
                    $(".checkbox").attr("checked",false);
                }
            },

            close_model:function(){
                $("#bannerModal").modal('hide');
                vm.add_province = -1;
                vm.edit_list = '';
            },
            show_model:function(){
                $('.wzxq').removeAttr("checked");
                $('.sybq').removeAttr("checked");
                $('.sy').removeAttr("checked");
                $('.showtitle').html('添加');
                $('#edit_id').val(0);
                $('.ggz').val('');
                $('#pic').val('');
                $('#starttime1').val('');
                $('#endtime1').val('');
                $('.title').val('');
                $('#pic_preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');
                $('.links').val('');
                $(".glbq").val('');
                $("#bannerModal").modal('show');
            },
            bannerSubmit:function(){
                var ggz = $('.ggz').val();
                var title = $('.title').val();
                if(title.length == 0)
                {
                    alert('请填写标题'); return false;
                }
                if(title.length > 40)
                {
                    alert('标题只能40个字以内'); return false;
                }


                var kssj = $('#kssj').val();
                var jssj = $('#jssj').val();
            // alert(kssj);
                if(!kssj)
                {
                    // alert('开始时间不能为空'); return false;
                }
                if(!jssj)
                {
                    // alert('结束时间不能为空'); return false;
                }


                if(ggz.length > 0 && ggz.length > 15)
                {
                      alert('只能15个字以内'); return false;
                }else
                {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo U('Adve/add');?>",
                        data: $("#bannerForm").serialize(),
                        success: function (res) {
                            if(res.status==1){
                                $.dialog({id: 'popup', lock: true,icon:"succeed", content: res.info, time: 1});
                                vm.get_list();
                                $("#bannerModal").modal('hide');
                                vm.edit_list = '';
                            }else{
                                $.dialog({id: 'popup', lock: true,icon:"error", content: res.info, time: 1});
                            }
                        }
                    })
                }

            },
        },
        created:function(){
            this.get_list();
        }
    });

    function thumb_images2(uploadid, returnid) {
        //取得iframe对象
        var d = uploadid.iframe.contentWindow;
        //取得选择的图片
        var in_content = d.$("#att-status").html().substring(1);
        if (in_content == '') return false;
        if (!IsImg(in_content)) {
            isalert('选择的类型必须为图片类型！');
            return false;
        }
        $("#pic").val(in_content);
        $("#pic_preview").attr('src',in_content);
    }


</script>
<script type="text/javascript" src="/public/js/common.js"></script>