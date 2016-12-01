<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class StoreModel extends RelationModel{
	protected $_link = array(
			'goods' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'goods',
					'foreign_key'=>'goods_id',
					'as_fields'=>'goods_id,goods_name,goods',
			),
			
	
	);
}