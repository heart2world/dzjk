<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <title>个人中心</title>
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
<style>
    .iconJi{
        line-height: .33rem;
    }
    .orangeBall{
        margin-top: -2px;
    }
</style>
<body>
<section class="flex_column w-height100 w-bgcolorf7f7f7">
    <div class="w-flexitem1 w-section">



        <div class="personalBox2 w-marginBottom01">
            <h4 class="w-textalignR"><a href="/Member/memberInfo" class="iconSet"></a></h4>
            <div class="w-flex w-paddingLeftRight03">
                <a href="/Member/memberInfo">
                    <img src="<?php echo ($info['avatar']); ?>" alt="" class="header3 w-marginRight02"/>
                </a>
                <div class="w-flexitem1 w-overflowH" style="padding-top: .1rem">
                    <h4 class="w-height04 w-onlyline w-font0">
                        <em class="w-font16 w-colorFFF w-verticalMiddle w-onlyline w-inlineblock" style="max-width: 50%">
                            <?php echo ($info['nickname']); ?></em>
                        <a href="/Member/memberInte">
                            <span class="iconJi w-marginLeft02 w-font13"><?php echo ($info['integral']); ?></span>
                        </a>
                    </h4>
                    <?php if($info['mobile'] != null): ?><h4 class="w-height04 w-font13 w-colorFFF w-marginBottom02"><?php echo ($info['mobile']); ?></h4>
                        <?php else: ?>
                        <a href="/Member/bindPhone"><h4 class="w-height04 w-font13 w-colorFFF w-marginBottom02">绑定手机号</h4></a><?php endif; ?>

                    <div class="w-font0 w-overflowH">
                        <?php if($lablist != null): if(is_array($lablist)): $i = 0; $__LIST__ = $lablist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lab): $mod = ($i % 2 );++$i;?><span class="w-btn3"><?php echo ($lab["name"]); ?></span><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        <!--<span class="w-btn3">糖尿病</span>-->
                        <!--<span class="w-btn3">消化不良</span>-->
                    </div>
                </div>
            </div>
        </div>


        <div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">
            <a href="/Message/index">
                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconMyNews w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">我的消息</em>
                        <?php if($messages > 0): ?><span class="orangeBall"><?php echo ($messages); ?></span><?php endif; ?>
                    </h4>
                </div>
            </a>
        </div>

        <div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">
            <a href="/Member/memberColl">
                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconMyCollect w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">我的收藏</em>
                    </h4>
                </div>
            </a>
            <a href="/Member/follDoctor">
                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconLove w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">已关注医生</em>
                    </h4>
                </div>
            </a>
        </div>
        <div class="w-paddingLeftRight02 w-bgcolorFFF w-marginBottom01">


            <?php if($info['status'] == 1): ?><a href="/Member/agre/isbdphone/<?php echo ($info['isbdphone']); ?>">

                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconMyDocter w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">我是医生</em>
                    </h4>

                    <em class="w-floatright w-height05 w-font14 w-color44D397">
                        <?php if($info["showSQ"] == 1): ?>审核中<?php endif; ?>
                    </em>
                    <!--<a href="/Member/applyAuthFir">-->
                    <!--<em class="w-floatright w-height05 w-font14 w-color44D397">申请认证</em>-->
                    <!--</a>-->

                    <!--<a href="/Member/applyAuth">-->
                    <!--<em class="w-floatright w-height05 w-font14 w-color44D397">审核中</em>-->
                    <!--</a>-->
                </div>
            </a><?php endif; ?>

            <?php if($info["status"] == 0): ?><div class="rightLine w-font0 w-borderBeee">
                        <b class="iconMyDocter w-marginRight02"></b>
                        <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                            <em class="w-verticalMiddle">我是医生</em>
                        </h4>

                        <em class="w-floatright w-height05 w-font14 w-color44D397">
                            <?php if($info["showSQ"] == 1): ?>审核中<?php endif; ?>
                        </em>
                    </div><?php endif; ?>

            <a href="/Index/help">
                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconHelpCenter w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">帮助中心</em>
                    </h4>
                </div>
            </a>
            <a href="/Index/about">
                <div class="rightLine w-font0 w-borderBeee">
                    <b class="iconLookIm w-marginRight02"></b>
                    <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                        <em class="w-verticalMiddle">关于我们</em>
                    </h4>
                </div>
            </a>
            <div class="rightLine w-font0 w-borderBeee exitbtn">
                <b class="iconLogOut w-marginRight02"></b>
                <h4 class="w-inlineblock w-verticalMiddle w-height05 w-font14 w-color000">
                    <em  class="w-verticalMiddle ">退出登录</em>
                </h4>
            </div>
        </div>
    </div>

    <footer class="footer w-flex">
        <label for="home" class="w-flexitem1">
            <a href="/index/index">
                <input type="radio" name="footer" id="home"/>
                <span>首页</span>
            </a>
        </label>
        <label for="doctor" class="w-flexitem1">
            <a href="/DoctorSpec/index">
                <input type="radio" name="footer" id="doctor"/>
                <span>医生专栏</span>
            </a>
        </label>
        <label for="im" class="w-flexitem1">
            <?php  if($member_id > 0) { ?>


            <?php }else{ ?>
            <a href="/Index/login">
                <?php } ?>
                <input type="radio" name="footer" id="im" checked/>
                <span>我的</span>

        </label>
    </footer>

</section>


<!--退出登录蒙版-->

<div class="logCutBox" style="display: none;">
    <div class="logCut">
        <a  id="exitFun" class="w-block w-width100 w-height08 w-textalignC w-font14 w-color000 w-borderBeee w-bgcolorFFF">退出登录</a>
        <a onclick="$('.logCutBox').hide();" href="javascript:;" class="w-block w-width100 w-height08 w-textalignC w-font14 w-color666 w-bgcolorFFF">取消</a>
    </div>
</div>
</body>


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
</html>

<script type="text/javascript">

    $(function()
    {
        $('.exitbtn').click(function () {
            $('.logCutBox').show();
        });

        $('#exitFun').click(function ()
        {

            localStorage.app_openid = '';

            $.ajax({
                type: 'POST',
                url: '/Public/exitAction',
                data: {},
                success: function(e)
                {
                    if(e.status == 1)
                    {
                        // go_alert2(e.info.url28);
                        // go_alert2(e.info);
                        $('.logCutBox').hide();
                        location.href= e.info.url;
                    }else
                    {
                        go_alert2(e.info);
                    }
                },
            });
        });
    })
</script>