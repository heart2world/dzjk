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
<body>
<section class="flex_column w-height100">
    <div class="w-bgcolor44D397">
        <?php if(sp_is_ios()): ?><a onclick="back()" class="headLeft"></a>
    <?php else: ?>
    <a onclick="back()" class="headLeft"></a>
    <!--<a href="javascript:history.back();"  class="headLeft" ></a>--><?php endif; ?>


        <a href="/Member/index" class="headRight">跳过</a>
        <span class="headTit">番茄医学</span>
    </div>

    <div class="w-flexitem1 w-section">
        <form class="login_page  text-center">
            <div class="login_default">

                <a href="javascript:;">
                    <img src="/logo.png">
                    <p>绑定手机号</p>
                </a>
            </div>
            <div class="login_form">
                <div class="form_item flex_dom flex_item_mid ">
                    <img src="/themes//Public/Mobile/image/slice/tag1.png"/><input type="tel" placeholder="+86(手机号)" class="phone flex_1 m-l-15" />
                </div>
                <div class="form_item flex_dom flex_item_mid">
                    <img src="/themes//Public/Mobile/image/slice/tag2.png"/><input type="text" placeholder="短信验证码" class="code flex_1 p_both15" />
                    <button type="button" class="indentCode">获取验证码</button>
                </div>
            </div>
            <div class="submit_btn m-t-35">
                <button type="button" class="saveBtn">提交</button>
            </div>
            <!--<label class="agree_do text-left l-h-06rem">-->
                <!--<input checked type="checkbox"/><span></span> 已阅读并同意-->
                <!--<a class="showmb"  >《xx用户服务协议》</a>-->
            <!--</label>-->

        </form>
    </div>



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






</section>
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
    $(function () {

        $('.showmb').click(function () {
            $('.maskBox').show();
        });

        $('.qxBtn').click(function () {
            $('.maskBox').hide();
        });
        $('#read').click(function () {
            var checked = $("input[type='checkbox']").is(':checked');
            if(checked == false)
            {
                $("input[type='checkbox']").prop('checked','checked');
            }

            $('.maskBox').hide();
        });



        $('.indentCode').click(function(){
            var phone = $('.phone').val().trim();
            if(!phone)
            {
                go_alert2('手机号码不能为空');   return false;
            }else if(phone.length != 11)
            {
                go_alert2('手机号格式错误');   return false;
            }
            $.ajax({
                type: 'POST',
                url: '/Mobile/Member/getCode',
                data: {phone:phone},
                success: function (e) {
                    if(e.status == 1)
                    {
                        var that = $('.indentCode');
                        var time = 60;
                        that.text(time + 's').attr('disabled','disabled');
                        var getCode = setInterval(function(){
                            if(time == 1){
                                clearInterval(getCode);
                                that.text('验证码').removeAttr('disabled');
                            }else{
                                time--;
                                that.text(time + 's');
                            }
                        },1000)
                    }else
                    {
                        go_alert2(e.info);
                    }
                },
            });

        })






        $('.saveBtn').click(function ()
        {
            // var checked = $("input[type='checkbox']").is(':checked');
            // if(checked == false)
            // {
            //     go_alert2('请同意协议');
            // }else {

                var phone = $('.phone').val().trim();
                var code = $('.code').val().trim();
                if (!phone) {
                    go_alert2('手机号码不能为空');
                    return false;
                } else if (phone.length != 11) {
                    go_alert2('手机号格式错误');
                    return false;
                }
                if (!code) {
                    go_alert2('验证码不能为空');
                    return false;
                } else if (code.length != 6) {
                    go_alert2('验证码格式错误');
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    url: '/Member/bindPhone',
                    data: {phone: phone, code: code},
                    success: function (e) {
                        if (e.status == 1) {
                            go_alert2(e.info);
                            location.href = "/Member/index";
                        } else {
                            go_alert2(e.info);
                        }
                    },
                });
            // }
        });
    });
</script>