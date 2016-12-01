<?php
namespace Mall\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel{
	public $viewFields = array(
			'Member'=>array('*'),
			'MemberRelation'=>array('bc_id','_on'=>'Member.uid=MemberRelation.uid'),
	);
}