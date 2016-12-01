<?php
namespace Seller\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel{
	public $viewFields = array(
			'Goods'=>array('goods_id','goods_name','shop_price','is_on_sale','is_best','is_new','is_hot','floor_show','store_id','store_tuijian','sales','click_count','examine_status', '_type'=>'LEFT'),
			'GoodsImg'=>array('save_name','save_path','_on'=>'Goods.goods_id=GoodsImg.goods_id'),
	);
}