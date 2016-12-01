<?php
namespace Mobile\Model;
use Think\Model\RelationModel;

class AddressModel extends RelationModel{
	protected $_link = array(
			'member' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'member',
					'foreign_key'=>'uid',
					'mapping_name'=>'member',
			),
	);
}