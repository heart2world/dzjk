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

    .towHLab{
        margin-left: 60px;
    }
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
    .spec-sub {
        margin-right: 20px;
        float: left;
    }
    .controls span{
        margin-right: 10px;
    }
    .ueditorbox{
        height: auto;
    }
    .ueditorbox .ued
    {
        width: 800px;
        height: 500px;
        overflow-y:scroll;
        margin-bottom: 200px;
    }
    .delchildbox {
        color: red;
        cursor: pointer;
    }
    .inputboxlist{
        width: 100px;
    }
    .addhybtn
    {
        display: inline-block;padding: 4px 12px;margin-bottom: 0;font-size: 14px;
        line-height: 20px;color: #333;text-align: center;
        text-shadow: 0 1px 1px rgba(255,255,255,0.75);    vertical-align: middle;
        cursor: pointer;
        background-color: #2fa4e7;
        color: #FFF;
        margin-bottom: 1px;
    }
    .delchildbox {
        color: red;
        cursor: pointer;
    }
    #photos li{ float: left; margin: 10px;}
</style>
</head>
<body>
<div class="wrap js-check-wrap" id="seckill">
    <ul class="nav nav-tabs">
        <li ><a href="javascript:history.back()">医生列表</a></li>
        <li class="active"><a><?php if(ACTION_NAME == 'edit'): ?>编辑<?php else: ?>添加<?php endif; ?>医生</a></li>
    </ul>
    <form class="form-horizontal"  method="post" id="thisForm"  @submit.prevent="submit_form">
        <div class="control-group">
            <label class="control-label">头像：</label>
            <div class="controls">
                <input type="hidden"  name="headimg" value="<?php echo ($info["headimg"]); ?>"   id="avatar" >
                <a href="javascript:void(0);"
                   onclick="flashupload('thumb_images', '附件上传','headimg',thumb_images2,'1,jpg|jpeg|gif|png|bmp,1920','business','','','image');return false;">
                    <img src="<?php echo ($info["main_pic"]); ?>"
                         onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'"  id="headimg_preview" width="80"
                         style="cursor: hand; height: 80px !important;"/>
                </a>
                <input type="button" class="btn btn-info" id="btn_delete_main_pic" value="删除">
                建议尺寸：300*300
                <span class="form-required">*</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">开启咨询：</label>
            <div class="controls">
                <input type="checkbox" id="iszx" class="iszx"> 是
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">昵称：</label>
            <div class="controls">
                <input type="text" class="nickname"  maxlength="8" placeholder="8个字以内" value="<?php echo ($info["user_login"]); ?>"  >
                <span class="form-required">*</span>

                <span class="towHLab">
                    手机号：
                    <input   type="number" class="mobile"   maxlength="11" placeholder="" value="<?php echo ($info["user_login"]); ?>"  >
                    <span class="form-required">*</span>
                </span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">省市：</label>
            <div class="controls">
                <select class="select_2 prov" id="departure_province" name="prov" style="width:70px"   @change="get_city()">
                    <option value="all">全部</option>
                    <?php if(is_array($province)): $i = 0; $__LIST__ = $province;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["region_id"]); ?>"><?php echo ($vo["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <select class="select_2 city" name="city" id="departure_city" style="width:70px">
                    <option value="all">全部</option>
                    <option v-for="it in city" :value="it.region_id">{{it.region_name}}</option>
                </select>
                <span class="form-required">*</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">专业：</label>
            <div class="controls">
                <select name="" class="zy" id="">
                    <?php if(is_array($label)): $i = 0; $__LIST__ = $label;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <span class="form-required">*</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">个人介绍：</label>
            <div class="controls">
                <textarea name="introduction" class="intro" maxlength="100" rows="2" style="width: 400px"><?php echo ($info["introduction"]); ?></textarea>
                <span class="form-required">*</span>
            </div>
        </div>


        <h4>认证信息</h4>

        <div class="control-group">
            <label class="control-label">姓名：</label>
            <div class="controls">
                <input   type="text" class="truename"  maxlength="30" placeholder="" value="<?php echo ($info["name"]); ?>"  >    <span class="form-required">*</span>
                <span class="towHLab">
                医院:  <input   type="text"  class="hosp" maxlength="40" placeholder="" value="<?php echo ($info["name"]); ?>"  >    <span class="form-required">*</span>
                </span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">科室：</label>
            <div class="controls">
                <input   type="text"  class="ks" maxlength="30" placeholder=""  value="<?php echo ($info["name"]); ?>"  >    <span class="form-required">*</span>
                <span class="towHLab">
                职位：<input   type="text" class="zw"  maxlength="30" placeholder=""  value="<?php echo ($info["name"]); ?>"  >    <span class="form-required">*</span>
                </span>
            </div>
        </div>


        <div class="control-group">
            <label class="control-label">身份证：
                <br>(建议尺寸:500*500)
            </label>
            <div class="controls">
                <fieldset>
                    <legend>图片列表</legend>
                    <ul id="photos" class="pic-list unstyled">
                        <li  v-for="photo in goods.photos">
                            <img  onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'"
                                  src="{{photo}}"  class="ttt"  style="cursor: hand;width: 100px;height: 100px" ondblclick="image_priview('{{photo}}');"/>
                            <input required readonly title='双击查看' type="hidden" value="{{photo}}"  style="width:310px;" ondblclick="image_priview('{{photo}}');" class="input image-url-input">
                            <a href="javascript:;" @click="remove_image(photo)">移除</a>
                        </li>
                    </ul>
                </fieldset>
                <a href="javascript:;" id="photos_upload_btn" class="btn btn-small">选择图片</a>
                <span class="form-required">*</span>
            </div>

        </div>

        <div class="control-group">
            <label class="control-label">执业资格证：
                <br>(建议尺寸:500*500)
            </label>
            <div class="controls">
                <fieldset>
                    <legend>图片列表</legend>
                    <ul id="zyzgz" class="pic-list unstyled">
                        <li v-for="photos in goods.zyzgz" style="float: left;margin-left: 10px;">
                            <img  onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'"
                                  src="{{photos}}"  class="ttt"  style="cursor: hand;width: 100px;height: 100px;" ondblclick="image_priview('{{photos}}');"/>
                            <input required readonly title='双击查看' type="hidden" value="{{photos}}"  style="width:310px;" ondblclick="image_priview('{{photos}}');" class="input image-url-input">
                            <a href="javascript:;" @click="remove_zyzgz(photos)">移除</a>
                        </li>
                    </ul>
                </fieldset>
                <a href="javascript:;" style="margin-top: 5px;" id="photos_upload_btn_zyzgz" class="btn btn-small">选择图片</a>
                <span class="form-required">*</span>
            </div>

        </div>

        <div class="control-group">
            <label class="control-label">备注：</label>
            <div class="controls">
                <textarea name="introduction" class="grjs" maxlength="30" rows="2" style="width: 400px"><?php echo ($info["introduction"]); ?></textarea>

                <fieldset>
                    <legend>图片列表</legend>
                    <ul id="grjsimg" class="pic-list unstyled">
                        <li v-for="grjsimg in goods.grjsimg" style="float: left;margin-left: 10px;">
                            <img  onerror="this.src='/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png'"
                                  src="{{grjsimg}}"  class="ttt"  style="cursor: hand;width: 100px;height: 100px;" ondblclick="image_priview('{{grjsimg}}');"/>
                            <input required readonly title='双击查看' type="hidden" value="{{grjsimg}}"  style="width:310px;" ondblclick="image_priview('{{grjsimg}}');" class="input image-url-input">
                            <a href="javascript:;" @click="remove_grjsimg(grjsimg)">移除</a>
                        </li>
                    </ul>
                </fieldset>
                <a href="javascript:;" style="margin-top: 5px;" id="photos_upload_btn_grjsimg" class="btn btn-small">选择图片</a>
            </div>

        </div>



        <div class="form-actions">
            <button  type="submit"  class="btn btn-primary  js-ajax-submit">保存</button>
            <a  href="javascript:history.back();" class="btn btn-primary">取消</a>
        </div>
        <input id="id" type="hidden" name="id" value="<?php echo ($info["id"]); ?>"  >
    </form>
</div>
<script src="/public/js/common.js"></script>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vueComponent/pager.js"></script>
<script type="text/javascript" src="/public/js/content_addtop.js"></script>


<script src="/public/js/vue1.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>

<script>
    Wind.use("artDialog", function () {});
    var vm = new Vue({
        el:"#seckill",
        data: {
            city:[],
            goods: {
                photos: [],
                zyzgz: [],
                grjsimg: [],
            }
        },
        watch:{
            'pagerConf.currPage':function () {
                this.getData();
            }
        },
        methods:{
            remove_image: function (image) {
                this.goods.photos.$remove(image);
            },
            remove_grjsimg: function (image) {
                this.goods.grjsimg.$remove(image);
            },
            remove_zyzgz: function (image) {
                this.goods.zyzgz.$remove(image);
            },
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
            submit_form:function ()
            {
                var avatar = $('#avatar').val();
                if(!avatar)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '头像不能为空', time: 2});
                    return false;
                }
                if($('#iszx').is(':checked')) {
                    var iszx = 0;
                }else
                {
                    var iszx = 1;
                }
                var nickname = $('.nickname').val();
                if(nickname.length > 8 || nickname.length == 0)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '昵称不能为空', time: 2});
                    return false;
                }
                var mobile = $('.mobile').val();
                if(mobile.length != 11 || mobile.length == 0)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '手机号格式错误', time: 2});
                    return false;
                }

                var prov = $('#departure_province').val();
                var city = $('#departure_city').val();
                if(prov == 'all' || city == 'all')
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '省市不能为空', time: 2});
                    return false;
                }

                var zy = $('.zy').val();

                if(parseInt(zy) == 0)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '专业不能为空', time: 2});
                    return false;
                }

                var grjs = $('.grjs').val();

                var intro = $('.intro').val();

                if(!intro)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '简介不能为空', time: 2});
                    return false;
                }

                var truename = $('.truename').val();
                if(!truename)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '真实姓名不能为空', time: 2});
                    return false;
                }

                var hosp = $('.hosp').val();
                if(hosp.length == 0)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '医院不能为空', time: 2});
                    return false;
                }
                var ks = $('.ks').val();
                if(!ks)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '科室不能为空', time: 2});
                    return false;
                }
                var zw = $('.zw').val();
                if(!zw)
                {
                    $.dialog({id: 'popup', lock: true,icon:"error", content: '职位不能为空', time: 2});
                    return false;
                }


                var c = this.goods;




                $.ajax({
                    url: "<?php echo U('add');?>",
                    type: "POST",
                    data: {goods: c,avatar:avatar,iszx:iszx,nickname:nickname,mobile:mobile
                    ,prov:prov,city:city,zy:zy,truename:truename,hosp:hosp,ks:ks,zw:zw,intro:intro,
                        grjs:grjs
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1)
                        {
                            Wind.use('noty', 'noty', function () {
                                var n = noty({
                                    text: data.info,
                                    type: 'success'
                                });
                            });
                            setTimeout(function () {
                                location.href = "<?php echo U('index');?>";
                            }, 2000)
                        } else {
                            Wind.use('artDialog', function () {
                                art.dialog({
                                    content: data.info,
                                    icon: 'error',
                                    id: 'popup',
                                    lock: true,
                                    time: 2
                                });
                            })
                        }
                    }
                })
            }
        },
        created: function () {

        }
    });

    $("#btn_delete_main_pic").click(function(){
        $("#avatar").val('');
        $("#headimg_preview").attr('src',"/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png");
    });
    //单图上传
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
        $("#avatar").val(in_content);
        $("#headimg_preview").attr('src',in_content);
    }

    $(function ()
    {

        $("#photos_upload_btn_grjsimg").click(function () {
            var num= vm.goods.grjsimg.length;
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

        $("#photos_upload_btn_zyzgz").click(function () {
            var num= vm.goods.zyzgz.length;
            if(num>=2)
            {
                Wind.use('artDialog', function () {
                    art.dialog({
                        content: '最多只能添加两张',
                        icon: 'error',
                        id: 'popup',
                        lock: true,
                        time: 2
                    });
                })
                return false;
            }
            var ready_photos_num = 2- $("#zyzgz li").length;
            var args = ready_photos_num + ",gif|jpg|jpeg|png|bmp,0";
            flashupload('albums_images', '图片上传', 'photos', change_images3, args, '', '', '','image')
        });

        $("#photos_upload_btn").click(function () {
            var num= vm.goods.photos.length;
            if(num>=2)
            {
                Wind.use('artDialog', function () {
                    art.dialog({
                        content: '最多只能添加两张',
                        icon: 'error',
                        id: 'popup',
                        lock: true,
                        time: 2
                    });
                })
                return false;
            }
            var ready_photos_num = 2- $("#photos li").length;
            var args = ready_photos_num + ",gif|jpg|jpeg|png|bmp,0";
            flashupload('albums_images', '图片上传', 'photos', change_images2, args, '', '', '','image') 
        });

        function change_images2(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            var contents = in_content.split('|');
            var filenames = in_filename.split('|');
            $('#' + returnid + '_tips').css('display', 'none');
            if (contents == '') return true;
            for(i in contents){
                vm.goods.photos.splice(vm.goods.photos.length,0,contents[i]);
            }
        }
        function change_images3(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            var contents = in_content.split('|');
            var filenames = in_filename.split('|');
            $('#' + returnid + '_tips').css('display', 'none');
            if (contents == '') return true;
            for(i in contents)
            {
                vm.goods.zyzgz.splice(vm.goods.zyzgz.length,0,contents[i]);
            }
        }
        function change_images4(uploadid, returnid) {
            var d = uploadid.iframe.contentWindow;
            var in_content = d.$("#att-status").html().substring(1);
            var in_filename = d.$("#att-name").html().substring(1);
            var str = $('#' + returnid).html();
            var contents = in_content.split('|');
            var filenames = in_filename.split('|');
            $('#' + returnid + '_tips').css('display', 'none');
            if (contents == '') return true;
            for(i in contents)
            {
                vm.goods.grjsimg.splice(vm.goods.grjsimg.length,0,contents[i]);
            }
        }
    });

</script>
</body>
</html>