<?php
namespace Mobile\Model;
use Think\Model\ViewModel;

class CollectStoreViewModel extends ViewModel{
	public $viewFields = array(
			'CollectStore'=>array('id','_as'=>'c','_type'=>'LEFT'),
			'Store'=>array('*','_as'=>'s','_on'=>'c.store_id = s.store_id'),
	);
}