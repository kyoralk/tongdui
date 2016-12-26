<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;

class CommonController extends InitController{
	
	protected function _initialize(){
		parent::_initialize();
		if(empty($this->member_info)){
			jsonReturn('','01020');
		}
	}
	
	
	/**
	 * 获取商品信息
	 * @param unknown $goods_id
	 */
	protected function goods($goods_id,$spec_id=0){
		if($spec_id){
			$goods_info = M('Goods g')
			->join('__GOODS_IMG__ img on g.goods_id = img.goods_id')
			->join('__GOODS_SPEC__ gs on g.goods_id = gs.goods_id')
			->where('g.goods_id = '.$goods_id)->field('g.gwq_send, g.gwq_extra, g.is_cash, g.is_gwq, g.is_yqt, g.goods_id,g.goods_name,g.max_buy,g.store_id,g.promote_price,g.promote_start_date,g.promote_end_date,g.shop_price,g.market_price,g.consumption_type,cost_price,img.save_name,img.save_path,spec_name')
			->group('g.goods_id')
			->find();
		}else {
			$goods_info = M('Goods g')
			->join('__GOODS_IMG__ img on g.goods_id = img.goods_id')
			->where('g.goods_id = '.$goods_id)->field('g.gwq_send, g.gwq_extra, g.is_cash, g.is_gwq, g.is_yqt, g.goods_id,g.goods_name,g.max_buy,g.store_id,g.promote_price,g.promote_start_date,g.promote_end_date,g.shop_price,g.market_price,g.consumption_type,cost_price,img.save_name,img.save_path')
			->group('g.goods_id')
			->find();
		}

		return $goods_info;
	}
	/**
	 * 解析属性字符串
	 * @param string $atv_id_str
	 * @param stirng $cart_id
	 */
	protected function goodsExtend($atv_id_str){
		$atv_id_array = explode(',', $atv_id_str);
		if(!empty($atv_id_array)){
			$extend = M('ModelAttrValue atv')->join('__MODEL_ATTR__ attr on atv.attr_id = attr.attr_id')->where(array('atv.atv_id'=>array('IN',$atv_id_array)))->field('attr.attr_name,attr_value')->select();
		}
		return (array) $extend;
	}
	/**
	 * 处理活动价格
	 * @param array $goods_info
	 */
	protected function promotePrice($goods_info){
		//处理商品价格
		$now_time=time();
		if($goods_info['promote_price']!=0&$now_time>$goods_info['promote_start_date']&$now_time<$goods_info['promote_end_date']){
			$price=$goods_info['promote_price'];
		}else{
			$price = $goods_info['shop_price'];
		}
		return $price;
	}

	/**
	 * 处理赠送的购物券
	 */
	protected function sendGWJ($goods_info){
        $goods = M('Goods')->where('goods_id ="'.$goods_info['goods_id'].'"')->find();
        if ($goods) {
            //处理商品价格
            $price = intval($goods['gwq_send']*$goods['shop_price']/100) + $goods['gwq_extra'];
        }

		return $price;
	}


	/**
	 * 处理扩展价格【 冗余方法后期删除】
	 * @param string $atv_id_str
	 */
	protected function extendPrice($atv_id_str){
		$add_price = 0;
		if(!empty($data['atv_id_str'])){
			$add_price = M('GoodsExtend')->where('atv_id in('.$atv_id_str.')')->sum('add_price');
		}
		return $add_price;
	}
	protected function specPrice($spec_id){
		$add_price = 0;
		if(!empty($spec_id)){
			$add_price = M('GoodsSpec')->where('spec_id = '.$spec_id)->getField('spec_add_price');
		}
		return $add_price;
	}
	/**
	 * 检测个人资料是否完整
	 */
	protected function checkMemberInfo(){
		$check_data = array(
				'real_name'=>$this->member_info['real_name'],
				'card_id'=>$this->member_info['card_id'],
				'residence'=>$this->member_info['residence'],
// 				'bank_name'=>$this->member_info['bank_name'],
// 				'bank_address'=>$this->member_info['bank_address'],
// 				'bank_num'=>$this->member_info['bank_num'],
				'alipay_id'=>$this->member_info['alipay_id'],
// 				'wechat_id'=>$this->member_info['wechat_id'],
				'mobile'=>$this->member_info['mobile'],
		);
		$error = 0;
		foreach ($check_data as $item){
			if(empty($item)){
				$error++;
			}
		}
		if($error){
			jsonReturn('','01019');
		}
	}

	
	
	
}