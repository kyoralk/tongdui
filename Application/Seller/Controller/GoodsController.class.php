<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;
use General\Util\Image;

class GoodsController extends CommonController{
	/**
	 * 删除file对象中的元素
	 */
	private function unsetFile($file,$unset_file){
		foreach ($unset_file as $unset){
			unset($file['name'][$unset]);
			unset($file['type'][$unset]);
			unset($file['tmp_name'][$unset]);
			unset($file['error'][$unset]);
			unset($file['size'][$unset]);
		}
		return $file;
	}
	/**
	 * 当前分类下的模型
	 */
	private function modelList($gc_id_array){
		$Model = M('Model');
		$i = count($gc_id_array) - 1;
		while (!empty($gc_id_array[$i])){
			$model_list = M('Model')->where('gc_id = '.$gc_id_array[$i])->field('mid,model_name')->select();
			if(empty($model_list)){
				$i--;
			}else{
				break;
			}
		}
		return $model_list;
	}
	
	/**
	 * 把常用分类保存的cookie中
	 * @param array $gc_name_array
	 */
	private function lastClassToCookie($gc_name_array){
		$str = I('get.gc_id_path').'||'.implode('>', $gc_name_array);
		$gc_id_history = empty(cookie('gc_id_history'))? array(): cookie('gc_id_history');
		array_unshift($gc_id_history, $str);
		$gc_id_history = array_unique($gc_id_history);//插入元素到数组首位
		cookie('gc_id_history',$gc_id_history);
	}
	/**
	 * 规格信息
	 */
	private function goodsSpec(){
		$spec_id = I('post.spec_id');
		$spec_name = I('post.spec_name');
		$spec_cover = I('post.spec_cover');
		$spec_cost_price = I('post.spec_cost_price');
		$spec_add_price = I('post.spec_add_price');
		$spec_inventory = I('post.spec_inventory');
		$spec_name_array = array();
		$spec_value_array = array();
		foreach ($spec_name as $item){
			$spec_name_temp = array();
			$spec_value_temp = array();
			foreach ($item as $loop){
				$temp = explode('@', $loop);
				$spec_name_temp[] = $temp[1];
				$spec_value_temp[] = $temp[0];
				$data['goods_extend'][]['atv_id'] = $temp[0];
			}
			$spec_name_array[] = implode(' ', $spec_name_temp);
			asort($spec_value_temp);//排序
			$spec_value_array[] = implode(',', $spec_value_temp);
		}
		$i = 0;
		while (!empty($spec_name_array[$i])){
			if($spec_id[$i]){
				$data['goods_spec'][$i]['spec_id'] = $spec_id[$i];
			}
			if(strlen($spec_cover[$i])>50){
				$res = Image::createImg(array($spec_cover[$i]), 'MALL_SELLER',true,session('store_id').'/Goods/');
				$data['goods_spec'][$i]['spec_cover'] = $res[0];
			}else{
				$data['goods_spec'][$i]['spec_cover'] = $spec_cover[$i];
			}
			$data['goods_spec'][$i]['spec_name'] = $spec_name_array[$i];
			$data['goods_spec'][$i]['spec_value'] = $spec_value_array[$i];
			$data['goods_spec'][$i]['spec_cost_price'] = empty($spec_cost_price[$i])? 0 : $spec_cost_price[$i];
			$data['goods_spec'][$i]['spec_add_price'] = empty($spec_add_price[$i])? 0 : $spec_add_price[$i];
			$data['goods_spec'][$i]['spec_inventory'] = empty($spec_inventory[$i])? 0 : $spec_inventory[$i];
			$data['goods_spec'][$i]['store_id'] = session('store_id');
			$i++;
		}
		return $data;
	}
	/**
	 * 设置封面图片
	 */
	public function setCover(){
		try {
			$condition['goods_id'] = I('get.goods_id');
			$GoodsImg = M('GoodsImg');
			$GoodsImg->where($condition)->setField('is_cover',0);
			$condition['save_name'] = I('get.img_name');
			M('GoodsImg')->where($condition)->setField('is_cover',1);
		} catch (Exception $e) {
			$this->ajaxReturn(0,'JSON');
			exit();
		}
		$this->ajaxReturn(1,'JSON');
	}
	/**
	 * 删除图片
	 */
	public function removeImg(){
		$goods_id = I('get.goods_id');
		$img_name = I('get.img_name');
		$general_path = './Uploads/'.C('MALL_SELLER').session('store_id').'/Goods/';
		$file_0 = $general_path.$img_name;
		$file_l = $general_path.'Thumb/l_'.$img_name;
		$file_m = $general_path.'Thumb/m_'.$img_name;
		$file_s = $general_path.'Thumb/s_'.$img_name;
		try {
			unlink($file_0);
			unlink($file_l);
			unlink($file_m);
			unlink($file_s);
		} catch (Exception $e) {
				
		}
		$condition['goods_id'] = $goods_id;
		$condition['save_name'] = $img_name;
		if(M('GoodsImg')->where($condition)->delete()){
			$this->ajaxReturn(1,'JSON');
		}else{
			$this->ajaxReturn(0,'JSON');
		}
	}
	/**
	 * 商品列表
	 */
	public function goodsList(){
		$store_class_id = I('get.store_class_id');
		$goods_name = I('get.goods_name');
		if(!empty($store_class_id)){
			$condition['store_class_id'] = $store_class_id;
			$this->assign('store_class_id',$store_class_id);
		}
		if(!empty($goods_name)){
			$condition['goods_name'] = array('like','%'.$goods_name.'%');
			$this->assign('goods_name',$goods_name);
		}
		$condition['store_id'] = session('store_id');
		$count = M('Goods')->where($condition)->count();
		$data = page(D('GoodsView'), $condition,20,'view','Goods.goods_id desc','','Goods.goods_id',$count);
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('class_list',R('Class/getClass',array('sc_id,sc_parent_id,sc_name')));
		if(I('get.display')=='store'){
			$this->assign('id',I('get.id'));
			$this->display('Store/goods_list');
		}else{
			$this->display('Goods/goods_list');
		}
	}
	/**
	 * 添加产品第一步选择分类
	 */
	public function selectClass(){
		$gc_parent_id = I('get.gc_id',0);
		$goods_id = I('get.goods_id');
		$class_list = M('GoodsClass')->where('gc_parent_id = '.$gc_parent_id)->field('gc_id,gc_name')->select();
		if(IS_AJAX){
			$this->ajaxJsonReturn($class_list,'',1);
		}else{
			$gc_id_histroy = cookie('gc_id_history');
			foreach ($gc_id_histroy as $item){
				$temp = explode('||', $item);
				$gc_id_path[] = array('path_id_str'=>$temp[0],'path_name_str'=>$temp[1]);
			}
			$this->assign('gc_id_path',$gc_id_path);
			$this->assign('class_list',$class_list);
			$this->assign('goods_id',I('get.goods_id'));
			$this->display('Goods/class_list');
		}
	}
	/**
	 * 商品详情
	 */
	public function info(){
		$gc_id_path = empty(I('get.gc_id_path')) ? false : I('get.gc_id_path');
		if(!empty(I('get.goods_id'))){
			$goods_info = D('Goods')->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
			if(!$gc_id_path){
				$gc_id_path = $goods_info['gc_id_path'];
			}
			$this->assign('goods_info',$goods_info);
			$this->assign('brand_name',M('Brand')->where('brand_id = '.$goods_info['brand_id'])->getField('brand_name'));
		}
		$gc_id_array = explode('-', $gc_id_path);
		$condition['gc_id'] = array('IN',$gc_id_array);
		$gc_name_array = M('GoodsClass')->where($condition)->order('gc_id')->getField('gc_name',true);
		$this->lastClassToCookie($gc_name_array);//把常用分类保存的cookie中
		$this->assign('gc_id',end($gc_id_array));
		$this->assign('gc_id_path',$gc_id_path);
		$this->assign('gc_name_path',implode('>', $gc_name_array));
		$this->assign('model_list',$this->modelList($gc_id_array));
		$this->assign('class_list',R('Class/getClass',array('sc_id,sc_parent_id,sc_name')));
		$this->display();
	}
	/**
	 * 保存商品
	 */
	public function save(){
		$examine = 0;
		//通用信息
		$goods_id = empty(I('post.goods_id'))? 0 : I('post.goods_id');
		$data['goods_name'] = I('post.goods_name');
		$data['small_name'] = I('post.small_name');
		$data['spec_type'] = empty(I('post.spec_type'))? 1 : I('post.spec_type');
		$data['gc_id'] = empty(I('post.gc_id')) ? 0 : I('post.gc_id');//商品分类id
		$data['gc_id_path'] = empty(I('post.gc_id_path')) ? 0 : I('post.gc_id_path');//商品分类id_path
		$data['brand_id'] = empty(I('post.brand_id')) ? 0 : I('post.brand_id');//商品分类id
		$data['market_price'] = empty(I('post.market_price')) ? 0: I('post.market_price');//市场价
		$data['shop_price'] = empty(I('post.shop_price')) ? 0 : I('post.shop_price');//本店售价
		$data['cost_price'] = empty(I('post.cost_price')) ? 0 : I('post.cost_price');//成本价
		$data['max_buy'] = empty(I('post.max_buy')) ? 0 : I('post.max_buy');//最大购买量
		$data['goods_desc'] = I('post.goods_desc');//商品描述
		$data['goods_weight'] = empty(I('post.goods_weight')) ? 0 : I('post.goods_weight');//商品种类
		$data['is_on_sale'] = I('post.is_on_sale',0);//是否上架
		$data['freight_type'] = I('post.freight_type');//运费计算方式
		$data['freight'] = empty(I('post.freight_type'))? 0 : I('post.freight_type');//运费
		$data['keywords'] = I('post.keywords');//seo关键字
		$data['description'] = I('post.description');//seo描述
		$data['mid'] = empty(I('post.mid')) ? 0 : I('post.mid');//商品模型id
		$data['store_id'] = session('store_id');//商品模型id
		$data['store_tuijian'] = I('post.store_tuijian');//店铺推荐
		$data['store_class_id'] = I('post.store_class_id');//店铺中分类
		$data['promote_price'] = empty(I('post.promote_price')) ? 0 : I('post.promote_price');//促销价格
		//处理促销时间
		$promote_start_date = empty(I('post.promote_start_date')) ? 0 : I('post.promote_start_date');
		$promote_end_date = empty(I('post.promote_end_date'))? 0 : I('post.promote_end_date');
		$data['promote_start_date'] = $promote_start_date == 0 ? 0 : strtotime($promote_start_date);//促销开始时间
		$data['promote_end_date'] = $promote_end_date == 0 ? 0 : strtotime($promote_end_date);//促销结束时间
		//处理上传图片
		if($_FILES['goods_img']){
			$_FILES['goods_img'] = $this->unsetFile($_FILES['goods_img'], I('post.unset_file'));
			if(!empty($_FILES['goods_img'])){
				$res = Image::upload('goods_img', 'MALL_SELLER',true,session('store_id').'/Goods/');
				if($res){
					foreach ($res as $item){
						$data['goods_img'][] = array(
								'save_name' => $item['savename'],
								'save_path' => $item['savepath'],
						);
					}
				}
			}
		}
		//规格信息
		//如果是单一规格删除所有规格
		if($data['spec_type']==1){
			M('GoodsSpec')->where('goods_id = '.$goods_id)->delete();
		}else{
			$spec_info = $this->goodsSpec();
			if(!empty($spec_info['goods_spec'])){
				$data['goods_spec'] = $spec_info['goods_spec'];
				$data['goods_extend'] = $spec_info['goods_extend'];
			}else{
				$data['spec_type']=1;
			}
		}
		$Goods = D('Goods');
		if($goods_id){
			$data['goods_id'] = $goods_id;
			//删除扩展信息
			M('GoodsExtend')->where('goods_id = '.$goods_id)->delete();
			$spec_id_remove = I('post.spec_id_remvoe');
			//判断是否存需要删除的规格
			if(!empty($spec_id_remove)){
				M('GoodsSpec')->where(array('spec_id'=>array('in',$spec_id_remove)))->delete();
			}
			
			//更新
			if(is_numeric($Goods->relation(true)->save($data))){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
		}else{
			//新增
			$data['examine_status'] =  $examine == 0 ? 1 : 2;//处理审核状态
			$data['goods_sn'] = 'MS_'.serialNumber();//商品货号
			if($Goods->relation(true)->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	
	public function delete(){
		if(D('Goods')->relation(array('goods_img','goods_extend'))->delete(I('get.goods_id'))){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

}