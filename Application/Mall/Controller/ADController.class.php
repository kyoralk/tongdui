<?php
namespace Mall\Controller;
use Think\Controller;

class ADController extends Controller{
	protected function _initialize(){
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 获取广告
	 */
	public function output($position_id,$class_name){
		$adsense_list = D('Adsense')->relation(true)->where('enabled = 1 AND position_id = '.$position_id)->select();
		$i = 0;
		$ad_html = '';
		foreach ($adsense_list as $ad){
			$ad_html .='<li';
			if($i==0){
				$ad_html .=' class="'.$class_name.'" ';
			}
			$ad_html .=' >';
			$ad_html .= '<a href="'.$ad['ad_url'].'" ';
			if($ad['is_target'] == 1){
				$ad_html .=' target="_blank"';
			}
			$ad_html .=' >';
			$ad_html .= '<img width="'.$ad['ad_width'].'" height="'.$ad['ad_height'].'" src="/Uploads/Adsense/'.$ad['ad_value'].'"></a>';
			$ad_html .= '</li>';
		}
		return $ad_html;
	}
	/**
	 * ajax获取广告
	 */
	public function ajaxAD(){
		$ad_html = $this->getAd(I('get.id'), I('get.class_name'));
		echo $ad_html;
	}
}