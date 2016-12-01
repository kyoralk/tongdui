<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;

class IndexController extends InitController{
	/**
	 * 首页幻灯
	 */
	public function slide(){
		if(F('slide')){
			jsonReturn(F('slide'));
		}else{
			$slide = M('Slide',C('DB_PREFIX_C'));
			$condition['position'] = 2;
			$condition['enabled'] = 1;
			$condition['module'] = C('MODULE_NAME');
			$slide_list=$slide->where($condition)->order('sort')->select();
			F("slide",$slide_list);
			jsonReturn($slide_list);
		}
	}
	
	public function ad(){
		$ad_list = M('Adsense',C('DB_PREFIX_C'))->where('ad_id in(1,2,3)')->select();
		jsonReturn($ad_list);
	}
}