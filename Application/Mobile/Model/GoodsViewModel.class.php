<?php
namespace Mobile\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel{
	public $viewFields = array(
			'Goods'=>array('*','_as'=>'g'),
			'GoodsImg'=>array('save_name','save_path','_on'=>'g.goods_id = GoodsImg.goods_id'),
			'Store'=>array('store_status','_on'=>'g.store_id = Store.store_id'),
	);
}