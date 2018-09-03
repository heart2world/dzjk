<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <title>医生主页</title>
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
    #datalist>a{
        display: block;
    }
    .hide111{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 2rem;
        display: inline-block;
        vertical-align: middle;
    }
     .w-onlyline em.w-marginRight02{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 2rem;
        display: inline-block;
        vertical-align: middle;
    }
    .zixun{
    	padding: .1rem;
    	background: #41d397;
    	border-radius: 5px;
    	color: #fff;
    	position: absolute;
    	right: 0;
    	bottom: 0;
    }
</style>
<body>
<section class="flex_column w-height100 w-bgcolorf7f7f7">
    <div class="w-bgcolor44D397">
    <?php if(sp_is_ios()): ?><a onclick="back()" class="headLeft"></a>
    <?php else: ?>
    <a onclick="back()" class="headLeft"></a>
    <!--<a href="javascript:history.back();"  class="headLeft" ></a>--><?php endif; ?>


    <a href="javascritp:;" class="headRight"></a>

        <?php if($memberInfo['truename'] != '官方发布'): ?><span class="headTit">医生主页</span>
            <?php else: ?>
            <span class="headTit">平台主页</span><?php endif; ?>
    </div>
    <div class="w-flexitem1 w-section w-sectionD">
        <div class="w-paddingLeftRight02 w-marginBottom01 w-bgcolorFFF">
            <div class="w-paddingTopBottom02 w-borderBeee">
                <div class="w-flex w-marginBottom02" style="position: relative;">

                    <?php if($memberInfo['truename'] != '官方发布'): ?><div class="header2 w-marginRight02" style="background-image: url(<?php echo ($memberInfo["avatar"]); ?>);"><b></b></div>
                        <?php else: ?>
                        <div class="header2 w-marginRight02" style="background-image: url(/logo.png);"><b></b></div><?php endif; ?>
                    <div class="w-flexitem1 w-paddingTopBottom01">
                        <h4 class="w-font14 w-color000">

                            <?php if($memberInfo['truename'] != '官方发布'): ?><em class="w-verticalMiddle hide111"><?php echo ($memberInfo["nickname"]); ?></em>
                                <?php else: ?>
                                <em class="w-verticalMiddle newName">官方发布</em><?php endif; ?>


                            <?php if($memberInfo['zy'] != null): ?><span class="w-btn1 w-marginRight02"><?php echo ($memberInfo["zy"]); ?></span><?php endif; ?>



                        </h4>
                        <span class="w-block w-height04 w-font12 w-color999 w-onlyline"><?php echo ($memberInfo["hosp"]); echo ($memberInfo["zw"]); ?></span>
                    </div>


                    <?php if($memberInfo["showgz"] == 1): ?><label  class="iconCollect w-paddingTopBottom01" style="padding: 0;">
                        <?php if($memberInfo["isgz"] == 1): ?><input name="checkbox" type="checkbox" disabled checked >
                            <?php else: ?>
                            <input name="checkbox" type="checkbox" disabled ><?php endif; ?>
                        <span id="gzbtn"></span>
                        </label><?php endif; ?>
                    <div class="zixun">向他咨询</div>
                </div>
                <h4 class=" w-line04 w-font14 w-color333"><?php echo ($memberInfo["grjs"]); ?></h4>
            </div>
            <div class="w-paddingTopBottom02 w-flex">
                <span class="w-flexitem1 w-height04 w-textalignC w-font14 w-color333 w-onlyline">粉丝：<?php echo ($memberInfo["fss"]); ?></span>
                <b class="grayLine"></b>
                <span class="w-flexitem1 w-height04 w-textalignC w-font14 w-color333 w-onlyline">动态：<?php echo ($memberInfo["dynas"]); ?></span>
                <b class="grayLine"></b>
                <span class="w-flexitem1 w-height04 w-textalignC w-font14 w-color333 w-onlyline">文章：<?php echo ($memberInfo["wzs"]); ?></span>
                <b class="grayLine"></b>
                <span class="w-flexitem1 w-height04 w-textalignC w-font14 w-color333 w-onlyline">回答数：<?php echo ($memberInfo["wzs"]); ?></span>
            </div>
        </div>
        <div class="w-flex navBox">
            <a href="javascript:;" data-id="all" class="w-flexitem1 navBoxAce">全部</a>
            <a href="javascript:;" data-id="wz" class="w-flexitem1">文章</a>
            <a href="javascript:;" data-id="dt" class="w-flexitem1">动态</a>
        </div>
        <div id="datalist" >


        </div>
        <div style="display: none;" class="noData">暂无数据</div>
    </div>

</section>
</body>


<!--分享四按钮弹框-->
<div class="shareBox" style="display: none;">
    <div class="share">
        <div class="w-borderBeee w-paddingTopBottom02">
            <h4 class="w-height07 w-marginBottom02 w-textalignC w-font15 w-color333">分享</h4>
            <div class="w-flex">
                <a href="javascript:;" id="qq" class="w-flexitem1 w-textalignC">
                    <b class="iconQQ w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">QQ好友</h4>
                </a>
                <a href="javascript:;" id="wx"  class="w-flexitem1 w-textalignC">
                    <b class="iconWechat w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">微信好友</h4>
                </a>
                <a href="javascript:;" id="pyq"  class="w-flexitem1 w-textalignC">
                    <b class="iconFriend w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">朋友圈</h4>
                </a>
                <a href="javascript:;" id="qzone"  class="w-flexitem1 w-textalignC">
                    <b class="iconQzone w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">QQ空间</h4>
                </a>
            </div>
        </div>
        <a href="javascript:;" class="w-block w-height08 w-font15 w-color333 w-textalignC" onclick="$('.shareBox').hide()">取消</a>
    </div>

</div>


<input type="hidden" class="status" value="all">
<input type="hidden" class="page" value="1">
<input type="hidden" class="id" value="<?php echo ($_GET['id']); ?>">


<input type="hidden" class="ids" value="">
<input type="hidden" class="title" value="">
<input type="hidden" class="isdj" value="<?php echo ($isdj); ?>">

<input type="hidden" class="member_id" value="<?php echo ($member_id); ?>">

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
<script>

    function shareFunc(e)
    {
        var title = $(e).attr('data-title');
        $('.title').val(title);
        var id = $(e).attr('data-id');
        $('.ids').val(id);

        var member_id = $('.member_id').val();
        if(member_id)
        {
            if(isdj == 0)
            {
                go_alert2('已被冻结，不能执行此操作!');
            }else
            {
                $('.shareBox').show();
            }
        }else
        {
            location.href = '/Index/login';
        }
    }

    function shareFuns(type) {
        var title = $('.title').val();
        var id = $('.ids').val();
        var url = "http://dzjk.server.ehecd.com/Article/infoDyna/id/"+id;
        WebViewJavascriptBridge.shareAction(title,id,url,type,5);
    }

    function shareBack(id) {
        $.ajax({
            type: 'POST',
            url: '/Public/shareBack',
            data: {id:id},
            success: function () {

            },
        });
    }

    $('#qq').click(function () {
        shareFuns('qq');
    });
    $('#wx').click(function () {
        shareFuns('wx');

    });
    $('#pyq').click(function () {
        shareFuns('pyq');
    });
    $('#qzone').click(function () {
        shareFuns('qzone');
    });

    urlJump();




    //关注
    $('#gzbtn').click(function ()
    {
        var id = $('.id').val();
        var isgz = $('.isgz').val();
            $.ajax({
                type: 'POST',
                url: '/Article/folloAction',
                data: {id:id,types:'zjgz'},
                success: function (e) {
                    if(e.status == 1)
                    {

                        if(e.info == '关注成功')
                        {
                            $("input[name='checkbox']").prop("checked","true");

                        }else if(e.info == '取消关注成功')
                        {
                            $("input[name='checkbox']").removeAttr("checked","true");
                        }

                        go_alert2(e.info);
                    }else
                    {
                        go_alert2(e.info);
                        if(e.info =='请先登录')
                        {
                            location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                            //location.href ="/Index/login";
                        }
                    }
                },
            });

    })


    //nav切换
    $('.navBox a').click(function(){
        $(this).addClass('navBoxAce').siblings('a').removeClass('navBoxAce');
        $('.status').val($(this).attr('data-id'));
        $('.page').val(1);
        urlJump();
    });


    function urlJump()
    {
        var status = $('.status').val();
        var id = $('.id').val();
        $.ajax({
            type: 'POST',
            url: '/Doctor/index',
            data: {status:status,p:1,id:id},
            success: function (e) {
                if(e)
                {
                    $('.noData').hide();
                    $('#datalist').html(e);
                }else{
                    $('.noData').show();
                    $('#datalist').html('');
                }
                //判断超过三行“全文” 显示隐藏
                setTimeout(function () {
                    $('.threeLine').each(function () {
                        if ($(this).height() > 60) {
                            $(this).css('height', '60px');
                            $(this).children('.threeMark').show();
                        } else {
                            $(this).css('height', 'auto');
                            $(this).children('.threeMark').hide();
                        }
                    });
                    $('.threeMark').click(function (e) {
                        if ($(this).parents('.threeLine').height() == 60) {
                            $(this).parents('.threeLine').css('height', 'auto');
                            $(this).css('position', 'static');
                            $(this).children('em').text('收起');
                        } else {
                            $(this).parents('.threeLine').css('height', '60px');
                            $(this).css('position', 'absolute');
                            $(this).children('em').text('全文');
                        };
                        e.stopPropagation();
                    });
                }, 0)
            },
        });
    }

    //点赞
    function giveAction(id,el) {
        $.ajax({
            type: 'POST',
            url: '/Article/giveAction',
            data: {id:id},
            success: function (e) {
                if(e.status == 1)
                {
                    $('.isz').val(1);
                    $(el).parent().children('span').html(parseInt($(el).parent().children('span').html())+1);
                    $(el).prop('disabled','disabled');
                    $(el).prop("onclick",null).off("click");
                    $('.imputBtn').attr("disabled", true);
                    go_alert2(e.info);
                }else
                {
                    go_alert2(e.info);
                    if(e.info =='请先登录')
                    {
                        location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                     //   location.href ="/Index/login";
                    }
                }
            },
        });
    }

    
    $(".w-sectionD").on('scroll',function()
    {
        var nScrollHight = 0;
        var nScrollTop = 0;
        var nDivHight = $(".w-sectionD").height();
        nScrollHight = $(this)[0].scrollHeight;
        nScrollTop = $(this)[0].scrollTop;
        var paddingBottom = parseInt($(this).css('padding-bottom')), paddingTop = parseInt($(this).css('padding-top'));
        if (nScrollTop + paddingBottom + paddingTop + nDivHight >= nScrollHight - 10) {
            urlJumpMore();
        }
    });

    var isget = true;
    var page = 1;
    function urlJumpMore()
    {
        if(isget)
        {
            isget = false;
            page = parseInt(page) +1;
            var status = $('.status').val();
            var id = $('.id').val();
            $.ajax({
                type: 'POST',
                url: '/Doctor/index',
                data: {status:status,p:page,id:id},
                success: function (e) {
                    if(e)
                    {
                        isget = true;
                        $('#datalist').append(e);
                    }else{
                        $('#datalist').append('<li style="display: block;text-align:center;" class="loadMore">没有更多了...</li>');
                        isget = false;
                    }
                },
            });
        }
    }
</script>
</html>