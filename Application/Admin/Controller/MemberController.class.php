<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class MemberController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
		
	}
	/**
	 * 用户列表
	 */
	public function memberList(){
// 		rank 用户等级 0 未激活 1 消费商 2 合格的消费商 3 合作商 4 特殊的合作商
		$Member = D('Member');
		$rank = I('get.rank',0);
		if(I('get.seller',0)){
			$content_header = array('商家列表');
			$condition['store_id'] = array('gt',0);
		}else{
			$content_header = array('所有会员 ','消费商','合格的消费商 ','合作商','特殊的合作商');
			$this->assign('content_header',$content_header[$rank]);
		}
		if(!empty($rank)){
			$condition['rank'] = $rank;
		}
		if(!empty(I('get.agent_level'))){
			$condition['agent_level'] = I('get.agent_level');
			$content_header = array('','县代理','市代理 ','省代理');
			$this->assign('content_header',$content_header[$condition['agent_level']]);
		}
		if(!empty(I('get.user_status'))){
			$condition['user_status'] = I('get.user_status');
		}
		$data = page($Member, $condition,20,'relation','uid desc');
		$this->assign('member_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('rank',$rank);
		$this->display('Member/member_list');
		
	}
	public function realNameAUTH(){
		$condition['real_name_auth'] = 2;
		$data = page(M('Member'), $condition,20,'','uid desc');
		$this->assign('member_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display('real_name_auth');
	}
	public function rauth(){
		if(M('Member')->where('uid = '.I('get.uid'))->setField('real_name_auth',I('get.real_name_auth'))){
			$this->success('更新成功');
		}else{
			$this->error('更新失败');
		}
	}
	
	public function view(){
		$this->display();
	}
	/**
	 * 编辑
	 */
	public function info(){
		$member_info = D('Member')->relation('member_account')->where('uid = '.I('get.uid'))->find();
		$this->assign ('mi',$member_info);
		$this->assign ('bank_list', C('BANK_LIST'));
		$this->display ();
	}
	/**
	 * 添加会员
	 */
	public function checkMobile(){
		$count = M('Member')->where('mobile = "'.I('get.mobile').'"')->count();
		if($count>0){
			$this->ajaxReturn(array('status'=>0,'info'=>'手机号不能重复'));
		}
	}
	public function register(){
		$username = 'u'.I('post.mobile');
		$password = I('post.password');
		$mobile = I('post.mobile');
		$rank = I('post.rank');
		//6位随机数
		$salt = randstr(6);
		$data=array(
				"username"=>$username,
				'password' =>md5(md5($password).$salt),
				'mobile' =>$mobile,
				'referrer_id' =>0,
				'referrer_node_id'=>0,
				'position'=>'left',
				'salt' =>$salt,
				'rank'=>$rank,
				'register_time'=>time(),
				'token'=>md5(md5(time()).$salt),
				'member_account'=>array(
						'YJT_FEE'=>0,
						'YJT_GIVE'=>0,
						'YJT_FEEZE'=>0,
						'GWQ_FEE'=>0,
						'BDB_FEE'=>0,
						'DZB_FEE'=>0,
						'ZCB_FEE'=>0,
						'JF_SUM'=>0,
						'JF_FEE'=>0,
						'JF_FEEZE'=>0,
				),
		);
		$relation[] = 'member_account';
		if($rank == 4){
			$data['member_node'] = array('node_id'=>null);
			$relation[] = 'member_node';
		}
		if(D('Member')->relation($relation)->add($data)){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	
	/**
	 * 更新用户信息
	 */	
	public function update(){
		$data = array(
				'uid'=>I('post.uid'),
				'email'=>I('post.email'),
				'member_account'=>array(
						'bank_id'=>I('post.bank_id'),
						'bank_num'=>I('post.bank_num'),
						'account_name'=>I('post.account_name'),
				),
		);
	
		if(I('post.password')){
			$data['salt'] = createNonceStr(6);
			$data['password'] = md5(md5(I('post.password')).$data['salt']);
		}
		if(I('post.password_2')){
			$data['password_2'] = md5(I('post.password_2'));
		}
		if(is_numeric(D('Member')->relation('member_account')->save($data))){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
	
	
	/**
	 * 删除用户
	 */
// 	public function delete(){
// 		$Member = M('Member');
// 		if($Member->where('uid = '.I('get.uid'))->delete()){
// 			$this->success('删除成功');
// 		}else{
// 			$this->error('删除失败');
// 		}
// 	}
	/**
	 * 修改用户状态
	 */
// 	public function updateUserStatus(){
// 		if(M('Member')->where('uid = '.I('get.uid'))->setField('user_status',I('get.user_status'))){
// 			$this->success('更新成功');
// 		}else{
// 			$this->error('更新失败');
// 		}
// 	}
	
	/**
	 * 推广用户列表
	 */
// 	public function inviteList(){
// 		$Member = D('Member');
// 		$condition['invite_id'] = I('get.invite_id');
// 		$data = page($Member, $condition,20,'relation','uid desc');
// 		$this->assign('member_list',$data['list']);
// 		$this->assign('page',$data['page']);
// 		$this->display('Member/member_list');
// 	}
	/**
	 * 佣金流水
	 */
	public function runningAccount(){
		$condition['uid'] = I('get.uid');
		$data = page(M('MemberRunningAccount'), $condition,20,'','add_time desc');
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display('Member/running_account');
	}
	

	public function agentApply(){
		$condition['status'] = I('get.status',2);
		$data = page(M('ApplyAgent'), $condition,20);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('status',$condition['status']);
		$this->display('agent_apply');
	}
	
}