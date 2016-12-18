<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class CartController extends CommonController{
	private $goods_info;
	protected $Cart;
	protected function _initialize(){
		parent::_initialize();
		$this->Cart = M('Cart');
	}
	/**
	 * 按照店铺把商品分组
	 */
	public function groupByStore($cart_id = ''){
		$data = array();
		if(!empty($cart_id)){
			$condition['c.cart_id'] = array('in',explode(',', $cart_id));
		}
		$condition['c.uid'] = $this->member_info['uid'];
		//商家的产品
		$cart_list = 
					$this->Cart->alias('c')
								->join('__STORE__ s on c.store_id = s.store_id')
								->where($condition)
								->field('c.*,s.store_name')
								->select();
		$i = 0;
		$total = 0;
		while (!empty($cart_list[$i])){
			$cart_list[$i]['extend'] =  $this->goodsExtend($cart_list[$i]['atv_id_str']);
			$cart_list[$i]['extend_str'] = empty($cart_list[$i]['extend']) ? '' : serialize($cart_list[$i]['extend']);
			$total += $cart_list[$i]['price'] * $cart_list[$i]['prosum'];
			$data_0[$cart_list[$i]['store_id']][] = $cart_list[$i];
			$i++;
		}	
		foreach ($data_0 as $k=>$v){
			$data['store'][] = array(
					'store_id'=>$k,
					'store_name'=>$v[0]['store_name'],
					'goods_list'=>$v,
			);
			$data['total'] = $total;
		}
		return $data;
	}
	/**
	 * 判断是否限购
	 */
	private function purchase($prosum){
		if($this->goods_info['max_buy']>0){
			$condition['uid'] = $this->member_info['uid'];
			$condition['goods_id']=$this->goods_info['goods_id'];
			$count=$this->Cart->where($condition)->count();//查询同一个人是否有相同的产品
			if($count+$prosum>=$max_buy){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	/**
	 * 判断是够购买自己的产品
	 */
	private function isBuySelf(){
		if($this->member_info['store_id']>0 & $this->member_info['store_id'] == $this->goods_info['stroe_id']){
			jsonReturn('','01021');
		}
	}
	
	/**
	 * 加入购物车
	 */
	public function _before_add(){
		//获取产品信息
		$this->goods_info = $this->goods(I('post.goods_id'),I('post.spec_id'));   
	}
	public function add(){
		$prosum = I('post.prosum',1);
		$this->purchase($prosum);//检测限购
		$this->isBuySelf();//检测是否是购买自己的产品
		$cart = M('Cart'); 
		$condition['uid']=$this->member_info['uid'];
		if(empty($this->goods_info['spec_id'])){
			$condition['goods_id']=$this->goods_info['goods_id'];
		}else{
			$condition['spec_id']=$this->goods_info['spec_id'];
		}
		$count=$this->Cart->where($condition)->count();//查询同一个人是否有相同的产品
		if($count==0){
			$this->goods_info['spec_name'] = empty($this->goods_info['spec_name'])?'':'（'.$this->goods_info['spec_name'].'）';
			$data = array(
					'cart_id'=>time().randstr(4,true),
					'uid'=>$this->member_info['uid'],
					//'session_id'=>session_id(),
					'prosum'=>$prosum,
					'cost_price'=>$this->goods_info['cost_price'],
					'goods_id'=>$this->goods_info['goods_id'],
					'goods_name'=>$this->goods_info['goods_name'].$this->goods_info['spec_name'],
					'goods_img'=>$this->goods_info['save_path'].'Thumb/s_'.$this->goods_info['save_name'],
					'store_id'=>$this->goods_info['store_id'],
					'spec_id'=>I('post.spec_id'),
					'consumption_type'=>$this->goods_info['consumption_type'],
			);
			//处理产品价格
			$data['price'] = $this->promotePrice($this->goods_info);
			//根据规格处理价格
			$data['price'] += $this->specPrice($data['spec_id']);
			$data['price'] = $this->upgradePrice($this->goods_info);
			$data['market_price'] = $this->goods_info['market_price'];
			//处理产品价格结束
			if($cart->add($data)){
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}else{
			if($this->Cart->where($condition)->setInc('prosum',$prosum)){
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}
	}
	/**
	 * 从购物车中删除
	 */
	public function delc($cart_id){
		$cart_id = explode(',', $cart_id);
		if($this->Cart->where(array('cart_id'=>array('in',$cart_id)))->delete()){
			return true;
		}else{
			return false;
		}
	}
	public function del(){
		if($this->delc(I('post.cart_id'))){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 更新购物车
	 */
	public function update(){
		$data['prosum'] = explode(',', I('post.prosum'));
		$condition['cart_id'] = explode(',', I('post.cart_id'));
		if(is_numeric($this->saveAll('__CART__', $data, $condition))){
			jsonReturn();
		}else {
			jsonReturn('','00000');
		}
	}
	/**
	 * 查看购物车
	 */
	public function info(){
		$data = $this->groupByStore(I('get.cart_id'));
		jsonReturn((object)$data);
	}

	public function upgradePrice($data) {
		if ($this->member_info) {
			$fee = $this->member_info['upgrade_fee'];
			if (strstr($data['goods_name'], '套餐') && $data['store_id'] == 1) {
				$data['shop_price'] = $data['shop_price'] - $fee;
				$data['shop_price'] = $data['shop_price']<0?0:$data['shop_price'];	
			}
		} 

		return $data['shop_price'];
	}
}