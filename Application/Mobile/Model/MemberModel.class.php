<?php
namespace Mobile\Model;
use Think\Model\RelationModel;
class MemberModel extends RelationModel{
	
	protected $_link = array(
			'member_account' =>array(
					'mapping_type'=>self::HAS_ONE,
					'class_name'=>'member_account',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_account',
			),
	);
}