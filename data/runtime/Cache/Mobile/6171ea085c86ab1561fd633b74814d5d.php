<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <title>医生专栏</title>
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
    .iconSearch2 {
    display: inline-block;
    width: 1.3rem;
    height: 0.4rem;
    border: 1px solid #fff;
    line-height: .4rem;
    border-radius: .2rem;
    text-align: center;
    color: #fff;
    background-size: .25rem .25rem;
    background-repeat: no-repeat;
    background-position: .1rem center;
    position: absolute;
    top: .18rem;
    right: .2rem;
    padding-left: .3rem;
}
</style>
<body>
<section class="flex_column w-height100 w-bgcolorf7f7f7"  id="flex_column">
    <div class="w-bgcolor44D397 w-padding0102 w-textalignC w-font0">
        <div class="doubleBtnBox">
            <a href="javascript:;" data-id="all"  class="doubleBtnAce">全部</a>
            <b class="grayLine2"></b>
            <a href="javascript:;"    data-id="ygz">已关注</a>
        </div>
    </div>
    <div class="w-paddingTopBottom01 w-borderBeee w-bgcolorFFF w-flex">
        <div class="w-flexitem1 seaechNav">
            <a  data-id="all"  class="seaechNavAce">全部</a>
            <?php if(is_array($labelList)): $i = 0; $__LIST__ = $labelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$lists): $mod = ($i % 2 );++$i;?><a   data-id="<?php echo ($lists["id"]); ?>" class="lablist"><?php echo ($lists["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <a href="/Index/search/type/ys" class="iconSearch2">搜索医生</a>
    </div>
    <div class="w-flexitem1 w-section w-sectionD" id="scroll_content" >
        <div class="w-paddingLeftRight02 w-bgcolorFFF">


            <div class="w-paddingTopBottom02 w-borderBeee" v-cloak v-for="(item,index) in list">
                <div class="w-flex w-marginBottom02">
                    <div @click="jumpDoctIndex(item.id)" class="header2 w-marginRight02" :style="{ backgroundImage: 'url(' + (item.avatar?item.avatar:'/logo.png') + ')' }"><b></b></div>
                    <div  @click="jumpDoctIndex(item.id)" class="w-flexitem1 w-paddingTopBottom01">
                        <h4 class="w-height04 w-font14 w-color000 w-onlyline">
                            <em style="max-width: calc(2.5rem);overflow: hidden;display: inline-block;text-overflow: ellipsis;vertical-align: middle;">{{item.nickname}}</em>
                            <em style="vertical-align: middle;display: inline-block;margin-top: 2px;" class="w-font13 w-color333">粉丝：{{item.fss}}</em>
                        </h4>
                        <span class="w-block w-height04 w-font12 w-color999 w-onlyline">{{item.hosp}}{{item.zw}}</span>
                    </div>
                    <label  v-if="item.isshow==1 && item.isgz==0" for="collect2" class="iconCollect w-paddingTopBottom01">
                        <input     type="checkbox" disabled id="collect2">
                        <span  @click="gzFunc(item.id)"></span>
                    </label>
                    <label  v-if="item.isshow==1 && item.isgz==1"  class="w-paddingTopBottom01">
                        <input checked="checked"  disabled  type="checkbox">
                        <span  @click="gzFunc(item.id)" >已关注</span>
                    </label>
                </div>
                <h4  @click="jumpDoctIndex(item.id)" class="w-doubleline w-line04 w-font14 w-color333">{{item.grjs}}</h4>
            </div>

        </div>
        <div style="display: none" class="loadMore w-textalignC">没有更多了...</div>
        <div style="display: none;" class="noData">暂无数据</div>
    </div>


    <footer class="footer w-flex">
        <label for="home" class="w-flexitem1">
            <a href="/index/index">
                <input type="radio" name="footer" id="home"/>
                <span>首页</span>
            </a>
        </label>
        <label for="doctor" class="w-flexitem1">

                <input type="radio" name="footer" id="doctor" checked/>
                <span @click="goSearch()">医生专栏</span>

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
    <!--<footer class="footer w-flex">
    <label for="home" class="w-flexitem1">
        <a href="/index/index">
            <input type="radio" name="footer" id="home" <?php if($footer_url == '/Index/index' or $footer_url == '/' or $footer_url == '/index.php?g=&m=Index&a=index'): ?>checked<?php endif; ?>/>
            <span>首页</span>
        </a>
    </label>
    <label for="doctor" class="w-flexitem1">
        <input type="radio" name="footer" id="doctor" <?php if($footer_url == '/DoctorSpec/index' or $footer_url == '/index.php?g=&m=DoctorSpec&a=index'): ?>checked<?php endif; ?>/>
        <span @click="goSearch()">医生专栏</span>
    </label>
    <label for="im" class="w-flexitem1">
        <?php  if($member_id > 0) { ?>

        <a href="/Member/index">
            <?php }else{ ?>
            <a href="/Index/login">
                <?php } ?>

                <input type="radio" name="footer" id="im" <?php if($footer_url == '/Member/index' or $footer_url == '/index.php?g=&m=Member&a=index'): ?>checked<?php endif; ?> />
                <span>我的</span>
            </a>
    </label>
</footer>

-->
</section>
</body>
<input type="hidden" class="type" value="">
<input type="hidden" class="types" value="">
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



    //全部跟已关注切换样式
    // $('.doubleBtnBox a').click(function(){
    //     $(this).addClass('doubleBtnAce').siblings('a').removeClass('doubleBtnAce');
    //     var types = $(this).attr('data-id');
    //     $('.types').val(types);
    //     // urlJump();
    // });
    // $('.seaechNav a').click(function(){
    //     $(this).addClass('seaechNavAce').siblings('a').removeClass('seaechNavAce');
    //      var type = $(this).attr('data-id');
    //    　$('.type').val(type);
    //     // urlJump();
    // });

    // urlJump();
    // function urlJump()
    // {
    //     var type = $('.type').val();
    //     var types = $('.types').val();
    //
    //     $.ajax({
    //         type: 'POST',
    //         url: '/DoctorSpec/index',
    //         data: {p:1,types:types,type:type},
    //         success: function (e) {
    //             if(e)
    //             {
    //                 // $('#datalist').html(e);
    //             }else{
    //                 // $('.noData').show();
    //                 // $('#datalist').html('');
    //             }
    //         },
    //     });
    // }
</script>
<link rel="stylesheet" type="text/css" href="/themes//Public/Mobile/css/dropload.css"/>



<script>
    window.onload = function () {
        var isFirst = 0;
        msg_list_loading = false;
        var dropload = null;
        //页面加载后替换
        var vm = new Vue({
            el: '#flex_column',
            data: {
                list: [],
                show: $("#currPage").val() ? $("#currPage").val() : 2,

                select_member_id: 0,
                select_label: '',
                guanzhu_count: 0,
                fensi_count: 0,
                page: {
                    p: 0,
                    totalPage: 1,
                    loadContent: "加载更多"
                },
            },

            methods: {  //方法

                //到医生首页
                jumpDoctIndex:function(id)
                {
                    location.href = "/Doctor/index/id/"+id;
                },
                goSearch:function () {
                    vm.mySearch(true);
                },
                gzFunc:function (id)
                {
                    var id = id;
                    $.ajax({
                        type: 'POST',
                        url: '/Article/folloAction',
                        data: {id:id,types:'zjgz'},
                        success: function (e) {
                            if(e.status == 1)
                            {
                                go_alert2(e.info);

                                vm.mySearch(true);
                            }else
                            {
                                go_alert2(e.info);
                                if(e.info =='请先登录')
                                {

                                    location.href ="/Index/login?tiaourl="+encodeURI(location.href);
                                    // location.href ="/Index/login";
                                }
                            }
                        },
                    });
                },
                mySearch: function (search) {
                    if (search) {
                        this.list = [];
                        this.page.p = 0;
                        this.page.totalPage = 1;
                        this.page.loadContent = '加载更多';
                    }
                    var type = $('.type').val();
                    var types = $('.types').val();
                    msg_list_loading = true;

                    if (this.page.p < this.page.totalPage)
                    {
                        var p = this.page.p == 0 ? this.page.p = 1 : this.page.p += 1;
                        var url = "<?php echo U('index');?>";
                        var param = {p: p,type:type,types:types};

                        $.getJSON(url, param, function (data) {
                            vm.list = vm.list.concat(data.info.list);
                            vm.page.totalPage = data.info.total_page;

                            $(".page_more_load").show();
                            if (vm.page.p >= vm.page.totalPage)
                            {
                                if(vm.page.totalPage >= 2)
                                {
                                    vm.page.loadContent = '没有更多了!';
                                }
                                if (vm.page.totalPage == 1 || data.info.total_page == 0) {
                                    vm.page.loadContent = '';
                                    $(".page_more_load").hide();
                                }
                            }
                            if (data.info.p >= data.info.total_page) {


                                if(vm.page.totalPage >= 2)
                                {
                                    $('.loadMore').show();
                                }
                            }
                            if(!vm.list||vm.list.length==0){
                                $(".noData").show();
                            }else
                            {
                                $(".noData").hide();
                            }
                            msg_list_loading = false;
                        })
                    }
                },
            },

            //页面加载后调用ajax请求数据
            created: function () {
                this.mySearch(true);
                setTimeout(function ()
                {
                    $('.doubleBtnBox a').click(function(){
                        $(this).addClass('doubleBtnAce').siblings('a').removeClass('doubleBtnAce');
                        var types = $(this).attr('data-id');
                        $('.types').val(types);
                        vm.mySearch(true);
                    });
                    $('.seaechNav a').click(function(){
                        $(this).addClass('seaechNavAce').siblings('a').removeClass('seaechNavAce');

                        if($(this).attr('data-id') == $('.type').val())
                        {
                            $('.type').val('');
                            $(this).removeClass('seaechNavAce');
                        }else
                        {
                            var type = $(this).attr('data-id');
                            $('.type').val(type);
                        }
                        vm.mySearch(true);


                    });
                },0)

            },
            mounted:function(){
                //if (this.show == 2)
                //{
                //    //上拉刷新初始化
                //    dropload = $('.inner').dropload({
                //        domUp : {
                //            domClass   : 'dropload-up',
                //            domRefresh : '<div class="dropload-refresh">↓下拉刷新</div>',
                //            domUpdate  : '<div class="dropload-update">↑释放更新</div>'
                //        },
                //        loadUpFn : function(me){
                //            // 为了测试，延迟1秒加载
                //            setTimeout(function(){
                //                // 每次数据加载完，必须重置
                //                dropload.resetload();
                //            },1000);
                //            vm.mySearch(true);
                //        }
                //    });
                //}
            },
        });

        //下拉滚动分页
        //$("#scroll_content").scroll(function () {
        //    if($(this).scrollTop() == 0){
        //        //上拉刷新初始化
        //        dropload = $('.inner').dropload({
        //            domUp: {
        //                domClass: 'dropload-up',
        //                domRefresh: '<div class="dropload-refresh">↓下拉刷新</div>',
        //                domUpdate: '<div class="dropload-update">↑释放更新</div>'
        //            },
        //            loadUpFn: function (me) {
        //                // 为了测试，延迟1秒加载
        //                setTimeout(function () {
        //                    // 每次数据加载完，必须重置
        //                    dropload.resetload();
        //                }, 1000);
        //                vm.mySearch(true);
        //            }
        //        });
        //    } else {
        //        $('.inner').unbind();
        //    }
        //    //得到整个页面高度
        //    var html_height = $(".list_content").height();
        //    var screen_height = document.documentElement.clientHeight;//363
        //    var scroll_height = $("#scroll_content").scrollTop();//760
        //    if (scroll_height >= html_height - screen_height) {
        //        $(document).scrollTop(scroll_height - 1);
        //        if (!msg_list_loading) {
        //            vm.mySearch(false);
        //        }
        //    }
        //});
        $("#scroll_content").on('scroll', function () {
            var nScrollHight = 0;
            var nScrollTop = 0;
            var nDivHight = $(".w-sectionD").height();
            nScrollHight = $(this)[0].scrollHeight;
            nScrollTop = $(this)[0].scrollTop;
            var paddingBottom = parseInt($(this).css('padding-bottom')), paddingTop = parseInt($(this).css('padding-top'));
            if (nScrollTop + paddingBottom + paddingTop + nDivHight >= nScrollHight - 10) {
                if (!msg_list_loading) {
                    vm.mySearch(false);
                }
            }
        });


    }
</script>

</html>