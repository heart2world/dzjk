//公共函数库---基于属性驱动方式--需要页面加载完毕的方法

/****************cookie操作函数*************/
/*增加cookie值*/
window.addCookieVal = function(name /*名字*/ , val /*值*/ , out_time /*过期天数*/ ) {

	var date = new Date();
	date.setTime(date.getTime() + out_time * 24 * 3600 * 1000);
	document.cookie = name + "=" + escape(val) + ";expirs=" + date.toDateString();

}
/*删除cookie值*/
window.deleCookieVal = function(name) {

	var date = new Date();
	date.setTime(date.getTime() - 100000);
	document.cookie = name + "=0+;expires=" + date.toGMTString();

}
/*清除全部cookie值*/
window.cookieAllremove = function() {
	var cookiestring = document.cookie;
	var cookieVal = cookiestring.split(";");
	for(var i = 0; i < cookieVal.length; i++) {
		var subval = cookieVal[i].split("=");
		deleCookieVal(cookieVal[i].trim())
	}

}
/*获取cookie值*/
window.getCookieVal= function(name) {
	var cookiestring = document.cookie;
	var cookieVal = cookiestring.split(";")

	for(var i = 0; i < cookieVal.length; i++) {
		var subval = cookieVal[i].trim().split("=");
		if(subval[0] == name) {
			return unescape(subval[1])
		}
	}

};
$(function() {

	//		var webFont = (function(){
	//	    //移动端判断设备显示字体
	//		if(judge.platform() == "ios"){
	//			var str = "<style> body{ font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif!important;}</style>";
	//			$('head').append(str);
	//		}
	//		if(judge.platform()=="android"){
	//			var str = "<style>body{ font-family: 'RobotoRegular', 'Droid Sans', sans-serif!important;}</style>";
	//			$('head').append(str);
	//		}
	//	})()
	//判断是不是IOS
	//function isIOS() {
	//  var ua = navigator.userAgent.toLowerCase();
	//  if (/iphone|ipad|ipod/.test(ua)) {
	//      return true;
	//  } else if (/android/.test(ua)) {
	//      return false;
	//  }
	//}

	/*模拟下拉，只需要加select_dom属性即可,可以设置回调函数select_dom=func*/
	(function(dom) {
		if(dom.length==0)return;
		$("head").append("<style>[select_dom]{position:relative}[select_dom]>*:first-child{display:block;}[select_dom]>*:last-child{display:none;position:absolute;left:-1px;top:100%}[select_dom]>*:last-child>*{display:block;}</style>")
		dom.each(function() {
			var dom = $(this);
			var var_dom = dom.children().eq(0);
			var_dom.empty().append("<b class='fa fa-angle-down'></b>");
			var list_dom = dom.children().eq(dom.children().length - 1);
			var text_dom = document.createTextNode(list_dom.children().eq(0).text());
			var_dom.prepend(text_dom).click(function() {
				list_dom.toggle();
			});

			list_dom.children().each(function() {
				$(this).click(function() {
					var_dom[0].firstChild.nodeValue = $(this).text();
					list_dom.hide();
					if(dom.attr("select_dom"))
					  {
					  	eval(dom.attr("select_dom")+"(\'"+$(this).text()+"\')");
					  }
					
				})

			})
			dom.mouseleave(function() { list_dom.hide() }).css("display", "inline-block")

		})

	})($("[select_dom]"));

	/*点击切换事件函数*/

	/*首页幻灯片简便切换，按钮组*/
	//以按钮组驱动指向banner列表，data-bannerBtn=".className  banner列表DIV"
	(function click_showOtherDiv() {
		var dom = arguments[0];
		var bannerList = $(arguments[0].attr("data-bannerBtn")).children();
		var num = bannerList.length;
		bannerList.css({ "position": "absolute", "left": "0px", "top": "0px", "z-index": "1", "opacity": "0.6" });
		bannerList.eq(0).css({ "z-index": "10", "opacity": "1" })
		var ind = stop = 0;
		dom.children().click(function() {

			$(this).addClass("action").siblings().removeClass("action");
			ind = $(this).index();
			var curr = bannerList.eq(ind);
			dom.stop(true);
			bannerList.css("z-index", "1");
			curr.css("z-index", "10");
			curr.siblings().animate({ "opacity": "0.6" }, 1000);
			curr.animate({ "opacity": "1" }, 1000);
			clearInterval(stop);
			stop = setInterval(function() {
				ind = ind % num;
				dom.children().eq(ind).click();
				ind++;

			}, 4000);

		})

		stop = setInterval(function() {
			ind = ind % num;
			dom.children().eq(ind).click();
			ind++;

		}, 4000);

	}($("[data-bannerBtn]")));

	/******选项卡特效******/
	// 核心属性名 data-switchFor=".classname", .action
	(function items_swith() {

		if(arguments[0].length == 0) {
			return false;
		}
		var o = [],
			itemClass = null;
		var btn_array = arguments[0];
		while(btn_array.length > 0) {
			itemClass = btn_array.eq(0).attr("data-switchFor");

			o.push(itemClass);
			btn_array = btn_array.not("[data-switchFor='" + itemClass + "']");
		}
		while(o.length > 0) {
			(function() {
				var name = o.pop();
				var node = $("[data-switchFor='" + name + "']");
				var array_list = node.attr("data-switchFor").split("||");
				var forClass = array_list[0];
				var once = array_list[1];
				var dom = (node.length == 1) ? node.children() : node;
				dom.click(function(e) {

					if($(this).parents("[data-switchFor]").length > 0) {
						$(this).addClass("action").siblings().removeClass("action");
					}

					var ind = dom.index($(this));

					if($(forClass).length == 1) {

						var innerList = $(forClass).children();
						while(innerList.length == 1) {
							var innerList = innerList.children();
						}

						$(forClass).show();
						innerList.hide();
						innerList.eq(ind).show();
						if(innerList.find(".close_btn").length > 0) {
							innerList.find(".close_btn").click(function() {
								$(this).parents(forClass).hide();

							})

						}
					} else if($(forClass).length == dom.length) {

						$(forClass).eq(ind).show().siblings().hide();

					}
				})
				if(once != "no") {
					dom.eq(0).click();
				}
			}())
		}

	})($("[data-switchFor]"));

	/*显示--隐藏关联dome操作*/
	// 核心属性名 data-clickshowFor=".classname"
	(function click_showOtherDiv() {
		var dom = arguments[0];
		dom.click(function() {

			var fordom = $(this).attr("data-clickshowFor");

			$(fordom).show().find(".close_btn").click(function() {
				$(this).parents(fordom).hide();
			})

		})
	}($("[data-clickshowFor]")));

	/*表格全选框特效*/
	// 核心属性名 data-selectAll=".classname"
	(function click_selectAll() {
		var dom = arguments[0];
		dom.click(function() {
			var table = $(this).attr("data-selectAll");

			if($(this).prop("checked")) {
				$("[data-selectAll='" + table + "']").prop("checked", $(this).prop("checked")).prop("data-checked", "true");
				$("table" + table).find("tr").each(function() {

					$(this).find("*:first input[type=checkbox]").prop("checked", true).prop("data-checked", "true");

				})

			} else {

				$("[data-selectAll='" + table + "']").prop("checked", $(this).prop("checked")).removeAttr("data-checked");
				$("table" + table).find("tr").each(function() {
					$(this).find("*:first input[type=checkbox]").prop("checked", false).removeAttr("data-checked");

				})

			}

			$(table).find("tr  input[type=checkbox]").click(function() {

				if(dom.prop("checked")) {

					dom.prop("checked", false).removeAttr("data-checked");
				}

			})

		})
	}($("[data-selectAll]")));

	//全选效果 二
	function checked_all(dom) {

		dom.each(function() {
			var all_btn = $(this);
			var input_all = $(this).attr("data-selectAll2");

			if($(input_all)[0].nodeName == "input") {

				$(this).click(function() {
					if(!$(this).prop("checked")) {
						$(input_all).prop("checked", false)

					} else {
						$(input_all).prop("checked", true)

					}

				});
				$(input_all).click(function() {

					if(input_all.length == $(input_all + " input:checked").length) {
						all_btn.prop("checked", true)

					} else {

						all_btn.prop("checked", true)
					}
				})

			} else {
				$(this).click(function() {

					console.log($(input_all).find("input").length);
					if(!$(this).prop("checked")) {
						$(input_all).find("input").prop("checked", false)
					} else {
						$(input_all).find("input").prop("checked", true)
					}

				});
				$(input_all).find("input").click(function() {

					if($(input_all).length == $(input_all + " input:checked").length) {
						all_btn.prop("checked", true)
					} else {
						all_btn.prop("checked", false)
					}

				})

			}

		})

	};

	//可以用于ajax回调
	//	checked_all($("[data-selectAll2]"));

	/*倒计时封装*/
	function downTime(time) {
		//if(typeof end_time == "string")
		var end_time = new Date(time).getTime(), //月份是实际月份-1
			//current_time = new Date().getTime(),
			sys_second = (end_time - new Date().getTime()) / 1000;
		if(sys_second > 0) {

			sys_second -= 1;
			var day = Math.floor((sys_second / 3600) / 24);
			var hour = Math.floor((sys_second / 3600) % 24);
			var minute = Math.floor((sys_second / 60) % 60);
			var second = Math.floor(sys_second % 60);
			return { "date_day": day, "date_hours": (hour < 10 ? "0" + hour : hour), "data_minute": (minute < 10 ? "0" + minute : minute), "date_second": (second < 10 ? "0" + second : second) };
		}
	}

	//倒计时dom渲染,时间显示元素.days ,.hours,.minute,.second类
	(function show_downTime(dom) {
		dom.each(function() {

			var time = $(this).find("input").val();
			var sys_second;
			var that = $(this);
			var stop = setInterval(function() {

				if(sys_second <= 0) { clearInterval(stop) } else {
					var down_time = downTime(time);
					that.find(".days").text(down_time["date_day"] + "天");
					that.find(".hours").text(down_time["date_hours"]);
					that.find(".minute").text(down_time["date_minute"]);
					that.find(".second").text(down_time["date_second"]);

				}

			}, 1000)

		})
	})($(".down_time"));

	/*购物框物品数量增减*/
	// 核心属性名 .setNum 类
	(function click_setNum() {
		var dom = arguments[0];
		dom.click(function() {
			var val = parseInt($(this).siblings("input").val());
			var ind = $(this).parent().find(dom).index($(this));
			if(ind == 0) {
				$(this).siblings("input").val(val > 0 ? --val : 0);

			} else {
				var max = $(this).siblings("input").attr("max");

				if(max) {

					console.log(val);
					$(this).siblings("input").val(++val < max ? val : max);
				} else {
					$(this).siblings("input").val(++val);

				}

			}

		})
	}($(".setNum")));
	/*点击切换*/
	//核心属性 data-clickSwidth="className 切换当前活动类名"
	(function click_clickSwidth() {

		var dom = arguments[0];

		dom.click(function(e) {

			var active_class = $(this).attr("data-clickSwidth");
			var node_tag = $(e.target);
			while(node_tag.siblings(node_tag[0].nodeName).length < 1) {
				node_tag = node_tag.parent();

			}

			node_tag.has($(e.target)).addClass(active_class).siblings().removeClass(active_class);
		})
	}($("[data-clickSwidth]")));
	/*图片列表横排左右按钮LOOP滚动*/
	//核心属性  左右按钮 data-RunShow=".className 图片列表外"

	/*input iE8不支持:checked 处理方案*/
	(function set_checked() {

		var dom = arguments[0];

		dom.click(function(e) {

			if($(this).find("input[type=checkbox]").prop("checked")) {
				$(this).find("input").attr("data-checked", "true")

			} else {
				$(this).find("input").removeAttr("data-checked")

			}

		})
	}($("[data-checked]")));

	//验证邮箱
	function checkEmail(str) {
		return str.match(/[A-Za-z0-9_-]+[@](\\S*)(net|com|cn|org|cc|tv|[0-9]{1,3})(\\S*)/g);

	}
	//验证手机
	function checkMobilePhone(str) {

		return str.match(/^1[358][0-9]{9}$/);
	}

	/*ul li结构左右按钮无缝滚动*/
	/*核心属性 data-RunNoStop=".className||num 左右按钮类名||一屏数量"*/
	(function run_noStop() {
		var dom = arguments[0];
		dom.each(function() {
			var num = $(this).attr("data-RunNoStop").split("||")[1];
			var li_num = $(this).find("ul li").length;
			if(li_num <= num) {
				return;
			}
			var Ul = $(this).find("ul");
			var className = $(this).attr("data-RunNoStop").split("||")[0];
			var wid = Ul.find("li").outerWidth();
			$(this).width(num * wid);
			var list = Ul.find("li").clone();
			Ul.append(list.clone());
			Ul.append(list.clone());

			Ul.width(li_num * 3 * wid);
			Ul.css("position", "relative").css("left", -li_num * wid + "px");
			var that = $(this);
			var speed = 1;
			$(this).siblings(className).click(function() {
				var ind = that.siblings(className).index($(this));
				speed = (ind == 0 ? 1 : -1);

			})

			var stop = setInterval(function() {

				var position = parseInt(Ul.css("left"));
				console.log(position);

				if(position >= 0 || position <= -li_num * 2 * wid) {

					Ul.css("left", -li_num * wid + "px");
					position = parseInt(Ul.css("left"));
				}

				Ul.css("left", (position += speed) + "px");

			}, 30);
			Ul.mouseover(function() {
				clearInterval(stop);
			}).mouseout(function() {
				stop = setInterval(function() {
					var position = parseInt(Ul.css("left"));
					console.log(position);

					if(position >= 0 || position <= -li_num * 2 * wid) {
						Ul.css("left", -li_num * wid + "px");
						position = parseInt(Ul.css("left"));
					}
					Ul.css("left", (position += speed) + "px");
				}, 30);

			})
		})
	}($("[data-RunNoStop]")));
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	/*分页 特效*/
	/*分页器初始化*/
//	var fenye_option = {
//		"total": 155,
//		"target": 2,
//		"callback": function(index) {
//			console.log(index);
//		},
//		"start": function() {
//			showPageCommon(this);
//		}
//	};
//	fenye_option.start();
//
//	function showPageCommon(fenye_option) {
//		if(!fenye_option) return;
//		var page = fenye_option.target;
//		var total = fenye_option.total;
//		var callback = fenye_option.callback;
//
//		var add_html = function(page, total) {
//			var str = '<a class="color-red" id="js_nowpage">' + page + '</a>';
//			for(var i = 1; i <= 3; i++) {
//				if(page - i > 1) {
//					str = '<a >' + (page - i) + '</a> ' + str;
//				}
//				if(page + i < total) {
//					str = str + ' ' + '<a >' + (page + i) + '</a> ';
//				}
//			}
//			if(page - 4 > 1) {
//				str = '... ' + str;
//			}
//
//			if(page > 1) {
//				str = '<span class="js_last">' + '上一页 ' + '</span>' + '<a >' + 1 + '</a> ' + ' ' + str;
//			}
//
//			if(page + 4 < total) {
//				str = str + ' ...';
//			}
//
//			if(page < total) {
//				str = str + ' ' + '<a >' + total + '</a> ' + '<span class="js_next">' + '下一页 ' + '</span>';
//			}
//
//			$(".fenye").html(str);
//		};
//		add_html(page, total);
//		$('body').on('click', '.fenye a', function() {
//			page = parseInt($(this).text());
//
//			$(this).parents('.fenye').empty();
//			add_html(page, total);
//			callback && callback(page);
//
//		});
//		$('body').on('click', '.js_last', function() {
//			var page = parseInt($('#js_nowpage').text());
//
//			$(this).parents(".fenye").empty();
//			add_html(page - 1 < 1 ? 1 : page - 1, total);
//			callback && callback(page - 1);
//		});
//		$('body').on('click', '.js_next', function() {
//			var page = parseInt($('#js_nowpage').text());
//			$(this).parents(".fenye").empty();
//			add_html(page + 1 > total ? total : page + 1, total);
//			callback && callback(page + 1);
//		});
//
//	};
	/*select 下拉样式美化*/
	(function() {
		$("select.beautify").each(function() {
			$(this).width($(this).width() + 20);
			var the_wid = $(this).outerWidth() - 2;
			var hgt = $(this).outerHeight()
			var bg_color = $(this).css("background-color");
			$(this).wrap("<span style='width:" + the_wid + "px;height:" + hgt + "px;background-color:" + bg_color + ";display:inline-block;position:relative'></span>");
			var border_color = $(this).css("border-left-color");
			$(this).css({ "border": "0", "outline": "none", "opacity": "0", "vertical-align": "top" }).css({ "position": "absolute", "left": "-1px", "top": "1px", "z-index": "2" });
			$(this).parents("span").css("border", "1px solid " + border_color);
			$(this).parents("span").append("<em style='display: inline-block;width:100%;height: 100%;line-height: " + hgt + "px;left:0px;background-color: inherit;font-style:normal;padding-right:20px;box-sizing:border-box;overflow:hidden;padding-left:5px;word-break:break-all'>" + $(this).find("option:first").text() + "</em>")
			$(this).parents("span").append("<i style='position: absolute;right:5px;top:50%; margin-top: -4px;border-top:8px solid red;border-color: inherit;border-left: 4px solid transparent;border-right: 4px solid transparent;display:inline-block;width:0;height:0'></i>")
			$(this).change(function() {
				$(this).siblings("em").text($(this).find("option:selected").text())
			})
		})
	})();

	/*底部滑出层对齐,需要对齐的项目类名为‘center_align’*/
	(function cneter_align(dom) {
		dom.each(function() {
			var max_wid = 0;
			$(this).find(".center_align").each(function() {
				if($(this).width() > max_wid) max_wid = $(this).outerWidth();
			});
			$(this).find(".center_align").css("margin-left", -max_wid / 2 + "px")

		})

	})($(".float_div"));

	/*产品进度条显示*/
	(function progress_bar(dom) {
		if(dom.length == 0) return;
		dom.each(function() {
			var wid = $(this).find("input").val() + "%";
			$(this).children().css("width", wid)

		})
	})($(".progress_bar .bar"));

	/*上传图片功能，3张或者更多*/
	/*上传图片初始化，3张*/
	(function up_loadfile(list_box) {
		//input file change之后的动作封装
		var after_preview = function(dom) {
			dom.parents("div").eq(0).addClass("imgfile_show");
			dom.parent("label").siblings(".dele").click(function() {
				$(this).parents(".imgfile_show").removeClass("imgfile_show").appendTo($(this).parent(".imgdiv").parent());
				dom.siblings("img").prop("src", "img/slice/add.png");
				$("body").append("<form id='clear_erea'></form>");
				dom.appendTo($("#clear_erea"))
				$("#clear_erea")[0].reset();
				$("#clear_erea").find("input").appendTo($(this).siblings("label"));
				$("#clear_erea").remove();
			})

		}
		if($(".up_img_more,.up_img,.up_img_one").length) {
			var img_files = $(".up_img_more,.up_img,.up_img_one"); //初始页面必须至少有一个上传控件
			var files_num = img_files.length;
			/*上传图片初始化，多张*/
			if(img_files.length) {
				new uploadPreview({ UpBtn: img_files });
				img_files.change(function(e) {
					after_preview($(e.target))
				});
				img_files.parents("[class*='upload_img']").parent().bind("DOMNodeInserted", function(e) {
					if($(e.target).prop("class").indexOf("upload_img") >= 0 && $(e.target).find("input[type=file]").length) {
						new uploadPreview({ UpBtn: $(e.target).find("input[type=file]") });
						$(e.target).find("input[type=file]").change(function() {
							after_preview($(this))

						})

					}

				})

			}

		} else {
			list_box.each(function() {
				var dom = $(this);
				var img_files = dom.find(".up_img_more,.up_img,.up_img_one"); //初始页面必须至少有一个上传控件
				var files_num = img_files.length;
				if(img_files.length) {
					new uploadPreview({ UpBtn: img_files });
					img_files.change(function(e) {
						after_preview($(e.target))
					});

				};
				dom.bind("DOMNodeInserted", function(e) {
					if($(e.target).children().eq(-1).find("input[type=file]").length) {

						new uploadPreview({ UpBtn: $(e.target).find("input[type=file]") });
						$(e.target).find("input[type=file]").change(function() {
							after_preview($(this))

						})
					}

				})
			})

		}

	})($(".upload_erea"));

	/*IE9表单占位文字不显示处理的JS方案*/
	(function() {
		if(navigator.userAgent.indexOf("MSIE") > 0) {
			if(navigator.userAgent.indexOf("MSIE 9.0") > 0) {
				$("head").append($('<style type="text/css">*[placeholder]._default{color:#ccc!important;}</style>'));
				$("input[placeholder],textarea[placeholder]").each(function() {

					var default_txt = $(this).attr("placeholder").trim();
					if($(this).attr("type") == "password") {
						if($(this).val() == undefined || $(this).val().trim() == "") {
							$(this).attr("type", "text").val(default_txt).addClass("_default");
						}

						$(this).focus(function() {
							if($(this).val().trim() == default_txt) {
								$(this).attr("type", "password").val("").removeClass("_default")
							}
						}).blur(function() {
							if($(this).val().trim() == "" || $(this).val().trim() == default_txt) {
								$(this).attr("type", "text").val(default_txt).addClass("_default");
							}
						})

					} else {
						if($(this).val() == undefined || $(this).val().trim() == "") {
							$(this).val(default_txt).addClass("_default");
						}
						$(this).focus(function() {
							if($(this).val().trim() == default_txt) {
								$(this).val("").removeClass("_default")
							}
						}).blur(function() {
							if($(this).val().trim() == "" || $(this).val().trim() == default_txt) {
								$(this).val(default_txt).addClass("_default");
							}
						})

					}

				})
			}
		}
	})();

	/*点击弹出框禁止body滚动*/
	(function(dom) {
		dom.bind("touchstart", function() {
			$("body").css("pointer-events", "none")
		}).bind("touchend", function() {
			$("body").css("pointer-events", "auto")
		})
	})($("[touch_lockBody]"))

});




//简单封装上传图片插件-上传预览加裁剪传参数
function upload_img(btn,callBack){
	new uploadPreview({ UpBtn: btn},callBack);
};
$.fn.extend({  
        setImgload:function(param){
        
        	$(this).each(function(){
        		    var that=$(this);
     		        var options=param;
        		    var parentDom=$(this).parent();
        		    parentDom.prop("id","imgUp_erea"+parentDom.index())
		            if(!$("link[href*='imgareaselect']").length)
		        	{
		        	    $('<link rel="stylesheet" href="css/imgareaselect-default.css"/>').insertAfter($("head link:last-of-type"));
		        	};
		        	if(!$("script[src*='imgareaselect']").length)
		        	{
		        		$('<script type="text/javascript" src="lib/jquery.imgareaselect.dev.js" ></script>').insertAfter($("head script:last-of-type")) 
		        	};


		             //获得插件实例，以便调用获取各种参数
                   
		             //设置上传参数
		             function setParamVal(img,selection){
		             	 //抓取祖先元素
				        	 var file_parent=$(img).parent();
				             while(!file_parent.find("input[type=file]"))
				             {
				             	file_parent=file_parent.parent();
				             };
		             
		                    var _img=document.createElement("img");
		                    _img.src=$(img).attr("src");
		                    
		            		var wid_ratio=_img.width/$(img).width();
		            		var hgt_ratio=_img.height/$(img).height();
		            		
		            		if(file_parent.find("input[type=file]").val())
		            		{
		            			var imgName=file_parent.find("input[type=file]").val().split("\\").reverse()[0];
		            		}else{
            				   if($(img).prop("src").indexOf("upload_placeholder")<0)
                                {
                                	imgName=$(img).prop("src").split("/").reverse()[0];
                                }else{
                                	var imgName="";
                                }
	
		            		};
		            	
		            		file_parent.find("input[type=hidden]").val(JSON.stringify({
		            			"imgName":imgName,
		            			"left": Math.floor(selection.x1*wid_ratio),
		            			"top":Math.floor(selection.y1*hgt_ratio),
		            			"width":Math.floor(selection.width*wid_ratio),
		            			"height":Math.floor(selection.height*hgt_ratio)
		            		}));
		             };
		             
		             //设置初始位置
		             function setIni_position(img,wid,hgt){
		             	   var item=$(img).imgAreaSelect({ instance: true });
		             	   var left=$(img).width()/2-wid/2;
		             	   var top=$(img).height()/2-hgt/2;
		             	   item.setOptions({show:true});
		            	   item.setSelection(left,top,left+wid,top+hgt);
		            	   item.update();
          	               setParamVal(img,{x1:left,y1:top,width:left+wid,height:top+hgt});
 
		             };
		             //设置预览图
		            
		             function setPreviewCanvas(canvasDom,img,options){
                          var item=$(img).imgAreaSelect({ instance: true });
		                  var select_json=item.getSelection();
		                  var wid_ratio=select_json.width/$(img).width();
		                  var hgt_ratio=select_json.height/$(img).height();
		                  var canvas_wid=canvasDom.width;
                          var canvas_hgt=canvasDom.height;
		                  var wid_ratio2=select_json.width/canvas_wid;
		                  var hgt_ratio2=select_json.height/canvas_hgt;
		             	  canvasDom.getContext("2d").drawImage(img,-(select_json.x1/wid_ratio2),-(select_json.y1/hgt_ratio2),canvas_wid/wid_ratio,canvas_hgt/hgt_ratio);
            	     
		             }
		             //上传图片后的回调
		            function select_adjust(img){
                            setIni_position(img[0],defaults.minWidth,defaults.minWidth/(defaults.aspectRatio.split(":")[0]/defaults.aspectRatio.split(":")[1]));
                            setPreviewCanvas(img.next(".canvas_box").find("canvas")[0],img[0],options);
		    		};
              
		            var defaults = {
		            	aspectRatio:options.ratio?options.ratio:"1:1",
		            	handles:true,
		            	movable:true,
		            	minWidth:250,
		            	show:true,
		            	parent:$("#"+$(this).parent().attr("id")),
		            	preview:[false],//设置参数格式例如[true,"10:1"]
		            	onInit:function(img, selection){
		            		var that=$(img);
		                    setIni_position(img,defaults.minWidth,defaults.minWidth/(defaults.aspectRatio.split(":")[0]/defaults.aspectRatio.split(":")[1]));
		                    var bilv=(options.preview[1].split(":")[0]/options.preview[1].split(":")[1]);
		                    if(options.preview[0])
		                    {
		                     
		                    	 $("<div class='canvas_box'></div>").css({"display":"inline-block","width":"0","height":"0","vertical-align":"top","position":"relative","overflow":"visible"}).insertAfter($(img));
		                    	 $("<canvas class='preview_box'></canvas>").css({"position":"absolute","left":"10px","top":"0"}).appendTo($(img).next(".canvas_box"));
                                 $(img).next(".canvas_box").find("canvas")[0].width=that.width()/bilv;
                                 $(img).next(".canvas_box").find("canvas")[0].height=(that.width()/bilv)/(options.aspectRatio.split(":")[0]/options.aspectRatio.split(":")[1]);
                                 setPreviewCanvas( $(img).next(".canvas_box").find("canvas")[0],img,options);
		                    };
		                    upload_img($(img).parent().find("input[type=file]"),select_adjust);
		            	},
		            	onSelectChange:function(img, selection){
		            		setPreviewCanvas($(img).next(".canvas_box").find("canvas")[0],img,options)
		            		setParamVal(img,selection)
		            	},
		            	onSelectEnd:function(img, selection){
		            	   setPreviewCanvas($(img).next(".canvas_box").find("canvas")[0],img,options);
		                   setParamVal(img,selection)
		            	}
		            };
		            options = $.extend({},defaults, options);
		            $(this).imgAreaSelect(options);
		            

		            
	
        	});
        	
 
        }  
}); 
/*alert ,confirm 封装*/


var go_alert = function() {
		if(arguments.length==1&& typeof arguments[0]=="string")
		{
			var json={
				msg:arguments[0],
				title_text:"",
				agree_text:"确定",
				text_align:"center",
				extra_dom:{
					sort:2,
					html:"",
				},
				tag_set:{
					title_tag:"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					agree_tag:"",
				},
				
				
			}
		
		}else if(typeof arguments[0]=="object")
		{
			var json=$.extend({
				msg:"你还没有提供任何可展示的信息！",
				title_text:"",
				agree_text:"确定",
				text_align:"center",
				yesFunc:false,//回调函数
				extra_dom:{
					sort:2,
					html:"",
				}
			},arguments[0])
			json.tag_set={
					title_tag:json.tag_set.title_tag?("<img src='"+json.tag_set.title_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.title_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.title_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					agree_tag:json.tag_set.agree_tag?("<img src='"+json.tag_set.agree_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.agree_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.agree_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					cancel_tag:json.tag_set.cancel_tag?("<img src='"+json.tag_set.cancel_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.cancel_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.cancel_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位	
			}
	
		}

	$("body").append("<div class='alert_box' style='position:fixed;z-index:1500;width:100%;opacity:1;height:100%;left:0;top:0px;background-color:rgba(68,68,68,0.4);'><div class='content' style='width:260px;text-align:center;position:absolute;overflow: hidden;font-size:14px;left:50%;top:50%; transform:translateY(-50%) translateX(-50%);-webkit-transform:translateY(-50%) translateX(-50%);background-color:#fff;border-radius:5px'><h4 style='padding-top:15px'>"+json.tag_set.title_tag+json.title_text+"</h4><div style='padding:10px 20px; line-height:25px; border-bottom:1px solid #ccc;color:#999;font-size:14px'><div>"+(json.extra_dom.sort==1?json.extra_dom.html:"")+"</div><span style='display:inline-block;text-align:"+json.text_align+"'>" + json.msg + "</span><div>"+(json.extra_dom.sort==2?json.extra_dom.html:"")+"</div></div><div><button style='cursor:pointer;padding:10px 0px; background-color:#fff;border:0;display:block;width:100%;text-align:center;color:#ec3569;font-size:14px;outline:none;'>"+json.tag_set.agree_tag+json.agree_text+"</button><div>");
	$(".alert_box .content button").click(function() {
		var that = $(this);
		setTimeout(function() {
			that.parents(".alert_box").css({ "opacity": "0", "width": "0%", "height": "0" })
			setTimeout(function() { that.parents(".alert_box").remove() }, 300);
            json.yesFunc&&json.yesFunc();
		}, 500)

	})
};
var go_alert2 = function(msg,url_or_func,bgcolor) {
	if(bgcolor&&bgcolor.bgcolor)
	{
		$("body").append("<div class='alert_box' style='position:fixed;z-index:15000;width:100%;opacity:1;height:100%;left:0;top:0px;background-color:rgba(68,68,68,0.6);'><div class='content' style='max-width:60%;text-align:center;position:absolute;font-size:14px;left:50%;top:50%; transform:translateY(-50%) translateX(-50%);-webkit-transform:translateY(-50%) translateX(-50%);background-color:rgba(68,68,68,0.6);border-radius:6px; overflow:hidden'><P style='padding:10px 25px; line-height:25px; background-color:"+bgcolor.bgcolor+";color:#666;font-size:14px'>" + msg + "</P><div><div>");
	}else{
	
	    $("body").append("<div class='alert_box' style='position:fixed;z-index:15000;width:100%;opacity:1;height:100%;left:0;top:0px;background-color:transparent;'><div class='content' style='max-width:60%;text-align:left;position:absolute;font-size:14px;left:50%;top:50%; transform:translateY(-50%) translateX(-50%);-webkit-transform:translateY(-50%) translateX(-50%);background-color:rgba(68,68,68,0.6);border-radius:6px; '><P style='padding:10px 25px; color:#333;font-size:14px;color:#fff'>" + msg + "</P><div><div>");
	
	}

	setTimeout(function() {
		$(".alert_box").css({ "opacity": "0", "width": "0%", "height": "0" })
		setTimeout(function() { $(".alert_box").remove() }, 300);
		if (typeof url_or_func == "function")
		{ url_or_func() }
		else if (typeof url_or_func == "string")
		{ window.location.href = url_or_func; }
	}, 2000)

};
var go_confirm = function()
	{
		if(arguments.length==1&& typeof arguments[0]=="string")
		{
			var json={
				msg:arguments[0],
				title_text:"",
				agree_text:"确定",
			    cancel_text:"取消",
				text_align:"center",
				extra_dom:{
					sort:2,
					html:"",
				},
				tag_set:{
					title_tag:"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					agree_tag:"",
					cancel_tag:"",	
				}
				
			}
		
		}else if(arguments.length>=2){
	
			var json={
				msg:arguments[0],
				yesFunc:arguments[1],
				title_text:"",
				agree_text:"确定",
			    cancel_text:"取消",
				text_align:"center",
				tag_set:{
					title_tag:"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					agree_tag:"",
					cancel_tag:"",	
				},
				extra_dom:{
					sort:2,
					html:"",
				}
			}

		}else if(typeof arguments[0]=="object")
		{
			
			var json=$.extend({
				msg:"你还没有提供任何可展示的信息！",
				title_text:"",
				agree_text:"确定",
			    cancel_text:"取消",
				text_align:"center",
				yesFunc:false,//回调函数
				noFunc:false,//回调函数
				extra_dom:{
					sort:2,
					html:"",
				}
			},arguments[0])
			json.tag_set={
					title_tag:json.tag_set.title_tag?("<img src='"+json.tag_set.title_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.title_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.title_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					agree_tag:json.tag_set.agree_tag?("<img src='"+json.tag_set.agree_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.agree_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.agree_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位
					cancel_tag:json.tag_set.cancel_tag?("<img src='"+json.tag_set.cancel_tag.split(",")[0]+"'style='display:inline-block;vertical-align:middle;margin-right:5px;width:"+(json.tag_set.cancel_tag.split(",")[1]||"auto")+";height:"+(json.tag_set.cancel_tag.split(",")[2]||"auto")+"'/>"):"",//可以为字符串"url,wid,hgt" 路径加宽度高度,可以任何单位	
			}
	
		}


	$("body").append("<div class='confirm_box' style='position:fixed;z-index:1500;width:100%;opacity:1;height:100%;left:0;top:0px;background-color:rgba(68,68,68,0.4);'><div class='content' style='width:260px;text-align:center;position:absolute;overflow: hidden;font-size:14px;left:50%;top:50%; transform:translateY(-50%) translateX(-50%);-webkit-transform:translateY(-50%) translateX(-50%);background-color:#fff;border-radius:5px'><h4 style='padding-top:15px'>"+json.tag_set.title_tag+json.title_text+"</h4><div style='padding:10px 20px; line-height:25px; border-bottom:1px solid #ccc;color:#999;font-size:14px'><div>"+(json.extra_dom.sort==1?json.extra_dom.html:"")+"</div><span style='display:inline-block;text-align:"+json.text_align+"'>" + json.msg + "</span><div>"+(json.extra_dom.sort==2?json.extra_dom.html:"")+"</div></div><div><button style='cursor:pointer;padding:10px 0px; background-color:#fff;border:0;display:inline_block;width:48%;;color:#999;font-size:14px;outline:none; vertical-align:middle'>"+json.tag_set.cancel_tag+json.cancel_text+"</button><button style='cursor:pointer;padding:10px 0px; background-color:#fff;border:0;display:inline_block;width:48%;color:#ec3569;font-size:14px;outline:none;border-left:1px solid #ccc;vertical-align:middle'>"+json.tag_set.agree_tag+json.agree_text+"</button><div>");
	
	$(".confirm_box .content button").click(function() {

		var that = $(this);
		setTimeout(function() {
			that.parents(".confirm_box").css({ "opacity": "0", "width": "0%", "height": "0" });
			setTimeout(function() { that.parents(".confirm_box").remove() }, 0);
		}, 0)
		if($(this).text() == json.agree_text) {
			json.yesFunc && json.yesFunc();
		}else if($(this).text() == json.cancel_text){
			json.noFunc && json.noFunc();
		}
	})

};


$(function() {

	/*弹出框按钮绑定*/
	(function(btn) {
		btn.each(function() {
			var msg = $(this).attr("go_alert");

			$(this).click(function() {
				go_alert(msg);
			})
		})

	})($("[go_alert]"));

	(function(btn) {
		btn.each(function() {
			var msg = $(this).attr("go_confirm");
			$(this).click(function() {
				go_confirm(msg);
			})
		})

	})($("[go_confirm]"));

});

/*发送验证码，60秒等待效果*/
//核心属性  .identify_btn  针对input[type=button],button; func发送验证码回调函数,val值为电话号码
function click_identify(dom, func, val) {
	var downTime = 60;
	dom.click(function() {
		$(this).attr("disabled", true);
		func && func(val);

		if($(this)[0].nodeName == "INPUT") {

			var stop = setInterval(function() {

				if(downTime > 0) {
					$(this).attr("disabled", true);
					$(this).val((--downTime) + "秒后获取");
				} else {
					clearInterval(stop);
					$(this).attr("disabled", false);
					that.text("发送验证码");
					downTime = 60;
				}

			}, 1000)

		} else {

			$(this).text((--downTime) + "秒后获取");
			var that = $(this);
			var stop = setInterval(function() {

				if(downTime > 0) {
					that.attr("disabled", true);
					that.text((--downTime) + "秒后获取");
				} else {
					clearInterval(stop);
					that.attr("disabled", false)
					that.text("发送验证码");
					downTime = 60;
				}
			}, 1000)
		}
	})

};

/*验证回调发送验证码  2种验证类型tel,id_number;func 发送验证码回调函数*/
var identify_form = function(dom, func) {

	$(".identify_btn").click(function() {
		go_alert("请输入手机号")
	})
	dom.change(function() {

		switch($(this).attr("data-identify")) {
			case "tel":
				var tel_reg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
				if($(this).val() != " " && tel_reg.test($(this).val())) {
					click_identify($(".identify_btn"), func, $(this).val())
				} else {
					go_alert("请输入正确的手机号");
					$(".identify_btn").unbind("click");

				}

				break;

		}

	})
	dom.blur(function() {
		$(".identify_btn").unbind("click");
		dom.trigger("change");

	})

};

/*评星效果*/
var set_stars = function(params) {
	var img_json = params.img_url;
	var img_wid = (params.img_wid == undefined) ? "" : params.img_wid;
     if(!params.updated_repeat)
     {
        var startDoms=params.stars_box.not("._inited")
     	
     }else{
     	
     	var startDoms=params.stars_box
     }
     
	startDoms.each(function() {
	    var input_val=$(this).find("input[type=hidden]").val();
		var stars_number = params.starsNum.equel?input_val:params.starsNum.number;
		var num = $(this).find("input[type=hidden]").val();
		$(this).find("img").remove();
		$(this).append("<img class='star' src='" + img_json.filled + "' width='" + img_wid + "' style='display:inline-block;margin-right:5px'/>")
		var wid = $(this).find("img:first").width();
		var hgt = $(this).find("img:first").height();
		while(--stars_number) {
			$(this).append($(this).find("img:last").clone());
		};
		
	
		if( input_val> 0&&input_val.indexOf(".")<0) {

			$(this).find("img.star:gt(" + (input_val - 1) + ")").attr("src", img_json.empty);
		} else if(input_val> 0&&input_val.indexOf(".")>0){
		
			$(this).find("img.star:gt(" + (Math.floor(input_val) - 1) + ")").attr("src", img_json.empty);
			$(this).find("img.star:eq(" + Math.floor(input_val)  + ")").attr("src", img_json.half)
		}
        $(this).addClass("_inited");
		if(params.readonly) return;
		$(this).find("img.star").click(function() {

			var value = $(this).siblings("input[type=hidden]").val();

			var ind = $(this).parent().find("img.star").index($(this));

			if($(this).attr("src") == img_json.filled) {
				
				if($(this).parent().find("img[src='"+img_json.filled+"']").length<=params.minVal)
				{
					return;
				}
				if($(this).next("[src='" + img_json.filled + "']").length) {
					$(this).parent().find("img.star").attr("src", img_json.empty)
					$(this).parent().find("img.star:lt(" + (ind + 1) + ")").attr("src", img_json.filled);

				} else {
					$(this).parent().find("img.star").attr("src", img_json.empty)
					$(this).parent().find("img.star:lt(" + ind + ")").attr("src", img_json.filled);

				}

			} else {
				
				$(this).parent().find("img.star").attr("src", img_json.filled)
				$(this).parent().find("img.star:gt(" + ind + ")").attr("src", img_json.empty);

			}
			var val = $(this).parent().find("img.star[src='" + img_json.filled + "']").length;
			$(this).parent().find("input[type=hidden]").val(val);
		})

	});

};
//调用方法
//	 var img_url={filled:"img/slice/godstar.png",empty:"img/slice/badstar.png"};
//	set_stars($(".set_stars"),img_url,5,25,"onlyread")

/*多行文本超出隐藏,并显示省略号*/
//	function more_txtHide(dom){
//	 if(!dom.length)return;
//	   dom.each(function(){
//		     var txt=$(this).text();
//		     var len=txt.length;
//		     var hgt=$(this).height();
//		     $(this).css("max-height","none");
//		     if($(this).height()>hgt)
//		     {
//		     	while($(this).height()>hgt)
//		     	{
//		     		$(this).empty().text(txt.slice(0,--len));
//		     		$(this).append($("<i>...</i>"));
//		     	}
// 	
//		     }
//
//	   })
//	};
//	/*此处可用于ajax 回调*/
//	more_txtHide($(".txt_ellipsis"));