<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SettlementViewModel extends ViewModel{
	public $viewFields = array(

			'Store'=>array('*','_as'=>'s'),
        'OrderInfo'=>array('*','_on'=>'oi.store_id = s.store_id','_as'=>'oi'),
	);
}