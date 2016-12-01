<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;

class AccountController extends CommonController{
	/**
	 * 账户详情
	 */
	public function info(){
		$account = M('Store')->where('store_id = '.session('store_id'))->field('bank_account_name,bank_account_number,bank_name')->find();
		$this->assign('account',$account);
		$this->display();
	}
	/**
	 * 保存账户信息
	 */
	public function save(){
		if(is_numeric(M('Store')->where('store_id = '.session('store_id'))->save(array('bank_account_name'=>I('post.bank_account_name'),'bank_account_number'=>I('post.bank_account_number'))))){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
}