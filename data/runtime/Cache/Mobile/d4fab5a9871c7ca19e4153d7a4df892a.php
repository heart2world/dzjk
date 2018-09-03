<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <title>文章详情</title>
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
    .cancleBtn{
        background-color:#deebf7;
        width:1.2rem;
        height:.45rem;
        line-height:.45rem;
        color:#333;
        display:inline-block;
        margin-left:.1rem;
        border-radius:.04rem;
    }
</style>
<body>
<section class="flex_column w-height100 w-bgcolorf7f7f7">
    <?php if(!is_weixin()){ ?>
    <div class="w-bgcolor44D397">
        <?php if(sp_is_ios()): ?><a onclick="back()" class="headLeft"></a>
    <?php else: ?>
    <a onclick="back()" class="headLeft"></a>
    <!--<a href="javascript:history.back();"  class="headLeft" ></a>--><?php endif; ?>


        <a href="javascritp:;" class="headRight"></a>
        <span class="headTit">文章详情</span>
    </div>
    <?php } ?>
    <div class="w-flexitem1 w-section">
        <div class="w-padding02 w-bgcolorFFF w-marginBottom01">
            <h4 class="w-line04 w-marginBottom01">

                <?php if(data.isyc == 1): ?><em class="w-btn1">转</em><?php endif; ?>
                <em class="w-font14 w-color333 w-verticalMiddle" id="arttitle"><?php echo ($data["title"]); ?></em>
            </h4>

            <div class="w-flex w-marginBottom01">
                <a href="/Doctor/index/id/<?php echo ($data["author"]); ?>">
                <?php if($data['nickname'] == '官方发布'): ?><img src="/logo.png" class="header1 w-marginRight02" alt="">

                    <?php else: ?>

                    <img src="<?php echo ($data["avatar"]); ?>" class="header1 w-marginRight02" alt=""><?php endif; ?>
                </a>
                <div class="w-flexitem1">
                    <?php if($data['nickname'] == '官方发布'): ?><h4 class="w-height03 w-font14 w-color000 w-onlyline">官方发布</h4>
                        <span class="w-block w-height03 w-font12 w-color999 w-onlyline">番茄医学官方平台</span>
                        <?php else: ?>
                    <h4 class="w-height03 w-font14 w-color000 w-onlyline" style="width:2rem;"><?php echo ($data["nickname"]); ?></h4>
                    <span class="w-block w-height03 w-font12 w-color999 w-onlyline"><?php echo ($data["hosp"]); ?></span><?php endif; ?>
                </div>


                <?php if($data["showgz"] == 1): ?><label class="iconCollect">

                        <?php if($data["isgz"] == 1): ?><input disabled name="checkbox" checked type="checkbox"  />
                            <?php else: ?>
                            <input disabled name="checkbox" type="checkbox"   /><?php endif; ?>
                        <span style="display: inline-block !important;" id="gzbtn"></span>
                        <?php if($data["isgz"] == 1): ?><button id="gzbtn2" type="button" class="btn cancleBtn btn-small btn-primary">取消关注</button><?php endif; ?>
                    </label><?php endif; ?>


            </div>
            <div id="content" class="w-font14 w-line04 w-color333 w-imgBox">
                <?php echo ($data["content"]); ?>

            </div>

            <div id="more" style="display: none;text-align:center;">
                <a id="ios" style="width: 1.8rem;display:inline-block;height: .45rem;line-height: .45rem;border-radius: .06rem;background-color: #f7f7f7; color: #44d397; text-decoration: underline;" href="https://itunes.apple.com/cn/app/%E7%95%AA%E8%8C%84%E5%8C%BB%E5%AD%A6/id1374789340?mt=8">查看更多</a>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <a id="andron" style="width: 1.8rem;display:inline-block;height: .45rem;line-height: .45rem;border-radius: .06rem;background-color: #f7f7f7; color: #44d397; text-decoration: underline;" href="http://android.myapp.com/myapp/detail.htm?apkName=com.ehecd.dazhongjiankang&ADTAG=mobile">查看更多</a>
            </div>
            <div class="w-line04">
                <em class="w-btn1"><?php echo ($data["label"]); ?></em>

                <?php if($data['iszf'] == 1): ?><span class="zslsx" style="color: #999">喜欢这篇?</span><a data-id="<?php echo ($data["id"]); ?>" style="text-decoration:underline;" class="zfBtn zslsx xhLine1 w-font12">转发到我的文章</a>
                    <?php else: ?>
                    <?php if($data['iszx'] == 0): ?><a href="tel:<?php echo ($tel); ?>" class="xhLine1 w-font12"><?php echo ($zxinto); ?></a><?php endif; endif; ?>
            </div>

        </div>


            <?php  if($data['ad']['pic']){?>
        <div class="w-padding02 w-bgcolorFFF w-marginBottom01">
            <div class="w-font14 w-line04 w-color333 w-imgBox">
                   <?php if($data['ad']['links'] != null): ?><a href="<?php echo $data['ad']['links'];?>">
                       <?php else: ?>
                           <a href="javascript:void(0)" ><?php endif; ?>

                <?php echo $data['ad']['title'];?>
                    <img src=" <?php echo $data['ad']['pic'];?>" alt=""/>
                    </a>
            </div>
        </div>
        <?php }?>

        <h4 class="w-paddingLeftRight02 flex_x_center w-height06 w-color333 w-font14">
            <span class="plsBtn">评论(<?php echo ($data["pls"]); ?>)</span>
            <span class="ztest"><?php echo ($data["dzs"]); ?>赞</span>
            <input type="hidden" class="ydzs" value="<?php echo ($data["dzs"]); ?>">
        </h4>
        <div>

        <div id="datalist">


        </div>
            <div style="display: none;" class="noData w-bgcolorFFF">暂无评论</div>
            <!-- 暂无数据图-->
        </div>
        <h4 class="w-height07 w-textalignC w-font14" id="moreData" >
            <!--点击查看更多-->
            <a href="javascript:;" style="display: none;" class="showmore xhLine1">点击查看更多</a>
        </h4>
    </div>
    <div class="w-borderTeee w-bgcolorFFF w-font0" id="myzf" style="padding: .15rem .2rem">
        <div class="talkBox2">
            <input type="text" class="w-placeholder-colorccc plBtn" placeholder="说点啥"/>
        </div>

        <label  class="iconZan w-marginLeft02">
            <input  disabled  class="imputBtn" type="checkbox" <?php if($data['isz'] == 1): ?>checked<?php endif; ?>/>
            <span id="zan4" ></span>
        </label>

        <label  class="iconCollect2 w-marginLeft02">
            <input class="collBtnSCL"  disabled type="checkbox"   <?php if($data['issc'] == 1): ?>checked<?php endif; ?>/>
            <span id="collBtn"></span>
        </label>

        <a  id="shareBoxBtn" class="iconShare w-marginLeft02"></a>


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
                <a href="javascript:;"  id="wx"  class="w-flexitem1 w-textalignC">
                    <b class="iconWechat w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">微信好友</h4>
                </a>
                <a href="javascript:;"  id="pyq"  class="w-flexitem1 w-textalignC">
                    <b class="iconFriend w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">朋友圈</h4>
                </a>
                <a href="javascript:;"  id="qzone"  class="w-flexitem1 w-textalignC">
                    <b class="iconQzone w-marginBottom01"></b>
                    <h4 class="w-height03 w-font13 w-color666">QQ空间</h4>
                </a>
            </div>
        </div>
        <a href="javascript:;" class="w-block w-height08 w-font15 w-color333 w-textalignC" onclick="$('.shareBox').hide()">取消</a>
    </div>
</div>



<input type="hidden" class="isz" value="<?php echo ($data['isz']); ?>">
<input type="hidden" class="issc" value="<?php echo ($data['issc']); ?>">

<input type="hidden" class="id" value="<?php echo ($_GET['id']); ?>">
<input type="hidden" class="pls" value="<?php echo ($data["pls"]); ?>">

<input type="hidden" class="dzs" value="<?php echo ($data["dzs"]); ?>">
<input type="hidden" class="member_id" value="<?php echo ($member_id); ?>">

<input type="hidden" class="ids" value="<?php echo ($data["id"]); ?>">
<input type="hidden" class="title" value="<?php echo ($data["contentT"]); ?>">

<input type="hidden" class="isdj" value="<?php echo ($isdj); ?>">
<input type="hidden" class="mid" value="<?php echo ($member_id); ?>">

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
    //转发成功提示框直接调用go_alert2('转发成功')


    $('.zfBtn').click(function () {
        var id = $(this).attr('data-id');
        $.ajax({
            type: 'POST',
            url: '/Article/zfAction',
            data: {id:id},
            success: function (e)
            {
                if(e.status == 1)
                {
                    $('.zslsx').remove();
                    go_alert2(e.info);
                }else
                {
                    go_alert2(e.info);
                }
            },
        });

    });

    $('#shareBoxBtn').click(function () {
        var member_id = $('.member_id').val();
        var isdj = $('.isdj').val();

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
                location.href ="/Index/login?tiaourl="+encodeURI(location.href);
            }



    });
    /**
     * 图片压缩，默认同比例压缩
     * @param {Object} path
     *   pc端传入的路径可以为相对路径，但是在移动端上必须传入的路径是照相图片储存的绝对路径
     * @param {Object} obj
     *   obj 对象 有 width， height， quality(0-1)
     * @param {Object} callback
     *   回调函数有一个参数，base64的字符串数据
     */
    function dealImage(path, obj, callback){
        var img = new Image();
        img.src = path;
        img.onload = function(){
            var that = this;
            // 默认按比例压缩
            var w = that.width,
                    h = that.height,
                    scale = w / h;
            w = obj.width || w;
            h = obj.height || (w / scale);
            var quality = 0.7;  // 默认图片质量为0.7
            //生成canvas
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            // 创建属性节点
            var anw = document.createAttribute("width");
            anw.nodeValue = w;
            var anh = document.createAttribute("height");
            anh.nodeValue = h;
            canvas.setAttributeNode(anw);
            canvas.setAttributeNode(anh);
            ctx.drawImage(that, 0, 0, w, h);
            // 图像质量
            if(obj.quality && obj.quality <= 1 && obj.quality > 0){
                quality = obj.quality;
            }
            // quality值越小，所绘制出的图像越模糊
            var base64 = canvas.toDataURL('image/jpeg', quality );
            // 回调函数返回base64的值
            callback(base64);
        }
    }
    function shareFuns(type) {
        var img='<?php echo ($data["firstthum"]); ?>';
        $.ajax({
            type: 'POST',
            url: '/Article/thumimg',
            data: {imgpath:img},
            success: function (res) {
              //  alert(res.info);
//                alert(123);
                img=res.info;
                var title = $('.title').val();
                if(title.length>20){
                    title=title.slice(0,30);
                }
                var arttitle=$("#arttitle").text();
                var id = $('.ids').val();
                var url = "http://dzjk.server.ehecd.com/Article/info/id/"+id;
                var u = navigator.userAgent;
                var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
                if(isiOS){
                    url ="/Article/info/id/"+id;
                }
                WebViewJavascriptBridge.shareAction(arttitle,title,id,url,type,2,img);
            },
        });

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



    $(document).on('click',"#gzbtn2",function () {
        $('#gzbtn').click();
    });
    //关注
    $('#gzbtn').click(function ()
    {
        var id = $('.id').val();
            $.ajax({
                type: 'POST',
                url: '/Article/folloAction',
                data: {id:id},
                success: function (e) {
                    if(e.status == 1)
                    {
                        $('.imputBtn').attr("disabled", true);

                        if(e.info == '关注成功')
                        {
                            $("input[name='checkbox']").prop("checked","true");
                            var str = '<button id="gzbtn2" type="button" class="btn cancleBtn btn-small btn-primary">取消关注</button>';
                            $('#gzbtn').after(str);

                        }else if(e.info == '取消关注成功')
                        {
                            $("input[name='checkbox']").removeAttr("checked","true");
                            $('#gzbtn').next().remove();
                        }

                        go_alert2(e.info);
                    }else
                    {
                        go_alert2(e.info);
                        if(e.info =='请先登录')
                        {
                            location.href ="/Index/login?tiaourl="+encodeURI(location.href);

                        }
                    }
                },
            });

    })

    //收藏
    $('#collBtn').click(function ()
    {
        var id = $('.id').val();
        var issc = $('.issc').val();
        if(issc != 1)
        {

            var isdj = $('.isdj').val();
            var member_id = $('.member_id').val();
            if(member_id)
            {
                if(isdj == 0)
                {
                    go_alert2('已被冻结，不能执行此操作!');
                }else
                {
                    $.ajax({
                        type: 'POST',
                        url: '/Article/collAction',
                        data: {id:id},
                        success: function (e) {
                            if(e.status == 1)
                            {
                                // window.location.reload();
                                $('.issc').val(1);
                                $(".collBtnSCL").attr("checked",true);
                                $('.dzs').val(parseInt($('.dzs').val()) + 1);
                                $('.ztest').html($('.dzs').val()+'赞');
                                go_alert2(e.info);
                            }else
                            {
                                go_alert2(e.info);
                                if(e.info =='请先登录')
                                {
                                    location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                                }
                            }
                        },
                    });
                }

            }else
            {
                location.href ="/Index/login?tiaourl="+encodeURI(location.href);
            }




        }
    });
    //点赞
    $('#zan4').click(function ()
    {
        var id = $('.id').val();
        var isz = $('.isz').val();
        if(isz != 1)
        {
        $.ajax({
            type: 'POST',
            url: '/Article/giveAction',
            data: {id:id},
            success: function (e) {
                if(e.status == 1)
                {
                    // window.location.reload();
                    $('.isz').val(1);

                    $('.ztest').html(parseInt($('.ydzs').val()+1)+'赞');



                    $(".imputBtn").attr('checked','checked');
                    $('.imputBtn').attr("disabled", true);
                    go_alert2(e.info);
                }else
                {
                    go_alert2(e.info);
                    if(e.info =='请先登录')
                    {
                        location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                    }
                }
            },
        });
        }
    });

    //点赞  二级
    function zanerji(e, el) {
        $(el).prop('checked', false);
        var pid = e;
        var id = $('.id').val();
        $.ajax({
            type: 'POST',
            url: '/Article/giveActionEj',
            data: {id:id,pid:pid},
            success: function (e)
            {
                if(e.status == 1)
                {

                    var ids = parseInt($(el).parent().children('span').attr('data-id'));
                    $(el).parent().children('span').html(ids + 1);
                    $(el).prop('checked', true);
                    $(el).prop("onclick",null).off("click");
                    $(el).prop("disabled",'disabled');
                    go_alert2(e.info);
                }else
                {
                    go_alert2(e.info);
                    if(e.info =='请先登录')
                    {
                        location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                    }
                }
            },
        });

    }

    urlJump();
    function urlJump()
    {
        var id = $('.id').val();
        $.ajax({
            type: 'POST',
            url: '/Article/getArtiComment',
            data: {p:1,id:id,t:new Date().getTime()},
            success: function (e) {
                if(e.status == 1)
                {
                    if(e.info.showMore == 1)
                    {
                        $('.showmore').show();
                    }else
                        {

                        }
                    $('#datalist').html(e.info.info);

                }else{
                    $('.noData').show();
                    // $('#datalist').html('');
                }
            },
        });
    }


    $('#moreData').click(function () {
        urlJumpMore();
    });

    var isget = true;
    var page = 1;
    function urlJumpMore()
    {
        if(isget)
        {
            isget = false;
            page = parseInt(page) +1;
            var id = $('.id').val();
            $.ajax({
                type: 'POST',
                url: '/Article/getArtiComment',
                data: {p:page,id:id},
                success: function (e) {
                    if(e.status == 1)
                    {
                        isget = true;
                        $('#datalist').append(e.info.info);
                    }else{
                        $('#moreData').html('没有更多了');
                        isget = false;
                    }
                },
            });
        }
    }

    
    $('.plBtn').bind('keypress',function(event){
        if(event.keyCode == "13")
        {
            pl_submit();
        }
    });
    function pl_submit(){
    	var pltext = $('.plBtn').val();
        var id = $('.id').val();
        if(!pltext)
        {
            go_alert2('评论内容不能为空');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: '/Article/commentArti',
            data: {pltext:pltext,id:id},
            success: function (e) {
                if(e.status == 1)
                {

                    $('.pls').val(parseInt($('.pls').val()) + 1);
                    $('.plsBtn').html('评论'+($('.pls').val()));
                    go_alert2(e.info);
                    $('.plBtn').val('');
                    $('.noData').hide();
                    urlJump();
                }else
                {
                    go_alert2(e.info);
                    if(e.info =='请先登录')
                    {
                        location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                    }
                }
            },
        });
    }
    function isApp() {
        var u = navigator.userAgent;
        //var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        //var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        var app = u.indexOf('app') > -1//是否是移动端
        if (app) {
            return true;
        } else {
            return false;
        }
    }
    var allp=$("#content section section p");
    var tempength=Math.ceil(allp.length/3);
    var allp2=$("#content section p");
    var tempength2=Math.ceil(allp2.length/3);
    if(isApp()){
        //allp.css('dislay','block');
    }
    else {
        //allp.css('dislay','none');
        for (i=tempength;i<allp.length;i++){
            allp.eq(i).remove();
        }
        for (i2=tempength2;i2<allp2.length;i2++){
            allp2.eq(i2).remove();
        }
        $("#content").css('height','600px');
        $("#content").css('overflow','hidden');
        $("#more").css('display','block');
        $("#myzf").css('display','none');
        var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if(isiOS){
            $("#andron").css('display','none');
        }
        else  {

            $("#ios").css('display','none');

        }
    }
</script>
</html>