(function(){
    var calendarDate = [];
    var riliHtml = '';
    //绘制(初始化)
    function getIndexDay(obj,ind){
        isLeapYear(ind);
        getDays(ind);
        riliHtml = '';
        //本月一号周凿
        calendarDate[ind].monthStart = new Date(calendarDate[ind].year+"/"+calendarDate[ind].month+"/1").getDay();
        //上个月所占空格数
        //console.log(calendarDate[ind])
        if( calendarDate[ind].monthStart == 0 ){//独占一衿
            calendarDate[ind].monthStart = 7;
        }
        //上月数据
        for( var i = calendarDate[ind].monthStart;i>0;i-- ){
            var dataDateStr = calendarDate[ind].lastYear + "-" + calendarDate[ind].lastMonth + "-" + (calendarDate[ind].lastDays - i + 1);
            //判断是否设置了休息
            if($.inArray(nowDate,calendarDate[ind].opt.rest)!=-1){
                riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                    +'"><span class="ht-rili-day">'+ (k + 1)
                    +'</span><span class="ht-rili-money" style="color:#BFC4CA">休</span></div>';
            }else if(is_array(nowDate,calendarDate[ind].opt.seckill)){
                var value=get_array(nowDate,calendarDate[ind].opt.seckill);
                var string='';
                for(var t=0;t<value.length;t++){
                    string+= '<li>特惠活动 :秒杀</li><li>秒杀价 :￥'+value[t].money
                        +'</li><li>秒杀数量 :'+value[t].num
                        +'</li><li>秒杀时间 :'+value[t].seckill_time
                        +'</li>'
                }
                riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                    +'"><span class="miao" >秒</span>'
                    +'<ul class="miao_info">'+string+'</ul>'
                    +'<span class="ht-rili-day">'+ (k + 1)
                    +'</span><span class="ht-rili-money" style="color:#BFC4CA" data-money="'+ value[0].money
                    +'">&yen;'+ value[0].money +'</span></div>';
            }else if(is_array(nowDate,calendarDate[ind].opt.limit)){
                var value=get_array(nowDate,calendarDate[ind].opt.limit);
                riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                    +'"><span class="miao" >限</span>'
                    +'<ul class="miao_info"><li>特惠活动 :限时促销</li><li>促销价 :￥'+value.money
                    +'</li><li>促销数量 :'+value[0].num
                    +'</li><li>开始时间 :'+value[0].limit_start_time
                    +'</li><li>结束时间 :'+value[0].limit_end_time+'</li></ul>'
                    +'<span class="ht-rili-day">'+ (k + 1)
                    +'</span><span class="ht-rili-money"  style="color:#BFC4CA" data-money="'+ value.money
                    +'">&yen;'+ value.money +'</span></div>';
            }else{
                riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                    +'"><ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                    +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul><span class="ht-rili-day">'+ (calendarDate[ind].lastDays - i + 1)
                    +'</span><span class="ht-rili-money" style="color:#BFC4CA">&yen;'+ calendarDate[ind].opt.money +'</span></div>';
            }
            /*riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
             +'"><span class="ht-rili-day">'+ (calendarDate[ind].lastDays - i + 1)
             +'</span><span class="ht-rili-money"></span></div>'*/
        }
        //本月数据
        var d = new Date();
        var str = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate();//获取当前时间
        for( var k = 0;k<calendarDate[ind].days;k++ ){
            var flag
            var dataDateStr = calendarDate[ind].year + "-" + calendarDate[ind].month + "-" + ( k + 1  );
            for( var d in calendarDate[ind].opt.data ){
                var nowDate = dataDateStr;
                var dataDate = calendarDate[ind].opt.data[d].date;
                flag = checkDate(nowDate,dataDate);
                if( flag ){
                    //判断是否设置了休息
                    if($.inArray(nowDate,calendarDate[ind].opt.rest)!=-1){
                        if(nowDate==str){
                            riliHtml += '<div class="ht-rili-td ht-rili-td-disabled ht-rili-td-active" data-date="'+ dataDateStr
                                +'"><span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" style="color:#BFC4CA">休 <input data-date="'+ dataDateStr
                                +'" data-money="'+ calendarDate[ind].opt.data[d].data
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                                +'"><span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" style="color:#BFC4CA">休 <input data-date="'+ dataDateStr
                                +'" data-money="'+ calendarDate[ind].opt.data[d].data
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else if(is_array(nowDate,calendarDate[ind].opt.seckill)){
                        if(nowDate==str){
                           // var value=get_array(nowDate,calendarDate[ind].opt.seckill);
                            var value=get_array(nowDate,calendarDate[ind].opt.seckill);
                            var string='';
                            for(var t=0;t<value.length;t++){
                                string+= '<li>特惠活动 :秒杀</li><li>秒杀价 :￥'+value[t].money
                                    +'</li><li>秒杀数量 :'+value[t].num
                                    +'</li><li>秒杀时间 :'+value[t].seckill_time
                                    +'</li>'
                            }
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick ht-rili-td-active" data-date="'+ dataDateStr
                                +'"><span class="miao" >秒</span>'
                                +'<ul class="miao_info">'+string+'</ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  >&yen;'+ value[0].money +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ value[0].money
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            var value=get_array(nowDate,calendarDate[ind].opt.seckill);
                            var string='';
                            for(var t=0;t<value.length;t++){
                                string+= '<li>特惠活动 :秒杀</li><li>秒杀价 :￥'+value[t].money
                                    +'</li><li>秒杀数量 :'+value[t].num
                                    +'</li><li>秒杀时间 :'+value[t].seckill_time
                                    +'</li>'
                            }
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick" ><span class="miao" >秒</span>'
                                +'<ul class="miao_info">'+string+'</ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  data-money="'+ value[0].money
                                +'">&yen;'+ value[0].money +' <input data-money="'+ value[0].money
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else if(is_array(nowDate,calendarDate[ind].opt.limit)){
                        //判断是否是当天
                        if(nowDate==str){
                            var value=get_array(nowDate,calendarDate[ind].opt.limit);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick ht-rili-td-active" data-date="'+ dataDateStr
                                +'"><span class="miao" >限</span>'
                                +'<ul class="miao_info"><li>特惠活动 :限时促销</li><li>促销价 :￥'+value[0].money
                                +'</li><li>促销数量 :'+value[0].num
                                +'</li><li>开始时间 :'+value[0].limit_start_time
                                +'</li><li>结束时间 :'+value[0].limit_end_time+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  >&yen;'+ value[0].money +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ value[0].money
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            var value=get_array(nowDate,calendarDate[ind].opt.limit);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick" ><span class="miao" >限</span>'
                                +'<ul class="miao_info"><li>特惠活动 :限时促销</li><li>促销价 :￥'+value[0].money
                                +'</li><li>促销数量 :'+value[0].num
                                +'</li><li>开始时间 :'+value[0].limit_start_time
                                +'</li><li>结束时间 :'+value[0].limit_end_time+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ value[0].money +' <input data-money="'+ value[0].money
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else if(is_array(nowDate,calendarDate[ind].opt.special_price)&&is_array(nowDate,calendarDate[ind].opt.special_num)){
                        //判断是否是当天
                        if(nowDate==str){
                            var value=get_array(nowDate,calendarDate[ind].opt.special_price);
                            var value1=get_array(nowDate,calendarDate[ind].opt.special_num);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick ht-rili-td-active" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+value[0].money
                                +'</li><li>预售票数 :'+value1[0].num+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  >&yen;'+ value[0].money +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ value[0].money
                                +'" data-num="'+ value1[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            var value=get_array(nowDate,calendarDate[ind].opt.special_price);
                            var value1=get_array(nowDate,calendarDate[ind].opt.special_num);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+value[0].money
                                +'</li><li>预售票数 :'+value1[0].num+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ value[0].money +' <input data-money="'+ value[0].money
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ value1[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else if(is_array(nowDate,calendarDate[ind].opt.special_price)){
                        //判断是否是当天
                        if(nowDate==str){
                            var value=get_array(nowDate,calendarDate[ind].opt.special_price);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick ht-rili-td-active" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+value[0].money
                                +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  >&yen;'+ value[0].money +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ value[0].money
                                +'" data-num="'+ calendarDate[ind].opt.poll
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            var value=get_array(nowDate,calendarDate[ind].opt.special_price);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+value[0].money
                                +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ value[0].money +' <input data-money="'+ value[0].money
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ calendarDate[ind].opt.poll
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else if(is_array(nowDate,calendarDate[ind].opt.special_num)){
                        //判断是否是当天
                        if(nowDate==str){
                            var value=get_array(nowDate,calendarDate[ind].opt.special_num);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick ht-rili-td-active" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                                +'</li><li>预售票数 :'+value[0].num+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money"  >&yen;'+ calendarDate[ind].opt.money +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ calendarDate[ind].opt.money
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            var value=get_array(nowDate,calendarDate[ind].opt.special_num);
                            riliHtml += '<div class="ht-rili-td ht-rili-td-onclick" >'
                                +'<ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                                +'</li><li>预售票数 :'+value[0].num+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ calendarDate[ind].opt.money +' <input data-money="'+ calendarDate[ind].opt.money
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ value[0].num
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }else{
                        //判断是否是当天
                        if(nowDate==str){
                            riliHtml += '<div class="ht-rili-td ht-rili-onclick ht-rili-td-active" "data-date="'
                                + dataDateStr +'">'
                                +'<ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                                +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul>'
                                +'<span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ calendarDate[ind].opt.data[d].data +' <input data-date="'+ dataDateStr
                                +'" data-money="'+ calendarDate[ind].opt.data[d].data
                                +'" data-num="'+ calendarDate[ind].opt.poll
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }else{
                            riliHtml += '<div class="ht-rili-td ht-rili-onclick"><ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                                +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul><span class="ht-rili-day">'+ (k + 1)
                                +'</span><span class="ht-rili-money" >&yen;'+ calendarDate[ind].opt.data[d].data
                                +' <input data-money="'+ calendarDate[ind].opt.data[d].data
                                +'" data-date="'+ dataDateStr
                                +'" data-num="'+ calendarDate[ind].opt.poll
                                +'" type="checkbox" name="c_time"/></span></div>';
                            break;
                        }
                    }

                }
            }
            if( !flag ){
                //判断是否设置了休息
                if($.inArray(nowDate,calendarDate[ind].opt.rest)!=-1){
                    riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                        +'"><span class="ht-rili-day">'+ (k + 1)
                        +'</span><span class="ht-rili-money" style="color:#BFC4CA">休</span></div>';
                }else if(is_array(nowDate,calendarDate[ind].opt.seckill)){
                    var value=get_array(nowDate,calendarDate[ind].opt.seckill);
                    var string='';
                    for(var t=0;t<value.length;t++){
                        string+= '<li>特惠活动 :秒杀</li><li>秒杀价 :￥'+value[t].money
                            +'</li><li>秒杀数量 :'+value[t].num
                            +'</li><li>秒杀时间 :'+value[t].seckill_time
                            +'</li>'
                    }
                    riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                        +'"><span class="miao" >秒</span>'
                        +'<ul class="miao_info">'+string+'</ul>'
                        +'<span class="ht-rili-day">'+ (k + 1)
                        +'</span><span class="ht-rili-money" style="color:#BFC4CA" data-money="'+ value[0].money
                        +'">&yen;'+ value[0].money +'</span></div>';
                }else if(is_array(nowDate,calendarDate[ind].opt.limit)){
                    var value=get_array(nowDate,calendarDate[ind].opt.limit);
                    riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                        +'"><span class="miao" >限</span>'
                        +'<ul class="miao_info"><li>特惠活动 :限时促销</li><li>促销价 :￥'+value[0].money
                        +'</li><li>促销数量 :'+value[0].num
                        +'</li><li>开始时间 :'+value[0].start_time
                        +'</li><li>结束时间 :'+value[0].end_time+'</li></ul>'
                        +'<span class="ht-rili-day">'+ (k + 1)
                        +'</span><span class="ht-rili-money"  style="color:#BFC4CA" data-money="'+ value[0].money
                        +'">&yen;'+ value[0].money +'</span></div>';
                }else{
                    riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr
                        +'"><ul class="miao_info"><li>当日票价 :￥'+calendarDate[ind].opt.money
                        +'</li><li>预售票数 :'+calendarDate[ind].opt.poll+'</li></ul><span class="ht-rili-day">'+ (k + 1)
                        +'</span><span class="ht-rili-money" style="color:#BFC4CA">&yen;'+ calendarDate[ind].opt.money +'</span></div>';
                }
            }
        }
        //下月数据
        for( var j = 0;j<(42 - calendarDate[ind].days - calendarDate[ind].monthStart);j++ ){//42-已占用表格数=剩余表格敿
            var dataDateStr = calendarDate[ind].nextYear + "-" + calendarDate[ind].nextMonth + "-" + (j + 1);
            riliHtml += '<div class="ht-rili-td ht-rili-td-disabled" data-date="'+ dataDateStr +'"><span class="ht-rili-day">'+ (j + 1) +'</span><span class="ht-rili-money"></span></div>';
        }
        if(obj){
            obj.find('.ht-rili-body').append(riliHtml);
        }else{
            $(calendarDate[ind].opt.ele+'  .ht-rili-body').append(riliHtml);
        }

        /*$('.ht-rili-onclick').on('click',function(){
         dateClick(this);
         })*/
        //鼠标悬浮事件
        /*$('.ht-rili-td').mouseover(function(){
         $(this).children('.miao_info').show();

         })*/
        $('.ht-rili-td').hover(function(){
            $(this).find('.miao_info').show();
        },function(){
            $(this).find('.miao_info').hide();
        })

    }

    //检查是否在二维数组里
    function is_array(nowDate,data){
        if(data){
            var result=false;
            $.each(data,function(i,item){
                if(nowDate==item.date){
                    result=true;
                }
            });
            return result;
        }
    }
    function get_array(nowDate,data){
        var result=[];
        $.each(data,function(i,item){
            if(nowDate==item.date){
                result.push(item);
            }
        });
        return result;
    }
    //是否是闰年
    function isLeapYear(ind){
        if( (calendarDate[ind].year % 4 == 0) && (calendarDate[ind].year % 100 != 0 || calendarDate[ind].year % 400 == 0) ){
            calendarDate[ind].isLeapYear = true;
        }else{
            calendarDate[ind].isLeapYear = false;
        }
    }
    //日期点击事件
    function dateClick(obj){
        $(obj).siblings().each(function(){
            $(this).removeClass('ht-rili-td-active');
        });
        $(obj).addClass('ht-rili-td-active');
    }

    //获取上个月份，本月，下个月份信息
    function getDays(ind){
        //上月天数
        if(  parseInt(calendarDate[ind].month) == 1 ){
            calendarDate[ind].lastDays = new Date(calendarDate[ind].year-1,12, 0).getDate();
            calendarDate[ind].lastMonth = new Date(calendarDate[ind].year-1,12, 0).getMonth()+1;
            calendarDate[ind].lastYear = new Date(calendarDate[ind].year-1,12, 0).getFullYear();
        }else{
            calendarDate[ind].lastDays = new Date(calendarDate[ind].year,calendarDate[ind].month-1, 0).getDate();
            calendarDate[ind].lastMonth = new Date(calendarDate[ind].year,calendarDate[ind].month-1, 0).getMonth()+1;
            calendarDate[ind].lastYear = new Date(calendarDate[ind].year,calendarDate[ind].month-1, 0).getFullYear();
        }
        //下个月天敿
        if( parseInt(calendarDate[ind].month) == 12 ){
            calendarDate[ind].nextDays  = new Date(calendarDate[ind].year+1,1, 0).getDate();
            calendarDate[ind].nextMonth  = new Date(calendarDate[ind].year+1,1, 0).getMonth()+1;
            calendarDate[ind].nextYear  = new Date(calendarDate[ind].year+1,1, 0).getFullYear();
        }else{
            calendarDate[ind].nextDays  = new Date(calendarDate[ind].year,calendarDate[ind].month+1, 0).getDate();
            calendarDate[ind].nextMonth  = new Date(calendarDate[ind].year,calendarDate[ind].month+1, 0).getMonth()+1;
            calendarDate[ind].nextYear  = new Date(calendarDate[ind].year,calendarDate[ind].month+1, 0).getFullYear();
        }
        //本月天数
        calendarDate[ind].days = new Date(calendarDate[ind].year,calendarDate[ind].month, 0).getDate();
    }
    //检测时间是否一臿
    function checkDate( dateStr1, dateStr2 ){
        var date1 = dateStr1.split("-"); //[0]year,[1]month,[2]date;
        var date2 = dateStr2.split("-"); //[0]year,[1]month,[2]date;
        if( date1[1] < 10 && date1[1].length < 2){
            date1[1] = "0"+date1[1];
        }
        if( date1[2] < 10 && date1[2].length < 2){
            date1[2] = "0"+date1[2];
        }
        if( date2[1] < 10 && date2[1].length < 2){
            date2[1] = "0"+date2[1];
        }
        if( date2[2] < 10 && date2[2].length < 2){
            date2[2] = "0"+date2[2];
        }
        date1 = date1.join("-");
        date2 = date2.join("-");
        return date1 == date2;
    }

    $.fn.extend({
        calendar:function(opt,ind){
            if( opt.beginDate != undefined && opt.endDate != undefined ){
                opt.data=getAll(opt.beginDate,opt.endDate,opt.money);
                var beginDate = opt.data[0].date;
                var endDate = opt.data[opt.data.length-1].date;
                calendarDate[ind]={today:'',year:'',month:'',date:'',day:'',beginYear:'',beginMonth:'',beginDate:'',endYear:'',endMonth:'',endDate:'',opt:'',container:''};
                calendarDate[ind].today = new Date();
                calendarDate[ind].year = calendarDate[ind].today.getFullYear();//当前年
                calendarDate[ind].month = calendarDate[ind].today.getMonth()+1;//当前月
                calendarDate[ind].date = calendarDate[ind].today.getDate();//当前旿
                calendarDate[ind].day = calendarDate[ind].today.getDay();//当前周几

                calendarDate[ind].beginYear = parseInt(beginDate.split('-')[0]);//起始年
                calendarDate[ind].beginMonth = parseInt(beginDate.split('-')[1]);//起始月
                calendarDate[ind].beginDate = parseInt(beginDate.split('-')[2]);//起始旿

                calendarDate[ind].endYear = parseInt(endDate.split('-')[0]);//结束年
                calendarDate[ind].endMonth = parseInt(endDate.split('-')[1]);//结束月
                calendarDate[ind].endDate = parseInt(endDate.split('-')[2]);//结束旿

                calendarDate[ind].year = parseInt(beginDate.split('-')[0]);//设置起始日期为当前日月
                calendarDate[ind].month = parseInt(beginDate.split('-')[1]);//设置起始日期为当前日月
                calendarDate[ind].date = parseInt(beginDate.split('-')[2]);//设置起始日期为当前日月
                calendarDate[ind].opt = opt;

            }else if( opt.data.length > 0 ){
                var beginDate = opt.data[0].date;
                var endDate = opt.data[opt.data.length-1].date;
                calendarDate[ind].today = new Date();
                calendarDate[ind].year = calendarDate[ind].today.getFullYear();//当前年
                calendarDate[ind].month = calendarDate[ind].today.getMonth()+1;//当前月
                calendarDate[ind].date = calendarDate[ind].today.getDate();//当前旿
                calendarDate[ind].day = calendarDate[ind].today.getDay();//当前周几

                calendarDate[ind].beginYear = parseInt(beginDate.split('-')[0]);//起始年
                calendarDate[ind].beginMonth = parseInt(beginDate.split('-')[1]);//起始月
                calendarDate[ind].beginDate = parseInt(beginDate.split('-')[2]);//起始旿

                calendarDate[ind].endYear = parseInt(endDate.split('-')[0]);//结束年
                calendarDate[ind].endMonth = parseInt(endDate.split('-')[1]);//结束月
                calendarDate[ind].endDate = parseInt(endDate.split('-')[2]);//结束旿

                calendarDate[ind].year = parseInt(beginDate.split('-')[0]);//设置起始日期为当前日月
                calendarDate[ind].month = parseInt(beginDate.split('-')[1]);//设置起始日期为当前日月
                calendarDate[ind].date = parseInt(beginDate.split('-')[2]);//设置起始日期为当前日月
                calendarDate[ind].opt = opt;

            }else{
                console.log('未传入beginDate或endDate＿');
            }
            //加载容器
            calendarDate[ind].container = '<div class="ht-rili-querybox"><strong class="ht-rili-title">'+ opt.title +'</strong><div class="ht-rili-datebox"><span class="ht-rili-leftarr" date-ind="'+ind+'"></span><span class="ht-rili-date"></span><span class="ht-rili-rightarr" date-ind="'+ind+'"></span></div></div><div class="ht-rili-head"><div class="ht-rili-th">周日</div><div class="ht-rili-th">周一</div><div class="ht-rili-th">周二</div><div class="ht-rili-th">周三</div><div class="ht-rili-th">周四</div><div class="ht-rili-th">周五</div><div class="ht-rili-th">周六</div></div><div class="ht-rili-body"><!--<div class="ht-rili-td"><span class="ht-rili-day">1</span><span class="ht-rili-money">&yen;100</span></div>--></div>'
            $(opt.ele).append(calendarDate[ind].container);
            $('.ht-rili-date').html(calendarDate[ind].year+'年 '+calendarDate[ind].month+'月');

            getIndexDay('',ind);
            $('.ht-rili-leftarr').off('click').on('click',function(){
                //console.log($(this).parent().parent().parent().attr('class'));
                var ind=$(this).attr('date-ind');
                $(this).parent().parent().parent().find('.ht-rili-body').html('');
                if( calendarDate[ind].month == 1 ){
                    calendarDate[ind].year -= 1;
                    calendarDate[ind].month = 12;
                }else{
                    calendarDate[ind].month -=1;
                }
                $(this).parent().find('.ht-rili-date').text(calendarDate[ind].year+'年 '+calendarDate[ind].month+'月');

                getIndexDay($(this).parent().parent().parent(),ind);
            })
            $('.ht-rili-rightarr').off('click').on('click',function(){
                var ind=$(this).attr('date-ind');
                $(this).parent().parent().parent().find('.ht-rili-body').html('');
                if( calendarDate[ind].month == 12 ){
                    calendarDate[ind].year += 1;
                    calendarDate[ind].month = 1;
                }else{
                    calendarDate[ind].month +=1;
                }
                $(this).parent().find('.ht-rili-date').text(calendarDate[ind].year+'年 '+calendarDate[ind].month+'月');
                getIndexDay($(this).parent().parent().parent(),ind);
            })
        },
        calendarGetActive: function(){//获取当前选中日期的倿
            //未选中时返回undefined
            var activeEle = $(this).find(".ht-rili-td-active");
            var date = activeEle.attr("data-date");
            var money = activeEle.children(".ht-rili-money").attr("data-money");
            return data = {
                date : date,
                money : money
            }
        }
    });
    Date.prototype.format=function (){
        var s='';
        s+=this.getFullYear()+'-';          // 获取年份。
        s+=(this.getMonth()+1)+"-";         // 获取月份。
        s+= this.getDate();                 // 获取日。
        return(s);                          // 返回日期。
    };
    function getAll(begin,end,money){
        var ab = begin.split("-");
        var ae = end.split("-");
        var db = new Date();
        db.setUTCFullYear(ab[0], ab[1]-1, ab[2]);
        var de = new Date();
        de.setUTCFullYear(ae[0], ae[1]-1, ae[2]);
        var unixDb=db.getTime();
        var unixDe=de.getTime();
        var arr=[];
        for(var k=unixDb;k<=unixDe;){
            arr.push({date:(new Date(parseInt(k))).format(),data:money});
            // console.log((new Date(parseInt(k))).format());
            k=k+24*60*60*1000;
        }
        return arr;
    }

})(jQuery)