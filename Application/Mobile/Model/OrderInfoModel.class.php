<?php
namespace Mobile\Model;
use Think\Model\RelationModel;

class OrderInfoModel extends RelationModel{
	protected $_link = array(
			'order_goods' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'order_goods',
					'foreign_key'=>'order_sn',
					'mapping_name'=>'order_goods',
			),
			'store' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'store',
					'foreign_key'=>'store_id',
					'mapping_name'=>'store',
					'mapping_fields'=>'store_id,store_name',
			),
	);
}