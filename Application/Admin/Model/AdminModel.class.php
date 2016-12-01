<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class AdminModel extends RelationModel{
	protected $_link = array(
			'role' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'role',
					'foreign_key'=>'role_id',
					'as_fields'=>'role_name,menu_id_str',
			),
	
	);
}