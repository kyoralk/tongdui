<?php
namespace Admin\Controller;
use Admin\Controller\MallController;

class ModelController extends MallController{
	public static function returnModel(){
		$model_list = M('Model')->select();
		return $model_list;
	}
	/**
	 * 模型列表
	 */
	public function modelList(){
		$this->assign('model_list',self::returnModel());
		$this->assign('content_header','商品模型');
		$this->display('Model/model_list');
	}
	/**
	 * 添加模型
	 */
	public function add(){
		$Model = M('Model');
		$model_name_array = I('post.model_name');
		foreach ($model_name_array as $model_name){
			$data[] = array(
					'model_name'=>$model_name,
			);
		}
		if($Model->addAll($data)){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	/**
	 * 删除模型
	 */
	public function delete(){
		$ModelAttr = M('ModelAttr');
		$attr_id_array = $ModelAttr->where('mid = '.I('get.mid'))->getField('attr_id',true);
		if(!empty($attr_id_array)){
			$condition['attr_id'] = array('IN',$attr_id_array);
			if(is_numeric(M('ModelAttrValue')->where($condition)->delete())){
				if(!$ModelAttr->where($condition)->delete()){
					$this->error('删除失败');
				}
			}else{
				$this->error('删除失败');
			}
		}else{
			if(M('Model')->where('mid = '.I('get.mid'))->delete()){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}
		
		
	}
	/**
	 * 编辑模型
	 */
	public function edit(){
		$GoodsClass = M('GoodsClass');
		$class_list = $GoodsClass->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
		$Model = M('Model');
		$model_info = $Model->where('mid = '.I('get.mid'))->find();
		if(!empty($model_info['gc_id'])){
			$them_brand_array = explode(',', $model_info['brand_id']);
			$Brand = M('Brand');
			$brand_list = $Brand->where('gc_id = '.$model_info['gc_id'])->select();
			$count = count($brand_list);
			for($i = 0;$i<$count;$i++){
				if(in_array($brand_list[$i]['brand_id'], $them_brand_array)){
					$brand_list[$i]['checked'] = 1;
				}else{
					$brand_list[$i]['checked'] = 0;
				}
			}
		}
		$this->assign('brand_list',$brand_list);
		$this->assign('class_list',$class_list);
		$this->assign('model_info',$model_info);
		$this->assign('content_header','编辑模型');
		$this->display();
	}
	/**
	 * 保存模型
	 */
	public function save(){
		$Model = D('Model');
		$data = array(
				'mid'=>I('post.mid'),
				'model_name'=>I('post.model_name'),
				'enabled'=>I('post.enabled'),
				'range'=>I('post.range'),
				'keywords'=>I('post.keywords'),
				'gc_id'=>I('post.gc_id'),
				'brand_list'=>implode(',', I('post.brand_id')),
		);
		if(is_numeric($Model->save($data))){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}		
	}
	/**
	 * 模型属性列表
	 */
	public function attrList(){
		$attr_list = D('ModelAttr')->relation(true)->where('mid = '.I('get.mid'))->select();
		$this->assign('attr_list',$attr_list);
		$this->assign('mid',I('get.mid'));
		$this->assign('content_header','模型属性');
		$this->assign('right_menu',array('url'=>U('Model/attrEdit',array('mid'=>I('get.mid'))),'icon'=>'fa-plus','text'=>'添加属性'));
		$this->display('Model/attr_list');
	}
	/**
	 * 添加/编辑属性
	 */
	public function attrEdit(){
		if(empty(I('get.attr_id'))){
			$content_header = '添加属性';
			$mid = I('get.mid');
		}else{
			$ModelAttr = D('ModelAttr');
			$attr_info = $ModelAttr->relation(true)->where('attr_id = '.I('get.attr_id'))->find();
			$them_array = array_column($attr_info['attr_value'], 'attr_value');//返回键值为attr_value的数组
			$attr_info['attr_value'] = implode(PHP_EOL, $them_array);//把数组转换成待换行符的字符串
			$this->assign('attr_info',$attr_info);
			$content_header = '编辑属性';
			$mid = $attr_info['mid'];
		}
		$this->assign('mid',$mid);
		$this->assign('content_header',$content_header);
		$this->assign('right_menu',array('url'=>U('Model/attrList',array('mid'=>I('get.mid'))),'icon'=>'fa-list','text'=>'属性列表'));
		$this->display('Model/attr_info');
	}
	/**
	 * 添加属性
	 */
	public function attrAdd(){
		$ModelAttr = D('ModelAttr');
		$data = $ModelAttr->create();
		$attr_value_array = explode(PHP_EOL, $_POST['attr_value']);
		$attr_value_array = array_filter($attr_value_array);
		foreach ($attr_value_array as $attr_value){
			$data['attr_value'][]=array(
					'attr_value'=>$attr_value,
			);
		}
		unset($data['attr_id']);
		if($ModelAttr->relation(true)->add($data)){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	
	}
	/**
	 * 更新属性
	 */
	public function attrUpdate(){
		$ModelAttr = M('ModelAttr');
		$data = $ModelAttr->create();
		$data['sxsx'] = I('post.sxsx',0);
		$data['sxjs'] = I('post.sxjs',0);
		$error = 0;
		if($data){
			if(is_numeric($ModelAttr->save($data))){
				$ModelAttrValue = M('ModelAttrValue');
				$atv_list = M('ModelAttrValue')->where('attr_id = '.I('post.attr_id'))->field('atv_id,attr_value')->select();
				$attr_value_array = explode(PHP_EOL, I('post.attr_value'));
				//检索出需要删除的属性值
				foreach ($atv_list as $atv){
					if(!in_array($atv['attr_value'], $attr_value_array)){
						$data_del[] = $atv['atv_id'];
					}
					$attr_value_old[] = $atv['attr_value'];
				}
				//检索出新增的属性值
				foreach ($attr_value_array as $atv){
					if(!in_array($atv, $attr_value_old)){
						$data_add[]=array(
								'attr_id'=>I('post.attr_id'),
								'attr_value'=>$atv,
						);
					}
				}
				if(!empty($data_del)){
					$where['atv_id'] = array('IN',$data_del);
					if(!$ModelAttrValue->where($where)->delete()){
						$error++;
					}
				}
				if(!empty($data_add)){
					if(!$ModelAttrValue->addAll($data_add)){
						$error++;
					}
				}
			}else{
				$error++;
			}
		}else{
			$error++;
		}
		if($error){
			$this->error('更新失败');
		}else{
			$this->success('更新成功');
		}
	}
	/**
	 * 删除属性
	 */
	public function attrDel(){
		if(is_numeric(M('ModelAttrValue')->where('attr_id = '.I('get.attr_id'))->delete())){
			if(is_numeric(M('ModelAttr')->where('attr_id = '.I('get.attr_id'))->delete())){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 删除属性值
	 */
	public function atvDel(){
		if(M('ModelAttrValue')->where('atv_id = '.I('get.id'))->delete()){
			$this->ajaxReturn(1,'JSON');
		}else{
			$this->ajaxReturn(0,'JSON');
		}
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
	public function getAttr(){
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