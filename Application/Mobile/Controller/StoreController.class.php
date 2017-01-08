<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;

class StoreController extends InitController{
	/**
	 * 店铺信息
	 */
	public static function getInfo($store_id){
		return M('Store')->where('store_id = '.$store_id)->find();
	}
	/**
	 * 店铺主页
	 */
	public function home(){
		$condition['store_id'] = I('get.store_id',0);
		$data = D('Store')->relation(true)->where($condition)->find();
		$data['goods_count'] = M('Goods')->where($condition)->count();
		$condition['store_tuijian'] = 1;
		$data['goods_list'] = D('GoodsView')->where($condition)->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,evaluate_count,click_count,consumption_type,gwq_send,gwq_extra,love_amount')->limit(20)->order('goods_id')->group('goods_id')->select();
		jsonReturn($data);
	}
	/**
	 * 店铺列表
	 */
	public function storeList(){
		$sc_id = I('get.sc_id',false);
		if($sc_id){
			$condition['sc_id'] = $sc_id;
		}
		$condition['store_status'] = 1;
		$data = appPage(M('Store'), $condition, I('get.num'), I('get.p'),'','store_sort desc','store_id,store_name,store_logo,store_banner,store_credit,praise_rate,store_desccredit,store_servicecredit,store_deliverycredit,collect_times');
		jsonReturn($data);
	}
	

	
	
}