<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use General\Controller\GeneralController;

class AccountController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 黑金检测
	 */
	private function checkHeijin($uid,$trade_fee,$trade_code,$trade_type){
		//$trade_code = 'YJT' 表示当时发生交易的是一卷通
		//$trade_type = 4; 表示交易类型为赠送
		if($trade_code == 'YJT' && $trade_type == 4){
			return array('uid'=>$uid,'trade_fee'=>$trade_fee);
		}
	}
	/**
	 * 组装日志
	 * @param int $uid
	 * @param float $trade_fee
	 * @param string $trade_code
	 * @param int $trade_type
	 * @param number $proportion 资金兑换比例
	 */
	private function assemblyLog($uid,$trade_fee,$trade_code,$trade_type,$desc,$reward_code,$proportion){
		$code_array = array('YJT'=>'一卷通','GWQ'=>'购物券','BDB'=>'报单币','DZB'=>'电子币','ZCB'=>'资产包','JF'=>'积分','HJ'=>'黑金');
		$type_array = array('','充值','提现','消费','赠送','提现手续费','捐款','退款');
		$heijin_data = array();
		if(is_array($uid)){
			$data['bulk'] = 1;
			$i = 0;
			while (!empty($uid[$i])){
				if($trade_fee[$i] != 0){
					$data['log'][] = array(
							'out_trade_no'=>serialNumber(),
							'uid'=>$uid[$i],
							'trade_fee'=>$trade_fee[$i],
							'trade_code'=>$trade_code,
							'trade_type'=>$trade_type,
							'final_fee'=>$trade_fee[$i]*$proportion,
							'time_start'=>time(),
							'desc'=>empty($desc[$i]) ? $code_array[$trade_code].$type_array[$trade_type] : $desc[$i],
							'reward_code'=>$reward_code,
					);
					$them_heijin = AccountController::checkHeijin($uid[$i], $trade_fee[$i], $trade_code, $trade_type);
					if(!empty($them_heijin)){
						$heijin_data[] = $them_heijin;
					}
				}
				$i++;
			}
		}else{
			$data['bulk'] = 0;
			if($trade_fee != 0){
				$data['log'] = array(
						'out_trade_no'=>serialNumber(),
						'uid'=>$uid,
						'trade_fee'=>$trade_fee,
						'trade_code'=>$trade_code,
						'trade_type'=>$trade_type,
						'final_fee'=>$trade_fee*$proportion,
						'time_start'=>time(),
						'desc'=>empty($desc) ? $code_array[$trade_code].$type_array[$trade_type] : $desc,
				);
				$them_heijin = AccountController::checkHeijin($uid, $trade_fee, $trade_code, $trade_type);
				if(!empty($them_heijin)){
					$heijin_data[] = $them_heijin;
				}
			}
			
		}
		if(!empty($heijin_data)){
			R('Reward/heijinMore',array($heijin_data));//批量赠送黑金
		}
// 		writeTXT(var_export($data,true));
		return $data;
		
	} 
	/**
	 * 添加账户资金日志
	 * @param int $uid
	 * @param float $trade_fee
	 * @param string $trade_code
	 * @param int $trade_type
	 */
	private function addLog($uid, $trade_fee, $trade_code, $trade_type, $desc = '',$reward_code = '' ,$proportion = 1){
		$data = AccountController::assemblyLog($uid, $trade_fee, $trade_code, $trade_type, $desc,$reward_code,$proportion);
		if(!empty($data['log'])){
			if($data['bulk'] == 1){
				if(!M('MemberAccountLog',C('DB_PREFIX_C'))->addAll($data['log'])){
					E('日志写入失败');
				}
			}else{
				if(!M('MemberAccountLog',C('DB_PREFIX_C'))->add($data['log'])){
					E('日志写入失败');
				}else{
					return $data['log']['out_trade_no'];//预充值订单订单号
				}
			}
		}
	}
	
	/**
	 * 改变账户各种资金
	 * @param float $trade_fee
	 * @param string $trade_code
	 * @param int $trade_type
	 * @param boolen $minus 为true时 为减少
	 * @param string $desc 详情
	 */
	public static function change($uid,$trade_fee,$trade_code,$trade_type,$minus = false,$desc = '',$reward_code = '',$trans = true){
		if(is_array($trade_code)){
			$field =  $trade_code[0].$trade_code[1];
			$trade_code = $trade_code[0];
		}else{
			$field = $trade_code.'_FEE';
		}

		$Account = M('MemberAccount',C('DB_PREFIX_C'));
		if($minus){
			$fee = $Account->where('uid = '.$uid)->getField($field);

			if($fee-$trade_fee<0){
				jsonReturn('','01024');
			}
			$trade_fee = -$trade_fee;
		}

		if($trans){
			$M = M();
			$M->startTrans();
			try {
				$Account->where('uid = '.$uid)->setInc($field,$trade_fee);
				AccountController::addLog($uid, $trade_fee, $trade_code, $trade_type,$desc,$reward_code);
			} catch (Exception $e) {
				$M->rollback();
				return false;
			}
			$M->commit();
			return true;
		}else{
			$Account->where('uid = '.$uid)->setInc($field,$trade_fee);
			AccountController::addLog($uid, $trade_fee, $trade_code, $trade_type,$desc,$reward_code);
		}
		
	}
	/**
	 * 改变多个账户各种资金
	 * @param float $trade_fee
	 * @param string $trade_code
	 * @param int $trade_type
	 * @param boolen $minus 为true时 为减少
	 * @param string $desc 详情
	 */
	public static function changeALL($uid_array,$trade_fee_array,$trade_code,$trade_type,$desc,$reward_code,$minus = false){
		if(empty($uid_array)){
			return true;
		}
		if(is_array($trade_code)){
			$field =  $trade_code[0].$trade_code[1];
			$trade_code = $trade_code[0];
		}else{
			$field = $trade_code.'_FEE';
		}
		if($minus){
			foreach ($trade_fee_array as $trade_fee){
				$data[$field][] = $field.' -'.$trade_fee;
			}
		}else{
			foreach ($trade_fee_array as $trade_fee){
				$data[$field][] = $field.' +'.$trade_fee;
			}
		}
		$condition = array('uid'=>$uid_array);
		$M = M();
		$M->startTrans();
		try {
			GeneralController::saveAll('ms_common_member_account', $data, $condition,1);
			AccountController::addLog($uid_array, $trade_fee_array, $trade_code, $trade_type, $desc,$reward_code);
		} catch (Exception $e) {
			$M->rollback();
			return false;
		}
		$M->commit();
	}
		
	/**
	 * 冻结/解冻
	 * @param float $trade_fee
	 * @param string $trade_code
	 * @param boolen $unfreeze
	 */
	private function feeze($uid,$trade_fee,$trade_code,$unfreeze = false){
		$field_fee = $trade_code.'_FEE';
		$field_feeze = $trade_code.'_FEEZE';
		if($unfreeze){
			//解冻
			$data[$field_fee] = array('exp',$field_fee.'+'.$trade_fee);//增加可用数据
			$data[$field_feeze] = array('exp',$field_feeze.'-'.$trade_fee);//减少冻结数据
		}else{
			//冻结
			$data[$field_fee] = array('exp',$field_fee.'-'.$trade_fee);//减少可用数据
			$data[$field_feeze] = array('exp',$field_feeze.'+'.$trade_fee);//增加冻结数据
		}
		if(M('MemberAccount')->where('uid = '.$uid)->save($data)){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 转账
	 */
	public function transfer(){
		$trade_fee = I('post.trade_fee');
		$trade_code = I('post.trade_code');
		$to_username = I('post.to_username');
		$to_uid = M('Member')->where('username = '.$to_username)->getField('uid');
		$form_desc = '转账给'.$to_username.$trade_fee.'元';
		$to_desc = '收到'.$this->member_info['username'].$trade_fee.'元的转账';
		$field_fee = $trade_code.'_FEE';
		$Account = M('MemberAccount');
		$fee = $Account->where('uid = '.$this->member_info['uid'])->getField($field_fee);//from 的可用数据
		//判断余额是否够用
		if($fee-$trade_fee<0){
			jsonReturn('','01024');
		}
		$M = M();
		$M->startTrans();
		try {
			$Account->where('uid = '.$this->member_info['uid'])->setDec($field_fee,$trade_fee);//减少form
			$Account->where('uid = '.$to_uid)->setInc($field_fee,$trade_fee);//增加to
			$this->addLog($this->member_info['uid'],$trade_fee, $trade_code, $trade_type,$form_desc);
			$this->addLog($to_uid, $trade_fee, $trade_code, $trade_type,$to_desc);
		} catch (Exception $e) {
			$M->rollback();
			jsonReturn('','01032');
		}
		$M->commit();
		jsonReturn();
	}
	
	/**
	 * 预充值
	 */
	public function precharge(){
		$trade_code = I('post.trade_code');
		$rc = $this->ruleConfig(1);
		$proportion = $rc['CZ'][$trade_code];
		$out_trade_no = $this->addLog($this->member_info['uid'],I('post.trade_fee'), $trade_code, 1,'','',$proportion);
		$response = $this->notifyURL();
		if($out_trade_no){
			$response['out_trade_no'] = 'REC_'.$out_trade_no;
			jsonReturn($response);
		}else{
			$response['out_trade_no'] = '';
			jsonReturn($response,'00000');
		}
	}
	/**
	 * 账户详情
	 */
	public function info(){
		$info = M('MemberAccount')->where('uid = '.$this->member_info['uid'])->field(I('get.field','*'))->find();
		jsonReturn($info);
	}
	/**
	 * 提现
	 */
	public function withdraw(){
		$this->checkMemberInfo();//检测个人信息是否完整
		// 步骤：1、冻结提现金额。2、扣除手续费。3、审核通过，减少冻结并且打款，反之解除冻结返还手续费
		$rc = $this->ruleConfig(1);//配置信息
		$withdraw_type = I('post.withdraw_type');//提现类型
		$withdraw_money = I('post.withdraw_money')*$rc['TX'][$withdraw_type];//提现金额 按照人民币兑换币提现
		$withdraw_fee = $withdraw_money * $rc['TX']['TX_FEE']/100;//提现手续费
		$balance = M('MemberAccount')->where('uid = '.$this->member_info['uid'])->getField($withdraw_type.'_FEE');
		if($withdraw_money % $rc['TX']['TX_BASE'] != 0 || $withdraw_money < $rc['TX']['TX_MIN']){
			jsonReturn($rc['TX']['TX_MIN'].','.$rc['TX']['TX_BASE'],'01034');//提现金额不合法
		}
		if($balance-$withdraw_money-$withdraw_fee<0){
			jsonReturn('','01024');//余额不足
		}
		$M = M();
		try {
			$this->feeze($this->member_info['uid'], $withdraw_money, $withdraw_type);//冻结
			$this->addLog($this->member_info['uid'], $withdraw_money, $withdraw_type, 2);//添加冻结日志
			$this->change($this->member_info['uid'], $withdraw_fee, $withdraw_type, 5,true,'','',false);//扣除手续费
			$data = array(
					'apply_no'=>serialNumber(),
					'account_name'=>$this->member_info['real_name'],
					'bank_name'=>'支付宝',
					'bank_num'=>$this->member_info['alipay_id'],
					'withdraw_money'=>$withdraw_money,
					'withdraw_fee'=>$withdraw_fee,
					'withdraw_type'=>$withdraw_type,
					'uid'=>$this->member_info['uid'],
					'apply_time'=>time(),
			);
			M('ApplyWithdraw')->add($data);
		} catch (Exception $e) {
			$M->rollback();
			jsonReturn('','00000');
		}
		$M->commit();
		jsonReturn();
	}
	/**
	 * 提现记录
	 */
	public function wlog(){
		$withdraw_type = I('get.withdraw_type');
		$status = I('get.status');
		if($withdraw_type){
			$condition['withdraw_type'] = $withdraw_type;
		}
		if($status){
			$condition['status'] = $status;
		}
		$condition['uid'] = $this->member_info['uid'];
		$data = appPage(M('ApplyWithdraw'), $condition, I('get.num'), I('get.p'),'','apply_time desc');
		if(I('get.sum')){
			$data['fee_sum'] = M('ApplyWithdraw')->where($condition)->sum('withdraw_money');
		}
		jsonReturn($data);
	}
	/**
	 * 资金日志
	 */
	public function log(){
		$trade_code = I('get.trade_code');
		$trade_type = I('get.trade_type');
		if($trade_code){
			$condition['trade_code'] = $trade_code;
		}
		if($trade_type){
			$condition['trade_type'] = $trade_type;
		}
		$condition['uid'] = $this->member_info['uid'];
		$data = appPage(M('MemberAccountLog'), $condition, I('get.num'), I('get.p'),'','time_start desc');
		if(I('get.sum')){
			$data['yue'] = M('MemberAccount')->where('uid = '.$this->member_info['uid'])->getField($trade_code.'_FEE');
            $condition['trade_status'] = 1;
			$condition['trade_type'] = 1;//充值
			$zcz = M('MemberAccountLog')->where($condition)->sum('trade_fee');
			$condition['trade_type'] = 2;//提现
			$ztx = M('MemberAccountLog')->where($condition)->sum('trade_fee');
            $condition['trade_status'] = 0;
			$condition['trade_type'] = 4;//获得
			$zhd = M('MemberAccountLog')->where($condition)->sum('trade_fee');
			$data['zcz'] = empty($zcz) ? '0' : $zcz;
			$data['ztx'] = empty($ztx) ? '0' : $ztx;
			$data['zhd'] = empty($zhd) ? '0' : $zhd;
		}
		foreach($data["list"] as $key=>$val)
        {
            if($val['trade_type']==1 && $val['trade_status']==0)
            {
                $data["list"][$key]["trade_fee"]=$val["trade_fee"]."（充值失败）";

            }
            if($val['trade_type']==2)
            {
                $data["list"][$key]["trade_fee"]="-".$val["trade_fee"];//在提现上添加负号。
            }

        }
		jsonReturn($data);
	}
}