<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class ModelAttrModel extends RelationModel{
	protected $_link = array(
			'model_attr_value' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'model_attr_value',
					'foreign_key'=>'attr_id',
					'mapping_name'=>'attr_value'
			),
	
	);	
}
