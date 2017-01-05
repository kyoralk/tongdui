/**
 * 添加商品相关js
 */
$(function(){
	
	var mid = $("input[name='init_mid']").val();
	if(mid !=''){
		$("select[name='mid'] option:nth-child(2)").attr("selected" , "selected");  
		mid = $("select[name='mid']").find("option:selected").val();
		var gc_id = $("input[name='gc_id']").val();
		var goods_id = $("input[name='goods_id']").val();
		goods_id = goods_id == "" ? 0 : goods_id;
		getAttr(mid,goods_id);
	}
	//初始化品牌
	getBrand({force:1});
	
});


//选择模型
$("select[name='mid']").change(function(){
	var mid = $(this).find("option:selected").val();
	var goods_id = $("input[name='goods_id']").val();
	getAttr(mid,goods_id);
});

//获取扩展属性
function getAttr(mid,goods_id){
	$.get("/General/Model/getAttr",{goods_id:goods_id,mid:mid},function(ret){
		$("#extend").html(ret);
		if(goods_id == 0){
			specAdd("#specadd",0);
		}
	},'text');
}
//添加属性
function specAdd(curr,key){
	$(curr).attr("onclick","specAdd(this,"+(key/1+1)+")")
	var spec_templ = $("#spec_templ tbody").html();
	spec_templ = spec_templ.replace(/\[9999\]/g, "["+key+"]");
	spec_templ = spec_templ.replace(/_9999/g, "_"+key);
	$(".table-goods-model tbody").append(spec_templ);
}
//删除属性
function specRemove(curr,spec_id){
	var tr = $(curr).parent().parent();
	$(tr).remove();
	if(spec_id > 0){
		$("#spec_remove").append('<input name="spec_id_remvoe[]" type="hidden" value="'+spec_id+'"/>');
	}
}
//移除扩展信息

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
	$(".submit").prop('disabled', 'disabled');
	$(".submit").html("提交中");
	var goods_img = $("input[name='goods_img[]']").val();
	if(goods_img!=""){
		document.getElementById('myform').encoding="multipart/form-data";
	}else{
		var goods_id = $("input[name='goods_id']").val();
		if(goods_id == ""){
			alert("商品图片不能为空");
			return;
		}
		
	}
	$("#spec_templ").remove();
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
//美化的file
function file_click(id){
	return $("input[name='"+id+"_btn']").click();
}
