<?php
class TypeAction extends CommonAction{
	
	/**
	 * 产品类型列表
	 *
	 */
	
	public function TypeList(){
	
	
		$type = M('GoodsType');
	
		$typeList = $type->select();
	
		$this->assign('typeList',$typeList);
	
		$this->display();
	
	
	}
	public function typeView(){
		$bt = M('BrandType');
		$bt_list=$bt->select();
		$this->assign('bt_list',$bt_list);
		$this->display();
	}
	/**
	 * 添加产品类型
	 *
	 */	
	public function typeAdd(){
	
		$type = D('GoodsType');
	
		if($type->create()){
			$data = array(
					't_name'=>$_POST['t_name'],
					'enabled'=>$_POST['enabled'],
					'range'=>$_POST['range'],
					'keyword'=>$_POST['keyword'],
					'brand_list'=>implode(',', $_POST['brand_id']),
			);
			if($type->add($data)){
	
				$this->success('添加成功',U('Mall/Type/TypeList'));
	
			}else{
	
				$this->success('添加失败');
			}
		}else{
				
			$this->error($type->getError());
				
		}
	
	
	
	}
	
	/**
	 *
	 * 修改产品类型
	 */
	public function typeInfo(){
	
		//查询品牌类型表
		$bt = M('BrandType');
		$bt_list=$bt->select();
		$this->assign('bt_list',$bt_list);
		
		$type = M('GoodsType');
		$typeInfo=$type->where('t_id = '.$_GET['t_id'])->select();
			
		
		
		$brand_id_list=array_filter(explode(',', $typeInfo[0]['brand_list']));
		//var_dump($brand_id_list[0]);
		$brand = M('Brand');
		$bt_id=$brand->where('brand_id = '.$brand_id_list[0])->getField('bt_id');
		
		//echo "aaaaaaa",$bt_id,"bbbbbbb";
		if(count($brand_id_list)>1){
			$brandmap['brand_id']=array('in',$brand_id_list);
		}else{
			$brandmap['brand_id']=$brand_id_list[0];
		}
		
		$brand_list=$brand->where($brandmap)->select();;
		$this->assign('bt_id',$bt_id);
		$this->assign('brand_list',$brand_list);
		$this->assign('typeInfo',$typeInfo);
		$this->display();
	}
	
	public function typeUpdate(){
	
		
		
		
		$type = D('GoodsType');
	
		if($type->create()){
				
			$data = array(
						
					't_id' =>  $_POST['t_id'],
					't_name' => $_POST['t_name'],
					'enabled' => $_POST['enabled'],
					'range'=>$_POST['range'],
					'keyword'=>$_POST['keyword'],
					'brand_list'=>implode(',', $_POST['brand_id']),
			);
			$res=$type->save($data);
			if($res>=0){
	
				$this->success('更新成功',U('Mall/Type/TypeList'));
	
			}else{
	
				$this->error('更新失败');
			}
		}else{
				
			$this->error($type->getError());
				
		}
	}
	
	/**
	 *
	 * 删除产品类型
	 */
	
	public function typeDelete(){
	
		$attribute = D('Attribute');
	
		$attr_id=$attribute->where('t_id = '.$_GET['t_id'])->Field('attr_id')->select();
	
		if(count($attr_id)>0){
			for($i=0;$i<count($attr_id);$i++){
				$res=$attribute->relation('attrvalues')->delete($attr_id[$i]['attr_id']);
				if($res==false){
	
					$this->error('删除失败1');
						
					exit();
				}
			}
		}
	
		$type = D('GoodsType');
	
		$res=$type->relation(true)->delete($_GET['t_id']);
	
		if($res!=1){
				
			$this->error('删除失败2');
				
		}else{
				
			$this->success('成功删除一条记录');
		}
	}
	//按照类型获取品牌
	public function typeBrand(){
		$class = M('GoodsClass');
		$t_id=$class->where('gc_id = '.$_GET['gc_id'])->getField('type_id');
		$type = M('GoodsType');
		$brand_id_list=$type->where('t_id ='.$t_id)->getField('brand_list');
		$map['brand_id']=array('in',$brand_id_list);
		$brand = M('Brand');
		$brand_list=$brand->where($map)->select();
		$this->ajaxReturn($brand_list);
	}
	
}
?>