<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class TagController extends CommonController{
	/**
	 * 标签详情
	 */
	public function info(){
		$module = I('get.module');
		$tag = M('Tag',C('DB_PREFIX_C'))->where('module = "System" OR module = "'.$module.'"')->select();
		$this->assign('system_tag',$tag[0]['tag_radio'].$tag[0]['tag_checkbox']);
		$this->assign('module_tag',$tag[1]);
		$this->assign('module',$module);
		$this->display();
	}
	/**
	 * 保存标签
	 */
	public function save(){
		$tag_radio_str = str_replace('，', ',', I('post.tag_radio'));
		$tag_checkbox_str = str_replace('，', ',', I('post.tag_checkbox'));
		
		$tag_radio_array = array_unique(explode(',', $tag_radio_str));
		$tag_checkbox_array = array_unique(explode(',', $tag_checkbox_str));
		
		$data['tag_radio'] = implode(',', array_filter($tag_radio_array));
		$data['tag_checkbox'] = implode(',', array_filter($tag_checkbox_array));
	
		if(is_numeric(M('Tag',C('DB_PREFIX_C'))->where('module = "'.I('post.module').'"')->save($data))){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
	/**
	 * 获取标签列表
	 * @param string $module
	 */
	public static function getTagList($module){
		$tag = M('Tag',C('DB_PREFIX_C'))->where('module = "System" OR module = "'.$module.'"')->select();
		$data['tag_radio'] = array_filter(explode(',',$tag[0]['tag_radio'].','.$tag[1]['tag_radio']));
		$data['tag_checkbox'] = array_filter(explode(',',$tag[0]['tag_checkbox'].','.$tag[1]['tag_checkbox']));
		return $data;
	}
}