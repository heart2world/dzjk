/*swiper模式点击图片缩放浏览*/
/*二种模式：（一），列表模式*/
$(function () {


    window.img_clickShow = function () {

    	
        if ($(".swiper_clickLook").length) {

            $(".swiper_clickLook").each(function () {
                var dom_box = $(this);
                dom_box.click(function (e) {
                    //排除非需要区域的click事件
                   if($(e.target)[0].style.backgroundImage== "" && ($(e.target).attr("src") == undefined||($(e.target).attr("src").trim() == ""))) //排除其它点击区域
                    {
                      
                       return;
                    }else if($(e.target).parents(".swiper_clickLook").eq(0).find(".swiper_clickLook").length)//排除其他图片
                    {
                    	return;
                    }
                     
                    if (!$(".detail_look").length) {
                        $('body').append('<div class="detail_look" swiper_clickLook ><em><b></b>/<i></i></em><div class="swiper-container" id="Myswiper_clickLook"><div class="swiper-wrapper"></div></div><span class="close_btn" style=""><button href="javascript:" class="fa fa-times-circle-o color-fff font30 " ></button></span></div>');
                        $(".detail_look").bind("touchstart",function(){
                        	    $(this).css("pointer-events","auto");
								$("body,html").css("pointer-events","none")
							}).bind("touchend",function(){
								$("body,html").css("pointer-events","auto")
							})
                    };
                    $(".detail_look[swiper_clickLook] .close_btn button").click(function () {
                        $(".detail_look[swiper_clickLook]").remove();

                    })
                   
                    var img_ary = [];
                    //判断是点击图片，还是点击有背景图的元素
                    var outer_boxs = $(e.target).parent();
                    var doms_string = [];
                    doms_string.unshift(!$(e.target).attr("class")? ">" + $(e.target)[0].nodeName.toLowerCase() :"."+ $(e.target).attr("class").replace(/active|action/g, "").split(" ")[0]);

                    while (!outer_boxs.hasClass("swiper_clickLook")) {

                        if(outer_boxs.hasClass(".swiper-slide"))
                        {
                        	 doms_string.unshift(!outer_boxs.attr("class")? ">" + outer_boxs[0].nodeName.toLowerCase() : "." + outer_boxs.attr("class").replace(/active|action/g, "").split(" ").pop());
                        }else{
                        	 doms_string.unshift(">" + outer_boxs[0].nodeName.toLowerCase())
                        }
                      
                        outer_boxs = outer_boxs.parent();

                    }
                     
            
                    var img_ary = $(e.target).parents(".swiper_clickLook").eq(0).find(doms_string.join(""));
                
                    var ind = $(e.target).parents(".swiper_clickLook").eq(0).find(doms_string.join("")).index($(e.target));
                    doms_string = [];
                 
                    img_ary.each(function () {
                    	if($(this).parents(".swiper-slide-duplicate").length==0)
                    	{
                          doms_string.push($(this).attr("src") ? $(this).attr("src") : $(this)[0].style.backgroundImage.replace(/url\(\"|\"\)/g, ""))
                    	}

                    });
                    doms_string.forEach(function(item,index,ary){
   
                    
                    	if($(e.target)[0].style.backgroundImage.indexOf(item)>=0||$(e.target).attr("src").indexOf(item)>=0)
                    	{
                    	
                    		ind=index;
                    		return false
                    	}
                    })
                     
                     
                    
                    $(".detail_look .swiper-container .swiper-wrapper").empty();
                    var len = doms_string.length

                    while (doms_string.length) {
                        $(".detail_look .swiper-container .swiper-wrapper").append('<div class="swiper-slide"><div class="look_box"><img src="' + doms_string.shift() + '"/></div></div>')
                    }
       
                    $(".detail_look[swiper_clickLook] >em i").text(len);
                    $(".detail_look[swiper_clickLook]").show();
                    var mySwiper = new Swiper('#Myswiper_clickLook', {
                        initialSlide: ind,
                        loop: true,
                        onInit: function () {
                            $(".detail_look[swiper_clickLook] >em b").text(ind + 1);

                        },
                        onSlideChangeEnd: function (mySwiper) {
                            $(".detail_look[swiper_clickLook] >em b").text(mySwiper.activeIndex == "0" ? len : (mySwiper.activeIndex == len + 1 ? 1 : mySwiper.activeIndex));
                        }

                    });
    

                    $('.look_box').each(function () {
                        new RTP.PinchZoom($(this), {});
                    });

                });


            })





        };

    }
    img_clickShow();

})