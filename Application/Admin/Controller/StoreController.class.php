<?php
namespace Admin\Controller;
use Admin\Controller\MallController;

class StoreController extends MallController{
	/**
	 * 店铺列表
	 */
	public function storeList(){
		$store_status = I('get.store_status',1);
		$condition['store_status'] = $store_status;
		$data = page(M('Store'), $condition,'20','view','','store_id,username,store_name,sc_id,company_name,company_location,company_address,store_status');
		$this->assign('store_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('store_status',$store_status);
		$this->assign('content_header','店铺列表');
		$this->display('Store/store_list');
	}
	/**
	 * 店铺信息
	 */
	public function info(){
		$store_info = M('Store')->where('store_id = '.I('get.store_id'))->find();
		$apply = M('ApplyStore',C('DB_PREFIX_C'))->where('status > 1 AND store_id = '.I('get.store_id'))->count();
		$this->assign('apply',$apply);
		$this->assign('si',$store_info);
		$this->display();
	}
	/**
	 * 保存店铺信息
	 */
	public function save(){
		$data = array(
				'store_id'=>I('post.store_id'),
				'store_name'=>I('post.store_name'),
				'proportion'=>I('post.proportion'),
				'store_status'=>I('post.store_status'),
		);
		if(is_numeric(M('Store')->save($data))){
			$join_fee = I('post.join_fee',0);
			if($data['store_status']==1 && $join_fee>0){
				$ApplyStore = M('ApplyStore',C('DB_PREFIX_C'));
				$ApplyStore->where('store_id = '.$data['store_id'])->save(array('status'=>1,'join_fee'=>$join_fee));
				$apply_info = $ApplyStore->where('store_id = '.$data['store_id'])->find();
				R('Agent/reward',array($apply_info));
			}
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
}