<?php
namespace Mobile\Model;
use Think\Model\RelationModel;

class StoreModel extends RelationModel{
	protected $_link = array(
			'store_goods_class' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'store_goods_class',
					'foreign_key'=>'store_id',
					'mapping_name'=>'class',
			),
			'store_slide' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'store_slide',
					'foreign_key'=>'store_id',
					'mapping_name'=>'slide',
			),
	);
}