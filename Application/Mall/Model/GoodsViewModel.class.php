<?php
namespace Mall\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel{
	public $viewFields = array(
			'Goods'=>array('*'),
			'GoodsImg'=>array('save_name','save_path','_on'=>'Goods.goods_id = GoodsImg.goods_id'),
	);
}