<?php
namespace Mobile\Model;
use Think\Model\ViewModel;

class CollectGoodsViewModel extends ViewModel{
	public $viewFields = array(
			'CollectGoods'=>array('id','_as'=>'c','_type'=>'LEFT'),
			'Goods'=>array('goods_id','goods_name','small_name','market_price','shop_price','evaluate_count','click_count','store_id','_as'=>'g','_on'=>'c.goods_id = g.goods_id','_type'=>'LEFT'),
			'GoodsImg'=>array('_as'=>'gi','save_name','save_path','_on'=>'c.goods_id = gi.goods_id'),
			
	);
}