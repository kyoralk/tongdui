/**
 * 
 */
$(function(){
    //首页Tab标签卡滑门切换
    $(".tabs-nav > li > h3").bind('mouseover', (function(e) {
    	if (e.target == this) {
    		var tabs = $(this).parent().parent().children("li");
    		var panels = $(this).parent().parent().parent().children(".tabs-panel");
    		var index = $.inArray(this, $(this).parent().parent().find("h3"));
    		if (panels.eq(index)[0]) {
    			tabs.removeClass("tabs-selected").eq(index).addClass("tabs-selected");
    			panels.addClass("tabs-hide").eq(index).removeClass("tabs-hide");
    		}
    	}
    }));

    var settings = new Array();
    settings['start_opacity']=1;
    settings['high_opacity']=1;
    settings['low_opacity']=.5;
    settings['timing']=200;
    
    $(".jfocus-trigeminy > ul > li > a").hover(function(){

    	$(this).stop().animate({opacity: settings.high_opacity}, settings.timing); //100% opacity for hovered object
		$(this).siblings().stop().animate({opacity: settings.low_opacity}, settings.timing); //dimmed opacity for other objects
    	
    	
    	//$(".jfocus-trigeminy > ul > li > a").attr("style","opacity:0.5");
    	//$(this).attr("style","opacity:1");
    },function(){
    	$(this).stop().animate({opacity: settings.start_opacity}, settings.timing); //return hovered object to start opacity
		$(this).siblings().stop().animate({opacity: settings.start_opacity}, settings.timing); // return other objects to start opacity
    	//$(".jfocus-trigeminy > ul > li > a").attr("style","opacity:1");
    });
});
