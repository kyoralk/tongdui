<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class AddressController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 获取地址列表
	 */
	private function region(){
		$region_list =  M('Region')->getField('id,name');
		return $region_list;
	}
	/**
	 * 地址列表
	 */
	public function addressList(){
		$region_list = $this->region();
		$address_list = M('MemberAddress')->where('uid = '.$this->member_info['uid'])->select();
		$count = count($address_list);
		for($i = 0;$i<$count;$i++){
			$address_list[$i]['province'] = $region_list[$address_list[$i]['province']];
			$address_list[$i]['city'] = $region_list[$address_list[$i]['city']];
			$address_list[$i]['district'] = $region_list[$address_list[$i]['district']];
		}
		jsonReturn($address_list);
	}
	/**
	 * 获取地址
	 * @param stirng $address_id
	 * @param number $get_name
	 * @param number $is_def
	 */
	public function getAddress($address_id,$get_name = 1,$is_def = 0){
		$region_list = $this->region();
		if($is_def == 1){
			$address = M('MemberAddress')->where('is_def = 1 AND uid = '.$this->member_info['uid'])->find();
		}else{
			$address = M('MemberAddress')->where('address_id = '.$address_id.' AND uid = '.$this->member_info['uid'])->find();
		}
		if($get_name == 1){
			$address['province'] = $region_list[$address['province']];
			$address['city'] = $region_list[$address['city']];
			$address['district'] = $region_list[$address['district']];
		}
		C('DB_PREFIX',C('DB_PREFIX_MALL'));
		return $address;
	}
	/**
	 * 获取一个收货地址
	 */
	public function getOne() {
		$address = $this->getAddress(I('get.address_id'),I('get.get_name'),I('get.is_def',0));
		jsonReturn($address);
	}
	/**
	 * 添加/编辑地址
	 */
	public function save(){
		$data = array (
				'uid' => $this->member_info['uid'],
				'consignee' => I('post.consignee'),
				'is_def' => I('post.is_def',0),
				'address' => I('post.address'),
				'mobile' => I('post.mobile'),
		);
		if(!empty(I('post.province'))){
			$data['province'] = I('post.province');
		}
		if(!empty(I('post.city'))){
			$data['city'] = I('post.city');
		}
		if(!empty(I('post.district'))){
			$data['district'] = I('post.district');
		}
		$Address = M('MemberAddress');
		if($data['is_def'] == 1){
			$Address->where('uid = '.$this->member_info['uid'])->setField('is_def',0);
		}
		$address_id = I('post.address_id');
		if(empty($address_id)){
			$res = M('MemberAddress')->add($data);
		}else{
			$res = M('MemberAddress')->where('address_id = '.$address_id)->save($data);
		}
		if($res){
			jsonReturn($res);
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 设置默认地址
	 */
	public function setDef(){
		try {
			$Address = M('MemberAddress');
			$Address->where('uid = '.$this->member_info['uid'])->setField('is_def',0);
			$Address->where('address_id = '.I('post.address_id').' AND uid = '.$this->member_info['uid'])->setField('is_def',1);
		} catch (Exception $e) {
			jsonReturn('','00000');
		}
		jsonReturn();
	}
	/**
	 * 删除地址
	 */
	public function del(){
		$address_id = I('post.address_id');
		$condition['address_id'] =array('in',explode(',', $address_id));
		$condition['uid'] = $this->member_info['uid'];
		if(M('MemberAddress')->where($condition)->delete()){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
}