<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SettlementViewModel extends ViewModel{
	public $viewFields = array(
			'OrderInfo'=>array('*','_as'=>'oi'),
			'Store'=>array('*','_on'=>'oi.store_id = s.store_id','_as'=>'s'),
	);
}