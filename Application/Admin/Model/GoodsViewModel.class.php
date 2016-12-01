<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel{
	public $viewFields = array(
			'Goods'=>array('goods_id','goods_sn','goods_name','cost_price','shop_price','sales','store_id','_as'=>'g','_type'=>'LEFT'),
			'Store'=>array('uid','username','store_name','company_name','_on'=>'g.store_id = s.store_id','_as'=>'s'),
	);
}