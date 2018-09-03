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
<style type="text/css">
    .read{
        line-height: 0.8rem;
        width: 100%;
        color: #fff;
        background-color: #41d397;
        font-size: 16px;
    }
</style>
<section class="flex_column w-height100">
    <div class="w-bgcolor44D397">
        <?php if(sp_is_ios()): ?><a onclick="back()" class="headLeft"></a>
    <?php else: ?>
    <a onclick="back()" class="headLeft"></a>
    <!--<a href="javascript:history.back();"  class="headLeft" ></a>--><?php endif; ?>


        <a href="javascritp:;" class="headRight"></a>
        <span class="headTit">医生认证</span>
    </div>
    <div class="w-flexitem1 w-section">
        <div class="massage_page pad15">
            <div class="edit_area ">
                <p><?php echo ($content); ?></p>
            </div>
        </div>
    </div>

    <?php if($showSQ == 1): ?><div class="dele_btn">
            <button  id="showM" class="read">查看申请</button>
        </div>
        <?php else: ?>
        <div class="dele_btn">
            <button id="jkAdd"   class="read">申请认证</button>
        </div><?php endif; ?>

</section>
</body>
</html>
<input type="hidden" class="isbd" value="<?php echo ($_GET['isbdphone']); ?>">
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
        $('#showM').click(function ()
        {
            location.href = "/Member/applyAuth";
        });


        $('#jkAdd').click(function ()
        {
            var isbd = $('.isbd').val();
            if(isbd == 1)
            {
                location.href = "/Member/applyAuthFir";
            }else
            {
                location.href = "/Member/bindPhone";
            }
        });
    });
</script>