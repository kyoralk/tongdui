<?php
namespace General\Model;
use Think\Model\RelationModel;

class ModelAttrValueModel extends RelationModel{
	protected $_link = array(
			'model_attr' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'model_attr',
					'foreign_key'=>'attr_id',
					'mapping_name'=>'model_attr',
					'as_fields'=>'attr_id,mid,attr_name,attr_input_type,attr_type,kxcs,sxjs,sxsx',
			),
	
	);
}