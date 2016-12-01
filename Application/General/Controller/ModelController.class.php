<?php
namespace General\Controller;
use General\Controller\GeneralController;

class ModelController extends GeneralController{
	protected function _initialize(){
		C('DB_PREFIX',C('DB_PREFIX_MALL'));
	}
	
	public function getAttr(){
		$mid = I('get.mid');
		$goods_id = I('get.goods_id');
		if(!empty($goods_id)){
			$spec_list = M('GoodsSpec')->where('goods_id = '.$goods_id)->select();
		}
		$ModelAttr = D('ModelAttr');
		$attr_list = $ModelAttr->relation(true)->where('mid = '.$mid)->select();
		$attr_value_list = array_column($attr_list,'model_attr_value');
		$tmpl = $this->templ($attr_value_list);
		$html = '';
		$html .='<table class="table-goods-model">';
		$html .='<thead>';
		$html .='<tr>';
		$html .='<th width="80">封面</th>';
		foreach ($attr_list as $attr){
			$html .='<th width="100">'.$attr['attr_name'].'</th>';
		}
		$html .='<th width="100">供货价</th>';
		$html .='<th width="100">出货价</th>';
		$html .='<th width="100">库存</th>';
		$html .='<th width="50"><a href="javascript:;" id="specadd" onclick="specAdd(this,'.count($spec_list).')">添加</a></th>';
		$html .='</tr>';
		$html .='</thead>';
		$html .='<tbody>';
		if(empty($spec_list)){
			$html .= ""; 
		}else{
			$i = 0;
			foreach ($spec_list as $spec){
				$spec_value_array = explode(',', $spec['spec_value']);
				$html .='<tr>';
				$html .='<td id="spec_cover_view_'.$i.'"><p id="spec_cover_'.$i.'" onclick="file_click(\'spec_cover_'.$i.'\')">';
				if(empty($spec['spec_cover'])){
					$html .='<i class="icon-picture"></i>';
				}else{
					$html .='<img src="/Uploads/Mall/Seller/'.$spec['store_id'].'/Goods/Thumb/m_'.$spec['spec_cover'].'" width="80"/>';
					$html .='<input name="spec_cover[]" type="hidden" value="'.$spec['spec_cover'].'"/>';
				}
				$html .='<div class="ncsc-upload-btn" style="display: none;">';
				$html .='<input type="file" size="1" class="input-file" name="spec_cover_'.$i.'_btn"/>';
				$html .='</div><script>$("input[name=\'spec_cover_'.$i.'_btn\']").localResizeIMG({';
				$html .='quality : 1,success : function(result) {';
				$html .='$("input[name=\'spec_cover_'.$i.'\']").val(result.base64);';
				$html .='$("#spec_cover_view_'.$i.'").html(\'<input name="spec_cover[]" type="hidden" value="\'+result.base64+\'"/><img width="80" src="\'+result.base64+\'"/>\');';
				$html .='}';
				$html .='});</script>';
				$html .='</p></td>';
				foreach ($attr_value_list as $atv){
					$html .='<td><select name="spec_name['.$i.'][]">';
					foreach ($atv as $item){
						$html .='<option value="'.$item['atv_id'].'@'.$item['attr_value'].'"';
						if(in_array($item['atv_id'], $spec_value_array)){
							$html .=' selected="selected" ';
						}
						$html.='>'.$item['attr_value'].'</option>';
					}
					
					$html.'</select></td>';
				}
				$html .='<td><input name="spec_cost_price[]" class="text w60" value="'.$spec['spec_cost_price'].'" type="text"></td>';
				$html .='<td><input name="spec_add_price[]" class="text w60" value="'.$spec['spec_add_price'].'" type="text"></td>';
				$html .='<td><input name="spec_inventory[]" class="text w60" value="'.$spec['spec_inventory'].'" type="text"></td>';
				$html .='<td><input name="spec_id[]" type="hidden" value="'.$spec['spec_id'].'"/><a href="javascript:;" onclick="specRemove(this,'.$spec['spec_id'].')">移除</a></td>';
				$html .='</tr>';
				$i++;
			}
		}
		$html .='</tbody>';
		$html .='</table>';
		$html .='<table id="spec_templ" style="display:none;"><tbody>'.$tmpl.'</tbody></table>';
		echo $html;
	}
	
	
	private function templ($attr_value_list){
		$tr = '';
		$tr .='<tr>';
		$tr .='<td id="spec_cover_view_9999"><p id="spec_cover_9999" onclick="file_click(\'spec_cover_9999\')">';
		$tr .='<i class="icon-picture" style="font-size:80px;"></i>';
		$tr .='<div class="ncsc-upload-btn" style="display: none;">';
		$tr .='<input type="file" size="1" class="input-file" name="spec_cover_9999_btn"/>';
		$tr .='</div><script>$("input[name=\'spec_cover_9999_btn\']").localResizeIMG({';
		$tr .='quality : 1,success : function(result) {';
		$tr .='$("input[name=\'spec_cover_9999\']").val(result.base64);';
		$tr .='$("#spec_cover_view_9999").html(\'<input name="spec_cover[]" type="hidden" value="\'+result.base64+\'"/><img width="80" src="\'+result.base64+\'"/>\');';
		$tr .='}';
		$tr .='});</script>';
		$tr .='</p></td>';
		foreach ($attr_value_list as $atv){
			$tr .='<td><select name="spec_name[9999][]">';
			foreach ($atv as $item){
				$tr .='<option value="'.$item['atv_id'].'@'.$item['attr_value'].'">'.$item['attr_value'].'</option>';
			}
			$tr.'</select></td>';
		}
		$tr .='<td><input name="spec_cost_price[]" class="text w60" value="" type="text"></td>';
		$tr .='<td><input name="spec_add_price[]" class="text w60" value="" type="text"></td>';
		$tr .='<td><input name="spec_inventory[]" class="text w60" value="" type="text"></td>';
		$tr .='<td><a href="javascript:;" onclick="specRemove(this,0)">移除</a></td>';
		$tr .='</tr>';
		return $tr;
	}
	
	
	/**
	 * 获取模型
	 */
	public function getModel(){
		//查找父目录
		$GoodsClass = M('GoodsClass');
		$gc_id = I('get.gc_id',0);
		if($gc_id){
			do{
				$gc_parent_id = $GoodsClass->where('gc_id = '.$gc_id)->getField('gc_parent_id');
				if($gc_parent_id){
					$gc_id = $gc_parent_id;
				}
			}while($gc_parent_id);
			$model_list = M('Model')->where('gc_id = '.$gc_id)->field('mid,model_name')->select();
			$this->ajaxJsonReturn($model_list);
		}else{
			$this->ajaxJsonReturn('','',0);
		}
	}
	/**
	 * 初始化属性
	 */
	public function getAttr1(){
		if(empty(I('get.mid'))){
			echo '';
			exit();
		}
		//初始化选择的值
		if(I('get.goods_id')){
			$html = $this->getExtend(I('get.goods_id'),I('get.mid'));
			if(!empty($html)){
				echo $html;
				exit();
			}
				
		}
		$ModelAttr = D('ModelAttr');
		$attr_list = $ModelAttr->relation(true)->where('mid = '.I('get.mid'))->select();
		$count = count($attr_list);
		$html = '';
		for($i = 0; $i<$count; $i++){
			$html .='<div class="form-group">';
			$html.='<label for="" class="col-sm-2 control-label">'.$attr_list[$i]['attr_name'].'</label>';
			$html.='<div class="col-sm-2">';
			switch ($attr_list[$i]['attr_input_type']){
				case 1:
					$html.='<select name="atv_id[]" class="form-control">';
					foreach ($attr_list[$i]['attr_value'] as $item){
						$html.='<option ';
						if($item['choose']==1){
							$html.=' selected="selected" ';
						}else{
							$html.=' value="'.$item['atv_id'].'">'.$item['attr_value'].'</option>';
						}
					}
					$html.='</select>';
					break;
				case 2:
					foreach ($attr_list[$i]['attr_value'] as $item){
	
						$html.='<input ';
						if($item['choose']==1){
							$html.=' checked="checked" ';
						}else{
							$html.=' name="atv_id[]" value="'.$item['attr_value'].'" type="checkbox">&nbsp;&nbsp;&nbsp;'.$item['attr_value'].'&nbsp;&nbsp;&nbsp;';
						}
					}
					break;
			}
			$html.='</div>';
			$html.='<label for="" class="col-sm-1 control-label">增加价格</label>';
			$html.='<div class="col-sm-1"><input class="form-control" name="add_price[]" type="text" value=""></div>';
			$html.='<label for="" class="col-sm-1 control-label" style="text-align:left;"><a href="javascript:;" onclick="addAttr(this,0);"><i class="fa fa-plus" title="增加"></i>增加</a></label>';
			$html.='</div>';
				
		}
		echo $html;
	
	}
	/**
	 * 扩展信息
	 */
	public function getExtend($goods_id,$mid){
		$extend_list_them = M('GoodsExtend')->where('goods_id = '.$goods_id)->select();
		$atv_array = array_column($extend_list_them, 'atv_id');
		if(empty($atv_array)){
			return false;
		}
		//把列表转换成以atv_id 为key值得数组
		foreach ($extend_list_them as $item){
			$extend_list[$item['atv_id']] = $item;
		}
		$condition['atv_id'] = array('IN',$atv_array);
		$ModelAttrValue = D('ModelAttrValue');
		$atv_list = $ModelAttrValue->relation(true)->where($condition)->select();//选中的属性值列表
		$attr_list_them = D('ModelAttr')->relation(true)->where('mid = '.$mid)->select();//属性列表
		//把列表转换成以attr_id 为key值得数组
		foreach ($attr_list_them as $item){
			$attr_list[$item['attr_id']] = $item;
		}
		foreach ($atv_list as $atv){
			$loop_list = $attr_list[$atv['attr_id']];
			$html .='<div class="form-group">';
			$html.='<label for="" class="col-sm-2 control-label">'.$loop_list['attr_name'].'</label>';
			$html.='<div class="col-sm-2">';
			switch ($loop_list['attr_input_type']){
				case 1:
					$html.='<select name="atv_id[]" class="form-control">';
					foreach ($loop_list['attr_value'] as $item){
						$html.='<option ';
						if($item['atv_id']==$atv['atv_id']){
							$html.=' selected="selected" ';
						}
						$html.=' value="'.$item['atv_id'].'">'.$item['attr_value'].'</option>';
	
					}
					$html.='</select>';
					break;
				case 2:
					foreach ($loop_list['attr_value'] as $item){
						$html.='<input ';
						if($item['atv_id']===$atv['atv_id']){
							$html.=' checked="checked" ';
						}
						$html.=' name="atv_id[]" value="'.$item['attr_value'].'" type="checkbox">&nbsp;&nbsp;&nbsp;'.$item['attr_value'].'&nbsp;&nbsp;&nbsp;';
					}
					break;
			}
			$html.='</div>';
			$html.='<label for="" class="col-sm-1 control-label">增加价格</label>';
			$html.='<div class="col-sm-1"><input class="form-control" name="add_price[]" type="text" value="'.$extend_list[$atv['atv_id']]['add_price'].'"><input name="extend_id[]" type="hidden" value="'.$extend_list[$atv['atv_id']]['extend_id'].'"></div>';
			$html.='<label for="" class="col-sm-1 control-label" style="text-align:left;">';
			$html.='<a href="javascript:;" onclick="addAttr(this,'.$extend_list[$atv['atv_id']]['extend_id'].');"><i class="fa fa-plus" title="增加"></i>增加</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			$html.='<a href="javascript:;" onclick="removeExtend(this,'.$extend_list[$atv['atv_id']]['extend_id'].');"><i class="fa fa-remove" title="移除"></i>移除</a></label>';
			$html.='</div>';
		}
		return  $html;
	}
}