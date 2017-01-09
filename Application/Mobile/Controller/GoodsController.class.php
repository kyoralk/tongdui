<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;

class GoodsController extends InitController{
	/**
	 * 获取的符合条件的goods_id
	 * @param string $atv_id_str
	 */
	private function filterGoods($atv_id_str){
		$condition['atv_id'] = array('in',explode(',', $atv_id_str));
		$goods_id_array = M('GoodsExtend')->distinct(true)->where($condition)->getField('goods_id',true);
		return $goods_id_array;
	}
	/**
	 * 分类树
	 * @param int $gc_id
	 */
	private function gcTree($gc_id){
		$path[] = $gc_id;
		$GoodsClass = M('GoodsClass');
		do{
			$gc_parent_id = $GoodsClass->where('gc_id in ('.$gc_id.')')->getField('gc_parent_id');
			if($gc_parent_id){
				$path[] = $gc_parent_id;
				$gc_id = $gc_parent_id;
			}
		}while($gc_parent_id);
		return $path;
	}
	/**
	 * 处理分类id
	 */
	private function hgcid($gc_id){
		$condition['gc_parent_id'] = $gc_id;
		$data[] = $gc_id;
		$GoodsClass = M('GoodsClass');
		do{
			$gc_id_list =$GoodsClass->where($condition)->getField('gc_id',true);
			if(!empty($gc_id_list)){
				$data = array_merge($data,$gc_id_list);
				$condition['gc_parent_id'] = array('IN',$gc_id_list);
				$gc_id_list = $GoodsClass->where($condition)->getField('gc_id');
			}
		}while (!empty($gc_id_list));
		return $data;
	}
	/**
	 * 同店铺产品
	 */
	private function sglist($store_id,$limit){
		if(S('sglist_'.$store_id)){
			return S('sglist_'.$store_id);
		}else{
			$goods_list = D('GoodsView')->where('is_on_sale = 1 AND g.store_id = '.$store_id)->limit($limit)->order('rand()')->group('goods_id')
			->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,evaluate_count,store_id')->select();
			S('sglist_'.$store_id,$goods_list,60);
			return $goods_list;
		}
	}
	/**
	 * 同类产品
	 */
	private function tlglist($gc_id,$limit){
		if(S('tlglist_'.$gc_id)){
			return S('tlglist_'.$gc_id);
		}else{
			$goods_list = D('GoodsView')->where('is_on_sale = 1 AND gc_id = '.$gc_id)->limit($limit)->order('rand()')->group('goods_id')
			->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,evaluate_count,store_id')->select();
			S('tlglist_'.$gc_id,$goods_list,60);
			return $goods_list;
		}
	}
	/**
	 * sku
	 * @param int $goods_id
	 */
	private function sku($goods_id){
		$data = array();
		$extend_list = M('GoodsExtend ge')
		->join('__MODEL_ATTR_VALUE__ atv on ge.atv_id = atv.atv_id')
		->join('__MODEL_ATTR__ att on atv.attr_id = att.attr_id')
		->where('ge.goods_id = '.$goods_id)
		->field('ge.atv_id,att.attr_name,atv.attr_id,atv.attr_value')
		->select();
		if(!empty($extend_list)){
			foreach ($extend_list as $extend){
				$attr_value[$extend['attr_id']][] = array('atv_id'=>$extend['atv_id'],'attr_value'=>$extend['attr_value']);
				$data[$extend['attr_id']] = array(
						'attr_name'=>$extend['attr_name'],
						'attr_value'=>$attr_value[$extend['attr_id']],
							
				);
			}
			$data = array_values($data);
		}
		return $data;
	}
	/**
	 * 商品分类
	 */
	public function goodsClass(){
		$gc_parent_id = I('get.gc_parent_id');
		$GoodsClass = M('GoodsClass');
		if(F('goods_class')){
			$goods_class = F('goods_class');
		}else{
			$goods_class = $this->setGoodsClass();
		}
		if($gc_parent_id){
			if($gc_parent_id != 0){
				$result = $goods_class['_child'][$gc_parent_id];
			}else{
				$result = $goods_class['root'];
			}
		}else{
			$result = $goods_class['all'];
		}
		jsonReturn($result);
	}		
	/**
	 * 商品列表
	 */
	public function glist(){
	    if ($this->member_info) {

        }
		$order_array = array('shop_price,sales desc','shop_price','shop_price desc','sales','sales desc');
		$order_index = I('get.order_index',0);
		$gc_id = I('get.gc_id',false);
		$keywords = I('get.keywords',false);
		$atv_id_str = I('get.atv_id_str',false);
		$brand_id = I('get.brand_id',false);
		$range = I('get.range',false);
		$store_id = I('get.store_id',false);
		$goods_id = I('get.goods_id');
		$is_best = I('get.is_best',0);
		$is_new = I('get.is_new',0);
		$is_hot = I('get.is_hot',0);
		$consumption_type = I('get.consumption_type',0);
		if($is_best){
			$condition['is_best'] = 1;
		}
		if($is_new){
			$condition['is_new'] = 1;
		}
		if($is_hot){
			$condition['is_hot'] = 1;
		}
		if($store_id){
			$condition['g.store_id'] = $store_id;
		}
		if($keywords){
			$condition['goods_name|keywords'] =array('like',array('%'.$keywords.'%'),'AND');
		}
		if($gc_id){
			$condition['gc_id'] = array('in',$this->hgcid($gc_id));
		}
		if($consumption_type){
			$condition['consumption_type'] = $consumption_type;
		}
		if($atv_id_str){
			$_goods_id = $this->filterGoods($atv_id_str);
			if(!empty($_goods_id)){
				$condition['goods_id'] = array('in',$_goods_id);
			}else{
				$condition['goods_id'] = 0;
			}
			
		}
		if(!empty($goods_id)){
			if(strlen($goods_id)>1){
				$condition['goods_id'] = array('in',$goods_id);
			}else{
				$condition['goods_id'] = $goods_id;
			}
		}
		if($brand_id){
			$condition['brand_id'] = $brand_id;
		}
		if($range!='all'&$range!=false){
			if(preg_match("/[\x7f-\xff]/", $range)&!empty($range)){
				$range=explode('以上', $range);
				$condition['shop_price']=array('gt',$range[0]);
			}else{
				$range=explode('-', $range);
				$condition['shop_price']=array('between',array($range[0],$range[1]));
			}
		}
		$condition['is_on_sale'] = 1;
		$condition['store_status'] = 1;
		$condition['examine_status'] = 1;
		$count = M('Goods g')->join('__STORE__ s on g.store_id = s.store_id')->where($condition)->count();
		$Goods = D('GoodsView');
		$data = appPage($Goods, $condition, I('get.num'), I('get.p'),'view',$order_array[$order_index],'goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,store_id,evaluate_count,click_count,sales,consumption_type,gwq_send,gwq_extra,love_amount','goods_id',$count);

		// 升级增加补差额功能
		$data = $this->handleGoods($data);

		jsonReturn($data);
	}
	/**
	 * 商品详情
	 */
	public function info(){
		$Goods = D('Goods');
		$Goods->where('goods_id = '.I('get.goods_id'))->setInc('click_count');//添加点击次数（关注度）
		$data['goods_info'] = $Goods->relation(array('goods_img','goods_spec'))->where('goods_id = '.I('get.goods_id'))->find();
		$data['sku'] = $this->sku(I('get.goods_id'));
		if(I('get.store') == 1){
			$data['store_info'] = StoreController::getInfo($data['goods_info']['store_id']);//店铺信息
		}
		if(I('get.sg')==1){
			$data['sglist'] = $this->sglist($data['goods_info']['store_id'], 20);//同店铺产品
		}
		if(I('get.tl')==1){
			$data['tlglist'] = $this->tlglist($data['goods_info']['gc_id'], 20);//同类产品
		}
		foreach ($data['goods_info']['goods_spec'] as $spec){
			$spec_them[$spec['spec_value']] = $spec;
		}
		$data['goods_info']['goods_spec'] = $spec_them;
		$data['goods_info'] = $this->handleGood($data['goods_info']);
		unset($data['goods_info']['goods_desc']);
		jsonReturn($data);
	}	
	
	/**
	 * 商品介绍
	 */
	public function desc(){
		$set  = $this->setting();
		$goods_desc = M('Goods')->where('goods_id = '.I('get.goods_id'))->getField('goods_desc');
		$this->assign('goods_desc',htmlspecialchars_decode($goods_desc));
		$this->assign('domain',$set['domain']);
		$this->display();
	}
	/**
	 * 筛选器
	 */
	public function filter(){
		//获取分类递归树
		$gc_id_tree = $this->gcTree(I('get.gc_id'));
		$GoodsClass = M('GoodsClass');
		$mid_list = $GoodsClass->where(array('gc_id'=>array('in',$gc_id_tree)))->order('gc_id desc')->getField('mid',true);
		foreach ($mid_list as $item){
			if(!empty($item)){
				$mid = $item;
				break;
			}
		}
		$Attr = D('ModelAttr');
		$filter_list['attr'] = $Attr->relation(true)->where('mid = '.$mid)->select();
		$model_info = M('Model')->where('mid = '.$mid)->field('range,keywords,brand_list')->find();
		$filter_list['range'] = explode(',', $model_info['range']);
        $model_info['brand_list'] = !empty($model_info['brand_list'])?$model_info['brand_list']:'0';
		$filter_list['brand'] = M('Brand')->where('brand_id in ('.$model_info['brand_list'].')')->order('brand_sort')->select();
		jsonReturn($filter_list);
	}

	function handleGoods($data) {
		if ($this->member_info) {
			$fee = $this->member_info['upgrade_fee'];
			if ($data['list']) {
				foreach ($data['list'] as $k=>$v) {
					if (strstr($v['goods_name'], '套餐') && $v['store_id'] == 1) {
						$data['list'][$k]['shop_price'] = $v['shop_price'] - $fee;
						$data['list'][$k]['shop_price'] = $data['list'][$k]['shop_price']<0?0:$data['list'][$k]['shop_price'];	
					}
				}
			}
		} 

		return $data;
		
	}

	function handleGood($data) {
		if ($this->member_info) {
			$fee = $this->member_info['upgrade_fee'];
			if (strstr($data['goods_name'], '套餐') && $data['store_id'] == 1) {
				$data['shop_price'] = $data['shop_price'] - $fee;
				$data['shop_price'] = $data['shop_price']<0?0:$data['shop_price'];	
			}
		} 

		return $data;
	}

}