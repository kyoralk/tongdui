<?php
namespace Seller\Controller;
use General\Controller\GeneralController;

class CommonController extends GeneralController{
	protected $uid;
	protected function _initialize(){
		parent::_initialize();
		if($this->isLogin()){
			$this->uid = session('uid');
		}else{
			redirect('/Seller/Public/login');
		}
	}

    /**
     * 处理赠送的购物券
     */
    protected function sendGWJ($goods_info){
        $goods = M('Goods')->where('goods_id ="'.$goods_info['goods_id'].'"')->find();
        if ($goods) {
            if ($goods['consumption_type'] == 3) {
                $rate = M('Setting')->where('item_key = gwqrate')->find();
                $price = intval($rate*$goods['shop_price']/100) + $goods['gwq_extra'];
            } else {
                // 一券通商品不佔比率
                $price = $goods['gwq_send'] + $goods['gwq_extra'];
            }
        }

        return $price;
    }
}