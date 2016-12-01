<?php
namespace Mall\Controller;
use Mall\Controller\InitController;

class CommonController extends InitController{
	protected $uid;
	protected function _initialize(){
		parent::_initialize();
		if($this->isLogin()){
			$this->uid = session('uid');
		}else{
			redirect('/Public/login');
		}
	}	
}