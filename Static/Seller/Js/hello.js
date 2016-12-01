/**
 * 
 */
/**
 * 添加商品相关js
 */
$(function(){
	//初始化属性
	var gc_id = $("input[name='gc_id']").val();
	var mid = $("input[name='init_mid']").val();
	if(gc_id !=''){
		getInit(gc_id,mid);
	}
	if(mid !=''){
		var goods_id = $("input[name='goods_id']").val();
		getAttr(gc_id,goods_id);
	}
	//初始化品牌
	getBrand({});
});
//$("select[name='gc_id']").change(function(){
//	var gc_id = $(this).find("option:selected").val();
//	getInit(gc_id);
//});
function getInit(gc_id,mid){
	$.get("/General/Model/getModel",{gc_id:gc_id},function(res){
		if(res.status == 1){
			var data = res.result;
			var html = '<option value="0">选择模型</option>';
			for(var i in data){
				var item = data[i];
				html += '<option ';
				if(item['mid']==mid){
					html += ' selected="selected" ';
				}
				html += ' value="'+item['mid']+'">'+item['model_name']+'</option>';
			}
			$("select[name='mid']").html(html);
		}
	});
}
//选择模型
//$("select[name='mid']").change(function(){
//	var mid = $(this).find("option:selected").val();
//	var goods_id = $("input[name='goods_id']").val();
//	getAttr(mid,goods_id);
//});
//重置模型
function resetModel(){
	var mid = $("select[name='mid']").find("option:selected").val();
	getAttr(mid,0);
}
//获取扩展属性
function getAttr(mid,goods_id=0){
	$.get("/General/Model/getAttr",{goods_id:goods_id,mid:mid},function(ret){
		$("#extend").html(ret);
	},'text');
}
//添加属性
function addAttr(the_current,extend_id=0){
	var row = $(the_current).parent().parent();
	var html = row.html();
	html = html.replace('<a href="javascript:;" onclick="addAttr(this,'+extend_id+');"><i class="fa fa-plus" title="增加"></i>增加</a>','<a href="javascript:;" onclick="minusAttr(this);"><i class="fa fa-minus" title="减少"></i>减少</a>');
	
	if(extend_id!=0){
		html = html.replace('<input name="extend_id[]" value="'+extend_id+'" type="hidden">','<input name="extend_id[]" value="0" type="hidden">');
		html = html.replace('<a href="javascript:;" onclick="removeExtend(this,'+extend_id+');"><i class="fa fa-remove" title="移除"></i>移除</a>','');
	}
	row.after('<div class="form-group">'+html+'</div>');
}
//删除属性
function minusAttr(the_current){
	$(the_current).parent().parent().remove();
}
//移除扩展信息
function removeExtend(the_current,extend_id){
	$.get("{:U('Goods/removeExtend')}",{extend_id,extend_id},function(res){
		if(res.status == 1){
			$(the_current).parent().parent().remove();
		}else{
			alert(res.info);
		}
	})
}
/**********************品牌开始*******************************/
//获取品牌
function getBrand(data){
	$.get("/Seller/Brand/ajaxBrand",data,function(res){
		var html = '<ul>';
		for(var i in res){
			var item = res[i];
			html +='<li data-id="'+item.brand_id+'" data-name="'+item.brand_name+'"><em>'+item.first_letter+'</em>'+item.brand_name+'</li>';
		}
		html +='</ul>';
		$("#brand_list").html(html);
	});
}
//根据首字母检索
$(".letter ul li a").click(function(){
	var first_letter = $(this).attr("data-letter");
	getBrand({first_letter:first_letter});
});
//根据关键字检索
$(".search a").click(function(){
	var keyword = $("#search_brand_keyword").val();
	getBrand({keyword:keyword});
});
//选择品牌
$("#brand_list").delegate('li','click',function(){
	var brand_name = $(this).attr("data-name");
	var brand_id = $(this).attr("data-id");
	$("input[name='brand_name']").val(brand_name);
	$("input[name='brand_id']").val(brand_id);
	$(".ncsc-brand-select-container").hide();
});
//显示品牌
$('input[name="brand_name"]').focus(function(){
	$('.ncsc-brand-select-container').show();
});
//显示隐藏品牌
$("#brand_collapse").click(function(){
	if($(".ncsc-brand-select-container").is(":hidden")){
		$(".ncsc-brand-select-container").show();
	}else{
		$(".ncsc-brand-select-container").hide();
	}
});
//绑定滚动
$("#brand_list").perfectScrollbar();
//提交表单
function submitForm(){
	var goods_img = $("input[name='goods_img[]']").val();
	if(goods_img!=''){
		document.getElementById('myform').encoding="multipart/form-data";
	}
	document.myform.submit();	
}
//上传预览
function setImagePreviews(){
	var doc_obj = document.getElementById("doc");
  	var file_list = doc_obj.files;
  	var count = file_list.length;
  	var html = "";
  	for(var i = 0;i<count;i++){
  		var img_src = window.URL.createObjectURL(file_list[i]);
		html +='<li class="ncsc-goodspic-upload" id="preview_'+i+'">';
		html +='<div class="upload-thumb">';
		html +='<img src="'+img_src+'">';
		html +='</div>';
		html +='<div class="show-default">';
		html +='<a href="javascript:removePreview('+i+')"  class="del" title="移除">X</a>';
		html +='</div>';
		html +='</li>';
  	}
  	$("#preview").html(html);
  	$("#unset_file_div").html("");
  	
}
//移除上传预览图片
function removePreview(id){
	$("#unset_file_div").append('<input name="unset_file[]" type="hidden" value="'+id+'"/>');
	$("#preview_"+id).remove();
}
//设为封面
function setCover(goods_id,img_name,e){
	$.get("/Seller/Goods/setCover",{goods_id:goods_id,img_name:img_name},function(res){
		if(res == 1){
			$(".show-default").prop("class","show-default");
			$(e).parent().prop("class","show-default selected");
		}else{
			alert('设置失败');
		}
	});
}
//删除图片
function removeImg(goods_id,img_name,e){
	$.get("/Seller/Goods/removeImg",{goods_id:goods_id,img_name:img_name},function(res){
		if(res == 1){
			$(e).parent().parent().remove();
		}else{
			alert('删除失败');
		}
	});
}

