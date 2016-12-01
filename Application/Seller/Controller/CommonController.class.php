<?php
namespace Seller\Controller;
use General\Controller\GeneralController;

class CommonController extends GeneralController{
	protected $uid;
	protected function _initialize(){
		parent::_initialize();
		if($this->isLogin()){
			$this->uid = session('uid');
		}else{
			redirect('/Seller/Public/login');
		}
	}
}