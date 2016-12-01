<?php
namespace Mall\Controller;
use General\Controller\GeneralController;

class InitController extends GeneralController{
	protected function _initialize(){
		parent::_initialize();
		if($this->isMobile()){
			C('DEFAULT_THEME','WAP');
		}else{
			C('DEFAULT_THEME','PC');
		}
		$this->assign('set',self::setting());//网站设置
		$this->assign('nav_list',$this->getNav());//导航
		$this->assign('left_menu',$this->goodsClass());//商品分类--首页左侧菜单
	}
	/**
	 * 导航
	 */
	protected function getNav(){
		if(F('nav_list')){
			return F('nav_list');
		}else{
			$nav_list=M('Navigation')->where('is_show = 1')->order('nav_sort')->select();
			F('nav_list',$nav_list);
			return $nav_list;
		}
	}
	/**
	 * 商品分类
	 */
	protected function goodsClass(){
		if(F('goods_class')){
			$goods_class = F('goods_class');
		}else{
			$them_list = M('GoodsClass')->field('gc_id,gc_name,gc_parent_id')->select();
			$goods_class = list_to_tree($them_list,'gc_id','gc_parent_id');
			F('goods_class',$goods_class);
		}
		return $goods_class;
	}
	

}