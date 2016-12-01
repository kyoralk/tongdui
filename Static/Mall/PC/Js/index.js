/**
 * 首页用的js
 */
/*banner下小图左右切换*/
function Move(btn1,btn2,box,btnparent,shu){
	var llishu=$(box).first().children().length;
	var liwidth=$(box).children().width();
	var boxwidth=llishu*liwidth;
	var shuliang=llishu-shu;
	$(box).css('width',''+boxwidth+'px');
	var num=0;
	$(btn1).click(function(){
		num++;
		if (num>shuliang) {
			num=shuliang;
		}
		var move=-liwidth*num;
		$(this).closest(btnparent).find(box).stop().animate({'left':''+move+'px'},500);
	});
	$(btn2).click(function(){
		num--;
		if (num<0) {
			num=0;
		}
		var move=liwidth*num;
		$(this).closest(btnparent).find(box).stop().animate({'left':''+-move+'px'},500);
	})
}
$(function(){
	
    //首页Tab标签卡滑门切换
    $(".tabs-nav > li > h3").bind('mouseover', (function(e) {
    	if (e.target == this) {
    		var tabs = $(this).parent().parent().children("li");
    		var panels = $(this).parent().parent().parent().children(".tabs-panel");
    		var index = $.inArray(this, $(this).parent().parent().find("h3"));
    		if (panels.eq(index)[0]) {
    			tabs.removeClass("tabs-selected").eq(index).addClass("tabs-selected");
    			var color = $(this).parents(".floor:first").attr("color");
    			$(this).parents(".tabs-nav").find("h3").css({"border-color": "", "color": ""});
    			$(this).css({"border-color": color + " " + color + " #fff", "color": color});
    			panels.addClass("tabs-hide").eq(index).removeClass("tabs-hide");
    		}
    	}
    }));
	
	//首页楼层Tab标签卡滑门切换
    $(".floor-tabs-nav > li").bind('mouseover', (function(e) {
		var color = $(this).parents(".floor").attr("color");
    	$(this).addClass('floor-tabs-selected').siblings().removeClass('floor-tabs-selected');
		$(this).find('h3').css({'border-color': color + ' ' + color + ' #fff', 'color': color}).parents('li').siblings('li').find('h3').css({'border-color':'','color':''});
		$(this).parents('.floor-con').find('.floor-tabs-panel').eq($(this).index()).removeClass('floor-tabs-hide').siblings().addClass('floor-tabs-hide');
    }));

	
});
/*首页左侧楼层定位*/
$(function() {	
		var conTop = $(".floor-list").offset().top;
		$(window).scroll(function() {
			var scrt = $(window).scrollTop();
			if (scrt > conTop) {
				
				$(".elevator").show("fast", function() {
					$(".elevator-floor").css({
						
						"-webkit-transform": "scale(1)",
						"-moz-transform": "scale(1)",
						"transform": "scale(1)",
						"opacity": "1"
					})
				}).css({
					"visibility": "visible"
				})
			} else {
				$(".elevator-floor").css({
					"-webkit-transform": "scale(1.2)",
					"-moz-transform": "scale(1.2)",
					"transform": "scale(1.2)",
					"opacity": "0"
				});
				$(".elevator").css({
					"visibility": "hidden"
				})
			}
			setTab()
		});
		var arr = [],
			fsOffset = 0;
		for (var i = 1; i < $(".floor").length; i++) {
			arr.push(parseInt($(".floor").eq(i).offset().top) + 30)
		}
		$(".elevator-floor a.smooth").on("click", function() {
			var _th = $(this);
			_th.blur();
			var index = $(".elevator-floor a.smooth").index(this);
			if (index > 0) {
				fsOffset = 50
			}
			var hh = arr[index];
			$("html,body").stop().animate({
				scrollTop: hh - fsOffset + "px"
			}, 400)
		});
		$(".elevator-floor a.fsbacktotop").click(function() {
			$("html,body").stop().animate({
				scrollTop: 0
			}, 400)
		})

	function setTab() {
		var Objs = $(".floor:gt(0)");
		var textSt = $(window).scrollTop();
		
		for (var i = Objs.length - 1; i >= 0; i--) {

			if (textSt >= $(Objs[i]).offset().top - 300) {
				$(".elevator-floor a").eq(i).addClass("active").siblings().removeClass("active");
				return
			}
		}
	}
});