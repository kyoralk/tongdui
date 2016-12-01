/**
 * 提交入驻请求
 * @author zhangbin
 */
// 验证规则
var rule = [
		[ 'store_name', 'empty', '店铺名称不能为空', 1, 'regex', 0 ],
		[ 'store_name', checkName, '店铺名称已存在', 1, 'function', 1 ],
		[ 'company_name', 'empty', '公司名称不能为空', 1, 'regex', 0 ],
		[ 'company_location', 'empty', '公司所在地不能为空', 1, 'regex', 0 ],
		[ 'company_address', 'empty', '公司详细地址不能为空', 1, 'regex', 0 ],
		[ 'company_phone', 'empty', '公司电话不能为空', 1, 'regex', 0 ],
		[ 'employees_count', 'empty', '员工人数不能为空', 1, 'regex', 0 ],
		[ 'registered_capital', 'empty', '注册资金不能为空', 1, 'regex', 0 ],
//		[ 'company_phone', 'number', '电话号码格式不正确', 1, 'regex', 0 ],
		[ 'business_licence_number', 'empty', '营业执照编号不能为空', 1, 'regex', 0 ],
//		[ 'business_licence_number', 'number', '营业执照编号码格式不正确', 1, 'regex', 0 ],
//		[ 'business_licence_pic', checkPic, '营业执照图片不能为空', 1, 'function', 1 ],
		[ 'business_scope', 'empty', '经营范围不能为空', 1, 'regex', 0 ],
		[ 'contacts_name', 'empty', '联系人不能为空', 1, 'regex', 0 ],
		[ 'contacts_phone', 'empty', '联系人电话不能为空', 1, 'regex', 0 ],
//		[ 'company_phone', 'number', '电话号码格式不正确', 1, 'regex', 0 ],
		[ 'contacts_email', 'empty', '联系人邮箱不能为空', 1, 'regex', 0 ],
//		[ 'contacts_email', 'email', '联系人邮箱格式不正确', 1, 'regex', 0 ],
];
var common_res;
// 验证用户名重复
function checkName(store_name) {
	$.ajax({
		type : "GET",
		url : "/Seller/Index/checkName",
		data : {
			store_name : store_name
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
//验证营业执照
function checkPic(){
	var pic = $("#business_licence_pic").val();
	if(pic == ""){
		$("#business_licence_pic_warn").html('<i class="fa fa-exclamation-circle"></i>营业执照必传');
		return;
	}
}
// 验证提示渲染
function warn(result) {
	if ($.inArray(typeof (result), [ 'string', 'object' ]) != -1) {
		$.each(result, function(key, value) {
			var id = '#' + key + '_warn';
			$(id).html('<i class="fa fa-exclamation-circle"></i>' + value);
			$(id).show();
		});
	}
}
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
$('input[name=store_name]').focus();
// 失去焦点验证
$(':input:not(input[name="code"]),#business_scope').blur(
		function() {
			var name = $(this).attr('name');
			var info = $(this).validate(rule, false);
			var result = {};
			result[name] = info;
			$('#' + name + '_warn').hide();// 清除之前验证信息
			info !== true && warn(result);
		});
// 提交表单验证
$("#btnAgreeBtn").click(
		function() {
			checkPic();
			var result = $("#my_store_form").validate(rule);
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
				 $("#my_store_form").submit();
			} else {
				warn(result);// 验证提示
			}

		});