<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use General\Util\Image;
use General\Util\XFile;
class MemberController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
		
	}
	public function bindBank(){
		if(!$this->checkSMSCode(I('post.sms_code'))){
			jsonReturn('','01011');
		}
		if($this->member_info['real_name_auth']){
			if(M('Member')->where($this->member_info['uid'])->setField('alipay_id',I('post.alipay_id'))){
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}else{
			jsonReturn('','01035');
		}
		
		
		
	}
	/**
	 *  绑定手机
	 *  当type为UNBIND为解绑
	 */
	public function bindMobile(){
		if(!$this->checkVerify(I('get.verify_code'))){
			jsonReturn('','01014');
		}
		if(I('get.type') == 'UNBIND'){
			$mobile = $this->member_info['mobile'];
			if(empty($mobile)){
				jsonReturn('','01010');
			}
		}else{
			$mobile = I('get.mobile');
			$this->checkMobile($mobile,true);
		}
		if($this->sendSMS($mobile, 0)){
			jsonReturn();
		}else{
			jsonReturn('','01012');
		}
	}
	/**
	 * 重置手机
	 */
	public function resetMobile(){
		if(!$this->checkSMSCode(I('post.sms_code'))){
			jsonReturn('','01011');
		}
		$mobile = I('post.mobile','');
		if(!empty($mobile)){
			if($this->isMobileNum($mobile)){
				jsonReturn('','01015');
			}
		}
		if(M('Member')->where('uid = '.$this->member_info['uid'])->setField('mobile',$mobile)){
			jsonReturn();
		}else{
			jsonReturn('','01016');
		}
	}
	/**
	 * 上传头像
	 */
	public function uploadHeadPhoto(){
		try {
            file_put_contents('param_get', print_r($_GET, true), FILE_APPEND);
            file_put_contents('param_post', print_r($_POST['type'], true), FILE_APPEND);
			switch (I('post.type')){
				case 'BASE64':
					$res = Image::createImg(array(I('post.head_photo')),'MEMBER');
                    file_put_contents('base_log', print_r($res, true));
					$head_photo = $res[0];
					break;
				default:
				    file_put_contents('upload_log', print_r($_FILES, true), FILE_APPEND);
					$res = Image::upload('head_photo', 'MEMBER');
					$head_photo = $res['savename'];
			}
			M('Member')->where('uid = '.$this->member_info['uid'])->setField('head_photo',$head_photo);
		} catch (Exception $e) {
			jsonReturn('','01017');
		}
		jsonReturn();
	}
	/**
	 * 修改密码
	 */
// 	public function updatePassword(){
// 		if(md5(md5(I('post.old_password')).$this->member_info['salt']) == $this->member_info['password']){
// 			$salt = randstr(6);
// 			$data['password']=md5(md5(I('post.password')).$salt);
// 			$data['salt']=$salt;
// 			if(M('Member')->where('uid = '.$this->member_info['uid'])->save($data)){
// 				jsonReturn('','修改成功',1);
// 			}else{
// 				jsonReturn('','修改失败',0);
// 			}
// 		}else{
// 			jsonReturn('','原始密码不正确',2);
// 		}
// 	}
	public function resetPassword(){
		if(I('get.step')==1){
			if(!$this->checkVerify(I('get.verify_code'))){
				jsonReturn('','01014');
			}
			$mobile = $this->member_info['mobile'];
			if(empty($mobile)){
				jsonReturn('','01010');
			}
			if($this->sendSMS($mobile, 0)){
				jsonReturn();
			}else{
				jsonReturn('','01012');
			}
		}else{
			if($this->checkSMSCode(I('post.sms_code'))){
				jsonReturn('','01011');
			}
			$password = empty(I('post.password')) ? false : I('post.password');
			$password_2 = empty(I('post.password_2')) ? false : I('post.password_2');
			$password_3 = empty(I('post.password_3')) ? false : I('post.password_3');
			if($password){
				$data['salt'] = randstr(6);
				$data['password'] = md5(md5($password).$data['salt']);
			}
			if($password_2){
				$data['password_2'] = md5($password_2);
			}
			if(password_3){
				$data['password_3'] = md5($password_3);
			}
			if(is_numeric(M('Member')->where('uid = '.$this->member_info['uid'])->save($data))){
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}
	}
	/**
	 * 实名认证
	 */
	public function certification(){
		$data = array(
				'real_name'=>I('post.real_name'),
				'card_id'=>I('post.card_id'),
				'residence'=>I('post.residence'),
		);
		try {
			switch (I('post.type')){
				case 'BASE64':
					$res = Image::createImg(array(I('post.card_img_1'),I('post.card_img_2')),'MEMBER');
					$data['card_img_1'] = $res[0];
					$data['card_img_2'] = $res[1];
					break;
				default:
					$res = Image::upload('card_img_1', 'MEMBER');
					$data['card_img_1'] = $res['savename'];
					$res = Image::upload('card_img_2', 'MEMBER');
					$data['card_img_2'] = $res['savename'];
					
			}
		} catch (Exception $e) {
			jsonReturn('','01017');
		}
		 
		$data['real_name_auth'] = 2;
		if(M('Member')->where('uid = '.$this->member_info['uid'])->save($data)){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	
	/**
	 * 推广码
	 */
	public function marketCode(){
		$res = Image::qrcode(urlencode('http://tongdui.hulianwangdai.com/Mobile/Public/marketParams/token/'.$this->member_info['token']),10);
	}
	public function promoCode(){
		$node_id = M('MemberNode')->where('uid = '.$this->member_info['uid'])->getField('node_id');
		$data = array(
				'referrer_id'=>$this->member_info['uid'],
				'referrer_node_id'=>empty($node_id) ? 1 : $node_id,
				'position'=>$node_id == 1 ? 'left' : empty(I('get.position'))? 'left' : I('get.position'),
		);
// 		dump(urlencode($data));
		$res = Image::qrcode(json_encode($data),10);
	}
	private function applyStore($store_id,$agent_province,$agent_city,$agent_district){
		$data = array(
				'apply_no'=>serialNumber(),
				'store_id'=>$store_id,
				'referrer_id'=>$this->member_info['referrer_id'],
				'agent_province'=>$agent_province,
				'agent_city'=>$agent_city,
				'agent_district'=>$agent_district,
				'status'=>2,
		);
		M('ApplyStore')->add($data);
	}
	/**
	 * 商家入驻
	 */
	public function join(){
		$store = M('Store', C('DB_PREFIX_MALL'))->where(array('uid' => $this->member_info['uid']))->order('store_id ASC')->find();
		if ($store['store_id']){
			if ($store['store_status'] == 1){
				jsonReturn('', '01036');
			} elseif ($store['store_status'] == 2) {
				jsonReturn('', '01037');
			} elseif ($store['store_status'] == 0) {
				jsonReturn('', '01038');
			}
		}
		$data=array(
				'store_name'=>I('post.store_name'),
				'username'=>$this->member_info['username'],
				'uid'=>$this->member_info['uid'],
				'company_name'=>I('post.company_name'),
				'company_province'=>I('post.company_province'),
				'company_city'=>I('post.company_city'),
				'company_district'=>I('post.company_district'),
				'company_location'=>I('post.company_location'),
				'company_address'=>I('post.company_address'),
				'company_phone'=>I('post.company_phone'),
				'employees_count'=>I('post.employees_count'),
				'registered_capital'=>I('post.registered_capital'),
				'contacts_name'=>I('post.contacts_name'),
				'contacts_phone'=>I('post.contacts_phone'),
				'contacts_email'=>I('post.contacts_email'),
				'business_licence_number'=>I('post.business_licence_number'),
				'business_scope'=>I('post.business_scope'),
				'join_time'=>time(),
				//'sc_id'=>$_POST['sc_id'],
		);
		$res = Image::upload('business_licence_pic', 'MEMBER');
		$data['business_licence_pic'] = $res['savename'];
		$store_id = M('Store',C('DB_PREFIX_MALL'))->add($data);
		if($store_id){
			$this->applyStore($store_id, $data['company_province'], $data['companyt_city'], $data['company_district']);
			M('Member',C('DB_PREFIX_C'))->where('uid = '.$this->member_info['uid'])->setField('store_id',$store_id);
			session('store_id',$store_id);
			$seller_path = './Uploads/Mall/Seller/'.$store_id;
			XFile::createDir($seller_path);
			XFile::createDir($seller_path.'/Content');
			XFile::createDir($seller_path.'/Goods');
			XFile::createDir($seller_path.'/Goods/Thumb');
			XFile::createDir($seller_path.'/Store');
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 申请代理
	 */
	public function applyAgent(){
		$data = array(
				'apply_no'=>serialNumber(),
				'uid'=>$this->member_info['uid'],
				'username'=>$this->member_info['username'],
				'mobile'=>empty(I('post.mobile')) ? $this->member_info['mobile'] : I('post.mobile'),
				'referrer_id'=>$this->member_info['referrer_id'],
				'apply_level'=>I('post.apply_level'),
				'agent_province'=>I('post.agent_province'),
				'agent_city'=>I('post.agent_city'),
				'agent_district'=>I('post.agent_district'),
				'address'=>I('post.address'),
				'apply_time'=>time(),
				'status'=>2,
		);
		if(empty($data['mobile'])){
			jsonReturn('','01033');
		}
		if(M('ApplyAgent')->add($data)){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}

	/**
	* 计算和谐兄弟，直接推荐的数量
	*
	*/	
	public function countBrother() {
		$response['count'] = M('Member')->where('referrer_id ='.  $this->member_info['uid'])
		->count();

		jsonReturn($response);
	}

	public $countFamilyNums = 0;

	/**
	* 计算和谐家人的数量
	*
	*/
	public function countFamily() {
		$node =  M('MemberNode')->where('uid ='. $this->member_info['uid'])->find();

		if ($node) {
			$position = $this->member_info['position']?$this->member_info['position']:'left'; 
			$root = $this->getTop($node['node_id']); 
			$this->preorder($root['node_id']);	
		}
		
		jsonReturn(['count' => $this->countFamilyNums]);
	}


	//前序遍历，访问根节点->遍历子左树->遍历右左树
	private function preorder($root){
	    $stack = array();
	    array_push($stack, $root);
	    while(!empty($stack)){
	        $center_node = array_pop($stack);
	        // echo $center_node.' ';
	        $this->countFamilyNums++;
	        $centerModel = M('MemberNode')->where('node_id ='.$center_node)->find();
	        if($centerModel['right_node_id'] != null) {
	        	array_push($stack, $centerModel['right_node_id']);	
	        } 
	        if($centerModel['left_node_id'] != null) {
	        	array_push($stack, $centerModel['left_node_id']);
	        }
	    }
	}
 
	private function getTop($node_id,$floor = 0){
		$Node = M('MemberNode',C('DB_PREFIX_C'));
		$top_list = array();
		$i = 1;
		do{
			$node_info = $Node->where('left_node_id = '.$node_id.' OR right_node_id = '.$node_id)->field('node_id,uid,lyj,ryj,star_level')->find();
			if(!empty($node_info)){
				$node_id = $node_info['node_id'];
				$top_list[] = array(
						'node_id' => $node_info['node_id'],
						'uid'=>$node_info['uid'],
						'lyj'=>$node_info['lyj'],
						'ryj'=>$node_info['ryj'],
						'star_level'=>$node_info['star_level'],
						'floor'=>$i,
				);
			}
			$i++;
			if($floor && $i > $floor){
				$node_info = null;
			}
		}while (!empty($node_info));
		return $top_list[0];
	}

    /**
     * 查询用户申请情况
     */
	public function info() {
	    $apply_agent = M('ApplyAgent')->where('uid ='.$this->member_info['uid'].' and (status = 1 or status = 2)' )->count();
	
	    $res = [
	        'real_name_auth' => $this->member_info['real_name_auth'],
            'store_id' => $this->member_info['store_id'],
            'applay_agent' => $apply_agent,
			'info'=> $this->member_info,
        ];

        jsonReturn($res);
    }
}