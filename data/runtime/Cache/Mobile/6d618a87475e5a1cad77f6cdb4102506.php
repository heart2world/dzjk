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
    .w-swiper-pagination {
        
        left: 50%;
    }
    #dataList>a{
        display: block;
    }
    .adv{
        padding: 0.05rem .05rem;
        display: inline-block;
        line-height: 1;
        background-color: #44D397;
        color: #fff;
        text-align: center;
        border-radius: .08rem;
        font-size: .18rem;
        margin-right: .1rem;
    }
    .w-btn1{
        border: 1px solid #999;
        color: #999;
    }
</style>
<body>
    <section class="flex_column w-height100 w-bgcolorf7f7f7">
        <div class="w-padding0102 w-bgcolor44D397">
            <div class="searchBox w-flex">
                <input onclick="$(this).blur();" type="text" class="kw w-height04 w-font14 w-color333 w-placeholder-colorccc w-flexitem1" placeholder="搜索文章、关键字" />
            </div>
        </div>
        <div class="w-paddingTopBottom01 w-borderBeee w-bgcolorFFF w-flex">
            <div class="w-flexitem1 seaechNav">
                <a data-id="0" class="seaechNavAce lablist">推荐</a>
                <?php if(is_array($labelListShow)): $i = 0; $__LIST__ = $labelListShow;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lists): $mod = ($i % 2 );++$i;?><a data-id="<?php echo ($lists["id"]); ?>" tar="<?php echo ($lists["id"]); ?>" class="lablist mylablist"><?php echo ($lists["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
            <a href="javascript:;" class="iconAdd"></a>
        </div>
        <div class="w-flexitem1 w-section w-sectionD" id="listContent">

            <div id="dataList">

            </div>
            <div style="display: none;" class="noData">暂无数据</div>
        </div>


        <footer class="footer w-flex">
            <label for="home" class="w-flexitem1">
                <a href="/Index/index">
                    <input type="radio" name="footer" id="home" checked />
                    <span class="indexbtn">首页</span>
                </a>
            </label>
            <label for="doctor" class="w-flexitem1">
                <a href="/DoctorSpec/index">
                    <input type="radio" name="footer" id="doctor" />
                    <span>医生专栏</span>
                </a>
            </label>
            <label for="im" class="w-flexitem1">

                <?php  if($member_id > 0) { ?>

                <a href="/Member/index">
                    <?php }else{ ?>
                    <a href="/Index/login">
                        <?php } ?>

                        <input type="radio" name="footer" id="im" />
                        <span>我的</span>
                    </a>
            </label>
        </footer>
    </section>


    <!--弹出框样式-->
    <div class="markBox" style="display: none">
        <div class="mark">
            <div class="markText">
                <span>
                    <h4 class="w-height04 w-font15 w-color000 w-textalignC">获取位置</h4>
                    健康咨询想要获取您的位置<br />是否允许？
                </span>
            </div>
            <div class="w-flex">
                <a onclick="$('.markBox').hide()" href="javascript:;" class="w-flexitem1 w-height08 w-font14 w-color666 w-textalignC" style="border-right: 1px solid #eee">不允许</a>
                <a onclick="$('.markBox').hide()" href="javascript:;" class="w-flexitem1 w-height08 w-font14 w-color000 w-textalignC">允许</a>
            </div>
        </div>
    </div>



    <!--分享四按钮弹框-->
    <div class="shareBox" style="display: none;">
        <div class="share">
            <div class="w-borderBeee w-paddingTopBottom02">
                <h4 class="w-height07 w-marginBottom02 w-textalignC w-font15 w-color333">分享</h4>
                <div class="w-flex">
                    <a id="qq" class="w-flexitem1 w-textalignC">
                        <b class="iconQQ w-marginBottom01"></b>
                        <h4 class="w-height03 w-font13 w-color666">QQ好友</h4>
                    </a>
                    <a id="wx" class="w-flexitem1 w-textalignC">
                        <b class="iconWechat w-marginBottom01"></b>
                        <h4 class="w-height03 w-font13 w-color666">微信好友</h4>
                    </a>
                    <a id="pyq" class="w-flexitem1 w-textalignC">
                        <b class="iconFriend w-marginBottom01"></b>
                        <h4 class="w-height03 w-font13 w-color666">朋友圈</h4>
                    </a>
                    <a id="qzone" class="w-flexitem1 w-textalignC">
                        <b class="iconQzone w-marginBottom01"></b>
                        <h4 class="w-height03 w-font13 w-color666">QQ空间</h4>
                    </a>
                </div>
            </div>
            <a href="javascript:;" class="w-block w-height08 w-font15 w-color333 w-textalignC" onclick="$('.shareBox').hide()">取消</a>
        </div>
    </div>

    <!--感兴趣的标签蒙层-->
    <div class="tagBox">
        <a href="javascript:;" class="iconTag"></a>
        <h4 class="w-textalignC w-marginBottom06">
            <span class="xhMax">选择感兴趣的标签</span>
        </h4>
        <div class="swiper-container w-swiper-container">


            <div class="swiper-wrapper w-swiper-wrapper">
                <?php if(is_array($labelList)): $i = 0; $__LIST__ = $labelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lists): $mod = ($i % 2 );++$i; if($key%9 == 0) { ?>
                    <div class="swiper-slide w-swiper-slide">
                        <?php } ?>
                        <?php if($key%3 == 0) { ?>
                        <div class="iconTagBox">
                            <?php } ?>

                            <label class="<?php if($lists['color'] == 'h') { echo 'iconTagPurple';}elseif($lists['color'] == 'Orange') { echo 'iconTagOrange';}elseif($lists['color'] == 'blue') { echo 'iconTagBlue';}else{echo 'iconTagPurple';};?>">
                                <input id="lilabe<?php echo ($lists["id"]); ?>" name="lilabe" class="lilabe" value="<?php echo ($lists["id"]); ?>" type="checkbox" />
                                <span disabled data-id="<?php echo ($lists["id"]); ?>" class="listlabs"><?php echo ($lists["name"]); ?></span>
                            </label>

                            <?php if($key== 2||$key== 5 ||$key== 8 ||$key== 11 ||$key== 14 ||$key== 17 ||$key== 20 ||$key== 23 ||$key== 26) { ?>
                        </div>
                        <?php } ?>
                        <?php if($key== 8||$key== 17 ||$key== 26) { ?>
                    </div>
                    <?php } endforeach; endif; else: echo "" ;endif; ?>
                <?php if($key % 9 != 0) { ?>
            </div>
            <?php } ?>
        </div>
        <section class="swiper-pagination w-swiper-pagination"></section>
    </div>



    <input type="hidden" class="ids" value="">
    <input type="hidden" class="title" value="">

    <section class="w-textalignC">
        <a href="javascript:;" class="w-btn2 w-bgcolor44D397 w-colorFFF" id="okBtn">选好了</a>
    </section>

    </div>







</body>


<input type="hidden" class="member_id" value="<?php echo ($member_id); ?>">
<input type="hidden" class="isdj" value="<?php echo ($isdj); ?>">


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


<input type="hidden" class="ggid" value="">
<input type="hidden" class="lastid" value="">
<script type="text/javascript">

    var page = 1;

    checkLogin();
    function checkLogin() {
        var member_id = $('.member_id').val();
        if(member_id == '')
        {
           if(localStorage.app_openid)
           {
               $.ajax({
                   type: 'POST',
                   url: "/public/app_wx_login",
                   dataType: 'json',
                   data: {app_openid:localStorage.app_openid},
                   success: function (res)
                   {
                       if(res.status==1){
                           location.reload();
                       }
                   },
               });
           }
        }
    }


    $.ajax({
        type: 'POST',
        url: '/Public/index',
        data:{},
        success: function () {
           // alert(111);
        },
    });


    function showLab()
    {
        $('.tagBox').show();
        var swiper1 = new Swiper('.w-swiper-container', {
            pagination: '.w-swiper-pagination',
            paginationClickable: true,
            autoplayDisableOnInteraction : false
        });
    }


    $('.searchBox').click(function () {
        var kw = $('.kw').val();
        location.href = '/index/searchArti/kw/'+ kw;
    });

    function shareFunc(e)
    {
        // var title = $(e).attr('data-title');
        // $('.title').val(title);
        // var id = $(e).attr('data-id');
        //
        // $('.ids').val(id);
        // $('.shareBox').show();

    }

    function shareFuns(type) {
        var title = $('.title').val();
        var id = $('.ids').val();
        var url = "/Article/infoDyna/id/"+id;
        WebViewJavascriptBridge.shareAction(title,id,url,type,1);
    }

    function shareBack(id)
    {
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




    var chk_value =[];//定义一个数组
    var chk_value_all =[];//定义一个数组

    urlJump();


    $('.indexbtn').click(function () {
        urlJump();
    });

    $('.listlabs').click(function ()
    {

        $('.lilabe').each(function () {
            if($(this).prop('checked')){
                var val=$(this).val();
                var fag = $.inArray(parseInt(val),chk_value_all);
                if(fag == -1){
                    chk_value_all.push(parseInt(val));
                }
            }
        });
        var truz = $(this).attr('data-id');

        var indexs = $.inArray(parseInt(truz),chk_value_all);

        if(indexs != -1)
        {
            chk_value_all.splice(indexs,1);
        }else
        {
            chk_value_all.push(parseInt(truz));
        }
    });
    $('.iconTag').click(function()
    {
        $('.tagBox').hide();
        chk_value_all = [];
    });

    $('#okBtn').click(function()
    {

        $('.tagBox').hide();
        page = 1;

        urlJump(2);
    });
    function reloadActionHtml() {

        window.location.reload();
    }
    //disabled
    function urlJump(ts) {

        if(ts == 2)
        {
           //alert(321)
         //   window.location.reload();
            WebViewJavascriptBridge.reloadAction();
        }
        //alert(123)
        var lastid = $('.lastid').val();

        var ggid = $('.ggid').val();
        //alert(JSON.stringify(chk_value));
         //alert(chk_value.length);
        $.ajax({
            type: 'POST',
            url: '/Index/index',
            data: {lastid:lastid,lablist:chk_value,setLab:chk_value_all,p:1,ggid:ggid},
            success: function (e) {
                if(e.status == 1)
                {

                    // if(ts == 2)
                    // {
                    //     WebViewJavascriptBridge.reloadAction();
                    // }

                    $('.noData').hide();
                    $('#dataList').html(e.info.str);
                    $('.ggid').val(e.info.ggid);
                    $('.lastid').val(e.info.lastId);

                }else{
                    $('.noData').show();
                    $('#dataList').html('');
                }

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
    function urlJumpLoca()
    {
        //alert(123);
        location.href="/Index/index"
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

    function urlJumpMore()
    {
        if(isget)
        {
            isget = false;
            page = parseInt(page) +1;
            var type = $('.type').val();
            var ggid = $('.ggid').val();
            var lastid = $('.lastid').val();

            $.ajax({
                type: 'POST',
                url: '/Index/index',
                data: {p:page,lablist:chk_value,ggid:ggid,lastid:lastid},
                success: function (e)
                {
                    if(e.status == 1)
                    {
                        isget = true;
                        $('#dataList').append(e.info.str);
                        $('.ggid').val(e.info.ggid);
                        $('.lastid').val(e.info.lastId);
                    }else{
                        $('#dataList').append('<li style="display: block;text-align:center;" class="loadMore">没有更多了...</li>');
                        isget = false;
                    }
                },
            });
        }
    }


    //点赞
    function giveAction(id,el) {
        var isz = $('.isz').val();

            $.ajax({
                type: 'POST',
                url: '/Article/giveAction',
                data: {id:id},
                success: function (e) {
                    if(e.status == 1)
                    {
                        $('.isz').val(1);
                        var pn = parseInt($(el).parent().children('span').html());
                        if(!pn)
                        {
                            pn = 0;
                        }

                        $(el).parent().children('span').html(pn+1);
                        $(el).prop("onclick",null).off("click");
                        $(el).prop('disabled','disabled');
                        $('.imputBtn').attr("disabled", true);
                        go_alert2(e.info);
                    }else
                    {

                        go_alert2(e.info);
                    }
                },
            });
    }
    //关注
    function gzbtn(id)
    {
        $.ajax({
            type: 'POST',
            url: '/Article/folloAction',
            data: {id:id,types:'zjgz'},
            success: function (e) {
                if(e.status == 1)
                {
                    go_alert2(e.info);
                }else
                {
                    go_alert2(e.info);
                }
            },
        });
    }

    $('.seaechNav a').click(function(){
        $(this).addClass('seaechNavAce').siblings('a').removeClass('seaechNavAce');
        var lab = $(this).attr('data-id');
        chk_value = [];
        chk_value.push(parseInt(lab));
        page = 1;
        $('.ggid').val('')
        urlJump();
    });
	
	//左右滑动事件
	$("#listContent").on('touchstart',function(e){
		var self=$(this);
		var startX = e.originalEvent.targetTouches[0].clientX,//手指触碰屏幕的x坐标
			pullDeltaX = 0;
			console.log(startX)
		self.on('touchmove',function(e){
			var x = e.originalEvent.targetTouches[0].clientX;//手指移动后所在的坐标
	        pullDeltaX = x - startX;//移动后的位移
	        if (!pullDeltaX){
	            return;
	        }
	        e.preventDefault();//阻止手机浏览器默认事件
		})
		self.on('touchend',function(e){
			self.off('touchmove touchend');//解除touchmove和touchend事件
	        //所要执行的代码
	        e.preventDefault();
	        //判断移动距离是否大于30像素
	        if (pullDeltaX > 30){
				console.log("向右滑，上一条");
	            //右滑，往前翻所执行的代码
	            var index=$('.seaechNav a.seaechNavAce').index();
	            if(index==0){    //当前为第一项
	            	return
	            }
	            else{
	            	index--;
	            	$('.seaechNav a').eq(index).addClass('seaechNavAce').siblings('a').removeClass('seaechNavAce')
	            	var ids=$('.seaechNav a').eq(index).attr('data-id');
	            	moveFun(ids);
	            }
	        }
	        //判断当前页面是否为最后一页
	        else if (pullDeltaX < -30){
				console.log("向左滑，下一条");
	            //左滑，往后翻所执行的代码
	            var index=$('.seaechNav a.seaechNavAce').index();
	            var len=$('.seaechNav a').length;
	            if(index==len-1){    //当前为最后一项
	            	return
	            }
	            else{
	            	index++;
	            	$('.seaechNav a').eq(index).addClass('seaechNavAce').siblings('a').removeClass('seaechNavAce')
	            	var ids=$('.seaechNav a').eq(index).attr('data-id');
	            	moveFun(ids);
	            }
	        }
		})
		console.log(chk_value);
	})
	
	function moveFun(ids){
		chk_value = [];
        chk_value.push(parseInt(ids));
        page = 1;
        $('.ggid').val('')
        urlJump();
	}

    //选择感兴趣标签
    $('.iconAdd').click(function(){
        $(".mylablist").each(function () {
            var id=$(this).attr('tar');
            var idstr='#lilabe'+id;
            $(idstr).prop('checked',true);
        });
        $('.tagBox').show();
        var swiper1 = new Swiper('.w-swiper-container', {
            pagination: '.w-swiper-pagination',
            paginationClickable: true,
            autoplayDisableOnInteraction : false
        });
    });
    function appclose() {
        if($('.tagBox').css('display')=='block'){
            $('.tagBox').hide();
        }
        else {
            WebViewJavascriptBridge.exitApp();
        }
    }
</script>
</html>