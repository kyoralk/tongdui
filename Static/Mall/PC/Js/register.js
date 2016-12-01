/**
 * 提交注册请求
 * @author zhangbin
 */
// 验证规则
var rule = [
		[ 'username', 'empty', '用户名不能为空', 1, 'regex', 0 ],
		[ 'username', /^[0-9a-zA-Z\u4E00-\u9FA5][0-9a-zA-Z\u4E00-\u9FA5_]*$/,'用户名格式不正确', 1, 'regex', 1 ],
		[ 'username', checkLength, '用户名长度不正确', 1, 'function', 0 ],
		[ 'username', checkName, '用户名已存在', 1, 'function', 1 ],
		[ 'password', 'empty', '密码不能为空', 1, 'regex', 0 ],
		[ 'password', /^[0-9 a-z A-Z !@#$%^&*]{6,16}$/, '密码格式不正确', 1, 'regex',1 ],
		[ 'repwd', 'empty', '确认密码不能为空', 1, 'regex', 0 ],
		[ 'repwd', checkRepwd, '两次密码输入不一致', 1, 'function', 1 ],
		[ 'code', 'empty', '验证码不能为空', 1, 'regex', 0 ],
		[ 'code', /^\w{4}$/, '验证码长度不正确', 1, 'regex', 1 ],
		[ 'code', checkCode, '验证码不正确', 1, 'function', 1 ],
];
var common_res;
// 验证用户名长度
function checkLength(a) {
	var c = a.match(/[^\x00-\x80]/g);
	var length = a.length + (!c ? 0 : c.length);
	return length > 16 || length < 4 ? true : false;
}

function checkRepwd(repwd) {
	var password = $("#password").val();
	var repwd = $("#repwd").val();
	if (password == repwd) {
		return true;
	}

}
// 验证验证码是否正确
function checkCode(code) {
	$.ajax({
		type : "POST",
		url : "/Mall/Public/checkCode",
		data : {
			code : code
		},
		dataType : "json",
		async : false,
		success : function(result) {
			common_res = result;
		}
	});
	if (common_res.status == 0) {
		document.getElementById('verify').src='/General/Image/verifyCode?'+(new Date()).getTime();
		return false;
	} else {
		return true;
	}
}

// 验证用户名重复
function checkName(username) {
	$.ajax({
		type : "POST",
		url : "/Mall/Public/checkName",
		data : {
			username : username
		},
		dataType : "json",
		async : false,
		success : function(result) {
			common_res = result;
		}
	});
	if (common_res.status == 0) {
		return false;
	} else {
		return true;
	}
}
// 验证提示渲染
function warn(result) {
	if ($.inArray(typeof (result), [ 'string', 'object' ]) != -1) {
		if (typeof (result) == 'object') {
			$.each(result, function(key, value) {
				var id = '#' + key + '_warn';
				$(id).size() > 0 ? $(id).html(
						'<strong class="error"></strong>' + value)
						: alert(value);
			});
		} else {
			alert(result);
		}
	}
}
// 验证密码等级
$('input[name=password]').keyup(function() {
	var password = $(this).val();
	var warn = '<strong class="public danger"></strong>请结合数字、字母、符号。';
	if (/^([0-9]{6,16})$/.test(password)) {
		warn = warn;
	} else if (/^[0-9 a-z]{6,16}$/.test(password)) {
		warn = '<strong class="public anquan-d"></strong>请结合数字、字母、符号。';
	} else if (/^[0-9 a-z A-Z !@#$%^&*]{6,16}$/.test(password)) {
		warn = '<strong class="public anquan"></strong>请牢记你的密码';
	}
	$('#password_warn').html(warn);
});
// 获取焦点事件
$(':input').focus(
		function() {
			// 高亮边框
			$('.input-active').removeClass('input-active');
			$(this).parents('.user').addClass('input-active');
			var name = $(this).attr('name');
			$('#' + name + '_warn').attr('warn') == undefined
					|| $('#' + name + '_warn').html(
							'<strong class="warn"></strong>'
									+ $('#' + name + '_warn').attr('warn'));// 清除之前验证信息
		});
// 默认获取焦点
$('input[name=email]').focus();
// 失去焦点验证
$(':input:not(input[name="code"])').blur(
		function() {
			var name = $(this).attr('name');
			var info = $(this).validate(rule, false);
			var result = {};
			result[name] = info;
			$('#' + name + '_warn').html(
					'<s style="display: inline;" class="passport-icon"></s>');// 清除之前验证信息
			info !== true && warn(result);
		});
// 提交表单验证
$("#btnAgreeBtn").click(
		function() {
			var result = $("#register").validate(rule);
			var err_key = [];
			for(var i in result){
				err_key.push(i.toString());
			}
			$("[id*='_warn']").each(function(){
				var id = $(this).attr("id");
				var res = $.inArray(id.split("_warn")[0], err_key);
				if(res<0){
					$("#"+id).html(
					'<s style="display: inline;" class="passport-icon"></s>');// 清除之前验证信息
				}
			});
			if ('boolean' == typeof (result)) {
				 $("#register").submit();
			} else {
				warn(result);// 验证提示
			}

		});
