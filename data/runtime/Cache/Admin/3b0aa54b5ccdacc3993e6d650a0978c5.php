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
        <li ><a href="javascript:history.back()">文章列表</a></li>
        <li class="active"><a><?php if(ACTION_NAME == 'add'): ?>添加<?php else: ?>编辑<?php endif; ?>文章</a></li>
    </ul>
    <form  class="form-horizontal"  method="post" @submit.prevent="submit_form">

        <div class="control-group" >
            <label class="control-label">所属标签：</label>
            <div class="controls">
                <select class="select_2 labellist" name="cate_id" style="width:100px">
                    <option value="0">请选择标签</option>
                    <?php if(lab != null): if(is_array($lab)): $i = 0; $__LIST__ = $lab;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                </select>
               <span style="margin-left: 10%;font-size: 14px;font-weight: normal;line-height: 20px;">
                       推荐到首页：
                        <input type="checkbox" id="checkbox-id" class="istjshouye"> 是
               </span>
            </div>
        </div>

        <div class="control-group" >
            <label class="control-label">作者：</label>
            <div class="controls">
                <select class="select_2 zzp" name="cate_id"  style="width:100px">
                    <option value="0">平台发布</option>

                    <?php if(count($yslist) != null): if(is_array($yslist)): $i = 0; $__LIST__ = $yslist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ys): $mod = ($i % 2 );++$i;?><option value="<?php echo ($ys["id"]); ?>" ><?php echo ($ys["nickname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>

                </select>
            </div>
        </div>
        <div class="control-group" >
            <label class="control-label">标题：</label>
            <div class="controls">
                <input type="text" class="title" name="keywords" placeholder="30字以内" style="width: 200px;"  >&nbsp;&nbsp;
            </div>
        </div>
        <div class="control-group" >
            <label class="control-label">列表展示图：
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
                    <div style="clear: both"></div>
                </fieldset>
                <a href="javascript:;" id="photos_upload_btn" class="btn btn-small">选择图片</a>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">消息内容：</label>
            <div class="controls">
                <textarea type="text/plain" id="content" name="content"><?php echo ($info["content"]); ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button  type="submit"  class="btn btn-primary  js-ajax-submit">发布</button>
            <a  href="javascript:history.back();" class="btn btn-primary">取消</a>
        </div>
        <input id="id" type="hidden" name="id" value="<?php echo ($info["id"]); ?>"  >
    </form>
</div>
</body>
</html>
<script src="/public/js/vue.js"></script>
<script src="/public/js/vue1.js"></script>
<script type="text/javascript" src="/public/js/content_addtop.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>
<script>
    var app = new Vue({
        el:'#seckill',
        data:{
            goods: {
                photos: [],
            }
        },
        methods:{

            remove_image: function (image) {
                this.goods.photos.$remove(image);
            },
            submit_form:function ()
            {
                var label =  $('.labellist').children('option:selected').val();

                if(parseInt(label) <= 0)
                {
                    Wind.use('artDialog', function () {
                        art.dialog({
                            content: '必须选择一个标签',
                            icon: 'error',
                            id: 'popup',
                            lock: true,
                            time: 2
                        });
                    })
                    return false;
                }

                if($('#checkbox-id').is(':checked')) {
                    var tj = 1;
                }else
                {
                    var tj = 0;
                }
                var zzp =  $('.zzp').children('option:selected').val();

                var title = $('.title').val();

                if(title.length == 0)
                {
                    Wind.use('artDialog', function () {
                        art.dialog({
                            content: '必须填标题',
                            icon: 'error',
                            id: 'popup',
                            lock: true,
                            time: 2
                        });
                    })
                    return false;
                }

                if(title.length > 30)
                {

                    Wind.use('artDialog', function () {
                        art.dialog({
                            content: '标题30字以内',
                            icon: 'error',
                            id: 'popup',
                            lock: true,
                            time: 2
                        });
                    })
                    return false;
                }
                var contents = content.getContent();
                var c = this.goods;
                $.ajax({
                    url: "<?php echo U('add');?>",
                    type: "POST",
                    data: {goods: c,label:label,tj:tj,zzp:zzp,title:title,contents:contents},
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


    $(function ()
    {
        $("#photos_upload_btn").click(function () {
            var num= app.goods.photos.length;
            if(num>=3){
                Wind.use('artDialog', function () {
                    art.dialog({
                        content: '最多只能添加三张',
                        icon: 'error',
                        id: 'popup',
                        lock: true,
                        time: 2
                    });
                })
                return false;
            }
            var ready_photos_num = 3- $("#photos li").length;
            var args = ready_photos_num + ",gif|jpg|jpeg|png|bmp,0";
            flashupload('albums_images', '图片上传', 'photos', change_images2, args, '', '', '','image')
        })
    })
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
            app.goods.photos.splice(app.goods.photos.length,0,contents[i]);
        }
    }
</script>
<script type="text/javascript" src="/public/js/common.js"></script>