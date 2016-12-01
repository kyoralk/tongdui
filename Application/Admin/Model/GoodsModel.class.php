<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class GoodsModel extends RelationModel{
	protected $_link = array(
			'goods_img' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'goods_img',
					'foreign_key'=>'goods_id',
					'mapping_name'=>'goods_img',
			),
			'goods_extend' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'goods_extend',
					'foreign_key'=>'goods_id',
					'mapping_name'=>'goods_extend',
			),
			'store' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'store',
					'foreign_key'=>'store_id',
					'as_fields'=>'uid,username,store_name,company_name',
			),
	
	);
}