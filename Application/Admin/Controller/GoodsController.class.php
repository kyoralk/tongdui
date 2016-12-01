<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
use General\Util\Image;

class GoodsController extends MallController{
	/**
	 * 商品列表
	 */
	public function goodsList(){
		if(I('get.seller')==1){
			$this->assign('seller',1);
			$condition['store_id'] = array('gt',$this->system_store_id);
		}else{
			$this->assign('seller',0);
			$condition['store_id'] = $this->system_store_id;
		}
		if(!empty(I('get.examine_status'))){
			$condition['examine_status'] = I('get.examine_status');
			$this->assign('examine_status',I('get.examine_status'));
		}
		if(!empty(I('get.keywords'))){
			$condition['goods_name|keywords'] =array('like',array('%'.I('get.keywords').'%'),'AND');
			$this->assign('keywords',I('get.keywords'));
		}
		if(!empty(I('get.consumption_type'))){
			$condition['consumption_type'] = I('get.consumption_type');
			$this->assign('consumption_type',$condition['consumption_type']);
		}
		$data = page(M('Goods'), $condition,20,'','goods_id desc','goods_id,goods_name,shop_price,is_on_sale,is_best,is_new,is_hot,floor_show,examine_status,consumption_type');
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','商品列表');
		$this->assign('all_goods',I('get.all_goods'));
		$this->display('Goods/goods_list');
	}
	/**
	 * 添加商品模板页
	 */
	public function view(){
		$this->assign('class_list',R('Class/classTree'));
		$this->assign('tag_list',$this->tags(''));
		$this->assign('content_header','添加商品');
		$this->display('Goods/info');
	}
	/**
	 * 编辑商品
	 */
	public function edit(){
		$goods_info = D('Goods')->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
		$this->assign('goods_info',$goods_info);
		$this->assign('class_list',R('Class/classTree'));
		$this->assign('tag_list',$this->tags($goods_info['goods_tag']));
		$this->assign('content_header','编辑商品');
		$this->display('Goods/info');
	}
	/**
	 * 保存商品
	 */
	public function save(){
		//通用信息
		$goods_id = empty(I('post.goods_id'))? 0 : I('post.goods_id');
		$data['goods_name'] = I('post.goods_name');
		$data['small_name'] = I('post.small_name');
		$data['goods_sn'] = 'MS_'.time().rand(1000,9999);//商品货号
		$data['gc_id'] = empty(I('post.gc_id')) ? 0 : I('post.gc_id');//商品分类id
		$data['brand_id'] = empty(I('post.brand_id')) ? 0 : I('post.brand_id');//商品分类id
		$data['market_price'] = empty(I('post.market_price')) ? 0: I('post.market_price');//市场价
		$data['shop_price'] = empty(I('post.shop_price')) ? 0 : I('post.shop_price');//本店售价
		$data['max_buy'] = empty(I('post.max_buy')) ? 0 : I('post.max_buy');//最大购买量
		$data['goods_desc'] = $_POST['goods_desc'];//商品描述
		$data['goods_weight'] = empty(I('post.goods_weight')) ? 0 : I('post.goods_weight');//商品种类
		$data['is_on_sale'] = I('post.is_on_sale',0);//是否上架
		$data['freight_type'] = I('post.freight_type');//运费计算方式
		$data['freight'] = empty(I('post.freight_type'))? 0 : I('post.freight_type');//运费
		$data['keywords'] = I('post.keywords');//seo关键字
		$data['description'] = I('post.description');//seo描述
		$data['mid'] = empty(I('post.mid')) ? 0 : I('post.mid');//商品模型id
		$data['examine_status'] = 1;//1：审核通过,  2：未审核 ,3： 审核不通过
		$data['goods_tag'] = implode(',', I('post.goods_tag_checkbox')).I('post.goods_tag_radio');//商品标签
		$data['consumption_type'] = I('post.consumption_type');
// 		$data['yjt_can'] = implode(',', I('post.yjt_can'));//可使用的一卷通数量
// 		$data['gwq_can'] = implode(',', I('post.gwq_can'));//可使用的购物券数量
		$data['give_integral'] = I('post.give_integral');//赠送多少积分
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
				$res = Image::upload('goods_img', 'MALL_SELLER',true,$this->system_store_id.'/Goods/');
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
		//扩展信息
		$extend_id = I('post.extend_id');
		$atv_id = I('post.atv_id');
		$add_price = I('post.add_price');
		$atv_id_count = count($atv_id);
		for($i=0;$i<$atv_id_count;$i++){
			if($extend_id[$i]){
				$data['goods_extend'][$i]['extend_id'] = $extend_id[$i];
			}
			$data['goods_extend'][$i]['atv_id'] = $atv_id[$i];
			$data['goods_extend'][$i]['add_price'] = empty($add_price[$i]) ? 0 : $add_price[$i];
		}
		$Goods = D('Goods');
		
		if($goods_id){
			$data['goods_id'] = $goods_id;
			//判断是否删除了就的扩展信息
			if(empty($extend_id)){
				M('GoodsExtend')->where('goods_id = '.$goods_id)->delete();
			}
			//更新
			if($Goods->relation(true)->save($data)){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
		}else{
			//新增
			if($Goods->relation(true)->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 删除图片
	 */
	public function removeImg(){
		$goods_id = I('get.goods_id');
		$img_name = I('get.img_name');
		$general_path = './Uploads/'.C('MALL_SELLER').$this->system_store_id.'/Goods/';
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
			$this->ajaxJsonReturn('','',1);
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}
	/**
	 * 删除扩展信息
	 */
	public function removeExtend(){
		if(M('GoodsExtend')->where('extend_id = '.I('get.extend_id'))->delete()){
			$this->ajaxJsonReturn('','',1);
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}
	/**
	 * 修改产品状态
	 * 上架，新品，精品，热销
	 */
	public function updateStatus(){
		$data['goods_id']=I('get.goods_id');
		$data[I('get.type')]=I('get.value')==1 ? 0 : 1;
		if(M('Goods')->save($data)){
			$result = array(
					'goods_id'=>$data['goods_id'],
					'value'=>$data[I('get.type')],
			);
			$this->ajaxJsonReturn($result);
		}else{
			$this->ajaxJsonReturn('','',0);
	
		}
	}
	/**
	 * 审核
	 */
	public function examine(){
		$data['examine_status'] = I('param.examine_status');
		if($data['examine_status'] == 3){
			$data['is_on_sale'] = 0;
		}
		if(is_array(I('param.goods_id'))){
			$condition['goods_id'] = array('in',I('param.goods_id'));
		}else{
			$condition['goods_id'] = I('param.goods_id');
		}
		if(M('Goods')->where($condition)->save($data)){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
	/**
	 * 上架/下架
	 */
	public function onsale(){
		$condition['goods_id'] = array('in',I('param.goods_id'));
		if(M('Goods')->where($condition)->setField('is_on_sale',I('param.examine_status'))){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
	
	public function consumption(){
		$condition['goods_id'] = array('in',I('param.goods_id'));
		if(M('Goods')->where($condition)->setField('consumption_type',I('param.examine_status'))){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
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
	 * 处理选中的标签
	 */
	private function tags($goods_tag){
		$check_tag_array = explode(',', $goods_tag);
		$tag_list = TagController::getTagList('Mall');
		$data = array();
		foreach ($tag_list['tag_radio'] as $item){
			if(in_array($item, $check_tag_array)){
				$data['tag_radio'][] = array(
						'tag'=>$item,
						'checked'=>1,
				);
			}else{
				$data['tag_radio'][] = array(
						'tag'=>$item,
						'checked'=>0,
				);
			}
		}
		foreach ($tag_list['tag_checkbox'] as $item){
			if(in_array($item, $check_tag_array)){
				$data['tag_checkbox'][] = array(
						'tag'=>$item,
						'checked'=>1,
				);
			}else{
				$data['tag_checkbox'][] = array(
						'tag'=>$item,
						'checked'=>0,
				);
			}
		}
		return $data;
	}
}