<?php
namespace Seller\Model;
use Think\Model\RelationModel;

class ShippingTemplateModel extends RelationModel{
	protected $_link = array(
			'shipping_area' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'shipping_area',
					'foreign_key'=>'templ_id',
					'mapping_name'=>'shipping_area',
			),
	
	);
}