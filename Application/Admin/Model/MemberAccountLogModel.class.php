<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class MemberAccountLogModel extends RelationModel{
	protected $_link = array(
			'member' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'member',
					'foreign_key'=>'uid',
					'as_fields'=>'username',
			),
	);
}