<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderViewModel extends ViewModel{
	public $viewFields = array(
			'OrderInfo'=>array('*','_as'=>'oi'),
			'OrderGoods'=>array('*','_on'=>'oi.order_sn = og.order_sn','_as'=>'og'),
			'Store'=>array('*','_on'=>'oi.store_id = s.store_id','_as'=>'s'),
	);
}