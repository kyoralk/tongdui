<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;
load('@.menu');
class CenterController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		self::getStoreStatus();
	}
	/**
	 * 首页
	 */
	public function index(){
		layout(false);
		$module = I('get.module','home');
		$menu_list = getMenu();
		$this->assign('module_list',$menu_list);
		$this->assign('menu_list',$menu_list[$module]['menu']);
		$this->assign('default_page',$this->defaultPage($module));
		$this->display();
	}
	public function home(){
		$this->assign('store_info',M('Store')->where('store_id = '.session('store_id'))->find());
		$this->display();
	}
	private function defaultPage($key){
		$data = array(
				'home'=>U('Center/home'),
				'store'=>U('Store/setting'),
				'goods'=>U('Goods/goodsList'),
				'order'=>U('Order/olist'),
				'account'=>U('Account/info'),
                'refund'=>U('Refund/index'),
		);
		return $data[$key];
	}
	private function getStoreStatus(){
		if(!is_numeric(session('store_status'))){
			session('store_status',M('Store')->where('store_id = '.session('store_id'))->getField('store_status'));
		}
		if(session('store_status') == 0){
			$this->error('店铺已关闭',U('/Seller'));
		}
		if(session('store_status') == 2){
			$this->error('店铺正在审核中',U('/Seller'));
		}
	}

}