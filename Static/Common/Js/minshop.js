/**
 * 系统全局js
 */

var ms = {
	//删除图片
	removeImg : function(remove_id, id, path, _action){
		$.get(_action, {
			id : id,
			path : path
		}, function(res) {
			if (res.status == 1) {
				$("#" + remove_id).remove();
			} else {
				alert(res.info);
			}
		});
	},
	//全选
	checkAll : function(con, checked){
		if ($(con).is(":checked")) {
			$(checked).prop("checked", true);
		} else {
			$(checked).prop("checked", false);
		}
	},
	//[验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]],
    //验证条件(0:存在则验证,1:必须验证,2:值不为空验证)
	//验证规则（require,）
	validate : function(field,rule,message,condition,extend){
		switch(rule){
			case 'require':
				return vRequire(field,condition);
				break;
		}
	},
}
/**
 * 必填验证
 */
function vRequire(field,condition){
	var obj = $("#".field);
	if(condition == 0){
		if(!isExists(obj)){
			return true;
		}
	}
	if(obj.val() == ''){
		return false;
	}else{
		return true;
	}
}
/**
 * 判读是否存在
 */
function isExists(variable) {
	if (typeof (variable) != "undefined" & variable != "") {
		return true;
	} else {
		return false;
	}
}

/**
 * 检测图片是否存在
 */

$(function(){
    $("img").each(function(){
        var _obj = $(this);
        if (_obj) {

            if (_obj.attr('src').indexOf('yes') > 0 || _obj.attr('src').indexOf('no') > 0) {
                return false;
            }
            $.ajax({
                type: "get",
                url: "/General/General/checkpic",
                data:{ imgurl : $(this).attr('src') },
                dataType: "json",
                success:function(msg){
					_obj.attr('src', msg.imgurl);
                }
            })
		}
    });
})
