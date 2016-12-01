<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class MemberModel extends RelationModel{
// 	protected $tableName  = 'pt_common_member';
	protected $_link = array(
			'Member' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'Member',
					'parent_key'=>'referrer_id',
					'as_fields'=>'username:referrer_name',
			),
			'member_account'=>array(
					'mapping_type'=>self::HAS_ONE,
					'class_name'=>'member_account',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_account',
			),
			'member_node'=>array(
					'mapping_type'=>self::HAS_ONE,
					'class_name'=>'member_node',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_node',
			),
	);
}