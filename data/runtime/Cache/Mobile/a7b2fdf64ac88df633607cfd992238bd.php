<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <title>番茄医学</title>
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta itemprop="name" content="" />
    <meta itemprop="description" name="description" content="" />
    <meta itemprop="image" content="img_url" />
    <meta name="format-detection" content="telephone=no" />

    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/global.css">
    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/swiper.min.css">
    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/NumberBank.css"/>
    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/myh.css"/>
    <link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/wqx.css"/>
</head>
<style>.login_page{padding:0}</style>
<body>
<section class="flex_column w-height100">
    <?php if(!is_weixin()){ ?>
    <div class="w-bgcolor44D397">
        <?php if(sp_is_ios()): ?><a onclick="back()" class="headLeft"></a>
    <?php else: ?>
    <a onclick="back()" class="headLeft"></a>
    <!--<a href="javascript:history.back();"  class="headLeft" ></a>--><?php endif; ?>


        <a href="javascritp:;" class="headRight"></a>
        <span class="headTit">番茄医学</span>
    </div>
    <?php } ?>
    <div class="w-flexitem1 w-section">
        <div class="login_page  text-center">
            <!--<div class="login_default">

                <a href="javascript:;">
                    <img src="/logo.png">
                    &lt;!&ndash;<p>用户登录</p>&ndash;&gt;
                    <p></p>
                </a>
            </div>-->
            <!--<div class="dian_title"><span>或者,你可以</span></div>-->
            <div class="login_default p-t-25" >

                <a class="wx_login" onclick="">
                    <img src="/themes//Public/Mobile/image/slice/weixin1.png">
                    <p style="font-size:14px;">微信快速登录</p>
                </a>
            </div>

            <div class="agree_do l-h-06rem" style="font-size:14px;">
                登录即表示您同意<a class="showmb">《番茄医学用户服务协议》</a>
            </div>
        </div>

    </div>
</section>




<style type="text/css">
    .maskBox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,.8);
        z-index: 1500;
    }
    .hide {
        display: none;
    }
    .reserveCtt {
        height: 90% !important;
        width: calc(100% - .6rem);
    }
    .maskCenter {
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%,-50%);
        transform: translate(-50%,-50%);
    }
    .reserveCtt h2 {
        padding: .2rem;
        background-color: #f8f8f8;
        font-size: 14px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }
    .reserveCtt a {
        display: block;
        border-top: 1px solid #eee;
        padding: .2rem;
        text-align: center;
        color: #666;
        background-color: #f8f8f8;
    }
</style>
<div class="maskBox hide" style="display:none;">
    <div class="maskCenter reserveCtt w-section flex_column back-fff">
        <h2>用户服务协议</h2>
        <div class="w-flexitem1 w-sectionD">
            <div class="richText w-padding03"><?php echo ($content); ?></div>
        </div>
        <a   id="read" href="javascript:;">阅读并同意</a>
        <a   class="qxBtn"   href="javascript:;">取消</a>
    </div>
</div>



</body>
</html>
<script type="text/javascript" src="/themes//Public/Mobile/lib/jquery.1.11.3.min.js" ></script>
<script type="text/javascript" src="/themes//Public/Mobile/lib/swiper.min.js"></script>
<script type="text/javascript" src="/themes//Public/Mobile/lib/vue.min.js"></script>
<script type="text/javascript" src="/themes//Public/Mobile/lib/con_js.6.23.js"></script>
<script type="text/javascript" src="/themes//Public/Mobile/lib/dropload.min.js"></script>
<script type="text/javascript" src="/themes//Public/Mobile/lib/vue.js"></script>

<script type="text/javascript">

  $(function () {
      $(".wx_pic_upload_single").click(function()
      {
          var target = $(this).attr('target');
          var module = $(this).attr('module');
          var action = $(this).attr("action");
          if(!action){
              action="";
          }
          location.href="/upload_app_single_img?target="+target+"&module="+module+"&action="+action;
      });
  })

  //多图上传成功后的处理函数
  function upload_success(pic_list,target,obj,temp,thumb_list){
      if(!obj){
          obj=$("#wx_pic_upload");
      }
      $.each(pic_list,function(i,item){
          var str="";
          if(temp){

              var temp_str=temp;
              str += temp_str.replace(/\[target\]/g,target).replace(/\[img\]/g,item);
          }else{
              str += "<div  class='w-position "+target+"'><img ";
              str += " src='"+thumb_list[i]+"' ><input type='hidden' name='"+target+"[]' value='"+item+"'>";
              str += "<b class='imgDelete2'></b></div>";
          }
          obj.before(str);
      });

      var current_count=obj.parent().find('.'+target).length;
      var count=obj.attr("count");

      if(count<=current_count){
          obj.hide();
      }
      var cookie_path=obj.attr("cookie_path");

      if(cookie_path){
          var all_pic_list=[];
          $.each(obj.parent().find('.'+target+' input'),function(i,item){
              all_pic_list.push($(item).val());
          });
          $.ajax({
              type: "GET",
              url: cookie_path+"?t="+new Date().getTime(),
              data: {img:all_pic_list},
              dataType: "json",
              success: function (res)
              {
              },
          });
      }
  }

  function back() {
      WebViewJavascriptBridge.back();
  }

</script>
<script type="text/javascript">
    var tiaourl="<?php echo (session('tiaourl')); ?>";
    tiaourl=tiaourl?tiaourl:'/Member/index';
   // alert(tiaourl)
    $('#read').click(function () {
        var checked = $("input[type='checkbox']").is(':checked');
        if(checked == false)
        {
            $("input[type='checkbox']").prop('checked','checked');
        }
        $('.maskBox').hide();
    });

    $('.showmb').click(function () {
        $('.maskBox').show();
    });

    $('.qxBtn').click(function () {
        $('.maskBox').hide();
    });



    $('.wx_login').click(function () {
        //var domain = window.location.host;
       // domain="http://"+domain+"/Index/mnologin";
       // alert(domain);  alert(tiaourl);
        WebViewJavascriptBridge.wx_auth();
    })




    function wx_auth_back(app_openid,nickname,avatar,prov,city)
    {
        var domain = "/Member/index";
        $.ajax({
            type: 'POST',
            url: '/Public/app_wx_login',
            data: {app_openid:app_openid,nickname:nickname,avatar:avatar,prov:prov,city:city},
            success: function (e) {

                if(e.status == 1)
                {

                    localStorage.app_openid = app_openid;

                    if(e.info == 'nomobile')
                    {
                        location.href = '/Member/bindPhone';
                    }else if(e.info == 'ok')
                    {
                        if(domain==tiaourl){
                             location.href = tiaourl;
                        }else{
                            WebViewJavascriptBridge.reloadAction();
                            WebViewJavascriptBridge.back();
                        }
                       // location.href = tiaourl;

                    }


                }
            },
        });
    }
</script>