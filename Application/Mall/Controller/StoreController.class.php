<?php
namespace Mall\Controller;
use Mall\Controller\InitController;

class StoreController extends InitController{
	//店铺信息
	static function info($store_id){
		if(F('store_info_'.$store_id)){
			M('Store')->where('store_id = '.$store_id)->setInc('click_count');
			return F('store_info_'.$store_id);
		}else{
			$store = M('Store');
			$store_info=$store->where('store_id = '.$store_id)->find();
			$store->where('store_id = '.$store_id)->setInc('click_count');
			F('store_info_'.$store_id,$store_info);
			return $store_info;
		}
	}
}