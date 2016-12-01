<?php
namespace Mall\Model;
use Think\Model\RelationModel;
class MemberModel extends RelationModel{
	
	protected $_link = array(
			'member_relation' =>array(
					'mapping_type'=>self::HAS_ONE,
					'class_name'=>'member_relation',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_relation',
					//'as_fields'=>'referrer_id,bc_id,left_id,right_id,lyj,ryj,yj,ljz,rjz',
			),
			'member_account' =>array(
					'mapping_type'=>self::HAS_ONE,
					'class_name'=>'member_account',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_account',
			),
			'member_floor' =>array(
					'mapping_type'=>self::HAS_MANY,
					'class_name'=>'member_floor',
					'foreign_key'=>'uid',
					'mapping_name'=>'member_floor',
			),
	);
	protected $_validate = array(
			array('username','require','用户名不能为空！'),
			array('email','require','email不能为空！'),
// 			array('email','email','email格式错误'),
			array('real_name','require','真实姓名不能为空！'),
			array('card_id','require','身份证不能为空！'),
			array('mobile','require','手机号不能为空！'),
			array('password','require','密码不能为空！'),
			array('password_2','require','密码不能为空！'),
			array('email','','邮箱已经使用！',0,'unique',1), // 在新增的时候验证emial字段是否唯一
			array('username','','用户名已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
			array('re_password','password','确认密码不正确',0,'confirm',1), // 验证确认密码是否和密码一致
			array('re_password_2','password_2','确认密码不正确',0,'confirm',1), // 验证确认密码是否和密码一致
			array('card_id','checkCard','身份证已经使用',1,'callback'),
	);
	
	protected function checkCard(){
		$count = $this->where('card_id = '.I('post.card_id'))->count();
		if($count>3){
			return false;
		}else{
			return true;
		}
		
	}
	
}