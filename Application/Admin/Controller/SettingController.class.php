<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class SettingController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIC_C'));
	}
	/**
	 * 详情
	 */
	public function info(){
		$item = I('get.item',0);
		$set_list = M('Setting')->where('item = '.$item)->order('item_sort')->select();
		$this->assign('set_list',$set_list);
		$this->assign('item',$item);
		$this->display();
	}
	/**
	 * 保存
	 */
	public function save(){
		$item = I('post.item');
		$item_name = I('post.item_name');
		$item_key = I('post.item_key');
		$item_value = I('post.item_value');
		$item_sort = I('post.item_sort'); 
		$i = 0;
		while (!empty($item_name[$i])){
			$data[$i] = array(
					'item'=>$item,
					'item_name'=>$item_name[$i],
					'item_key'=>$item_key[$i],
					'item_value'=>$item_value[$i],
					'item_sort'=>$item_sort[$i],
			);
			$i++;
		}
		$Setting = M('Setting');
		$Setting->startTrans();
		try {
			$Setting->where('item = '.$item)->delete();
			$Setting->addAll($data);
		} catch (Exception $e) {
			$Setting->rollback();
			$this->error('保存失败');
			exit();
		}
		$Setting->commit();
		$this->setCache($item);
		$this->success('保存成功');
	}
	/**
	 * 设置缓存
	 * @param int $item
	 */
	private function setCache($item){
		$set_list = M('Setting')->where('item = '.$item)->select();
		foreach ($set_list as $loop){
			$set[$loop['item_key']] = $loop['item_value'];
		}
		F('setting_'.$item,$set);
	}
}