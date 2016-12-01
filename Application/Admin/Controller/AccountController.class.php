<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
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
	 * 多个用户赠送黑金
	 */
	public function heijinMore($data){
		$Pond = M('Pond',C('DB_PREFIX_MALL'));
		$Pond->startTrans();
		$HJ_TOTAL = $Pond->where('code = "HEIJIN"')->lock(true)->getField('total');
		if($HJ_TOTAL > 0){
			$general = new GeneralController();
			$rc = $general->ruleConfig(1);
			$rc = $rc['HJ'];
			$base_fee = $rc['FXZSYJT_H'];
			if(!empty($data)){
				$give_count = 0;
				$temp_data = array();
				foreach ($data as $item){
					$temp_hj = intval($item['trade_fee']/$base_fee);//要赠送的黑金数
					$temp_data[] = array(
							'uid'=>$item['uid'],
							'HJ'=>$temp_hj,
							'YJT'=>$item['trade_fee'],
					);
					$give_count += $temp_hj;
				}
	
				if($HJ_TOTAL - $give_count < 0){
					$give_count = $HJ_TOTAL;
				}
				try {
					//减少黑金
					$Pond->where('code = "HEIJIN"')->setDec('total',$give_count);
				} catch (Exception $e) {
					$Pond->rollback();
					exit();
				}
				$Pond->commit();
				$i = 0;
				while ($give_count>0){
						
					if($give_count - $temp_data[$i]['HJ'] < 0){
						$trade_fee = $give_count;
						$desc[] = '获得分享赠送'.$temp_data['YJT'].'一卷通，由于系统黑金达到上线，只能赠送'.$trade_fee.'黑金';
					}else{
						$trade_fee = $temp_data[$i]['HJ'];
						$desc[] = '获得分享赠送'.$temp_data['YJT'].'一卷通，赠送'.$trade_fee.'黑金';
					}
					$give_count -= $trade_fee;
					$condition['uid'][] = $temp_data[$i]['uid'];
					$hj_data['HJ'][] = $temp_data[$i]['HJ'];
					$i++;
				}
				AccountController::changeALL($condition['uid'], $hj_data['HJ'], 'HJ', 4, $desc, 'HJ');
			}
				
				
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
		$type_array = array('','充值','提现','消费','赠送','提现手续费');
		$heijin_data = array();
		if(is_array($uid)){
			$data['bulk'] = 1;
			$i = 0;
			while (!empty($uid[$i])){
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
				$heijin_data[] = AccountController::checkHeijin($uid[$i], $trade_fee[$i], $trade_code, $trade_type);
				$i++;
			}
		}else{
			$data['bulk'] = 0;
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
			$heijin_data[] = AccountController::checkHeijin($uid, $trade_fee, $trade_code, $trade_type);
		}
		AccountController::heijinMore($heijin_data);//批量赠送黑金
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
		if($data['bulk'] == 1){
			if(M('MemberAccountLog')->addAll($data['log'])){
				return true;
			}else{
				E('日志写入失败');
			}
		}else{
			if(M('MemberAccountLog')->add($data['log'])){
				return $data['log']['out_trade_no'];
			}else{
				E('日志写入失败');
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
		$Account = M('MemberAccount');
		if($minus){
			$fee = $Account->where('uid = '.$uid)->getField($field);
			if($fee-$trade_fee<0){
				jsonReturn('','01024');
			}
			$change_fee = -$trade_fee;
		}
		if($trans){
			$M = M();
			$M->startTrans();
			try {
				$Account->where('uid = '.$uid)->setInc($field,$change_fee);
				AccountController::addLog($uid, $trade_fee, $trade_code, $trade_type,$desc,$reward_code);
			} catch (Exception $e) {
				$M->rollback();
				return false;
			}
			$M->commit();
			return true;
		}else{
			$Account->where('uid = '.$uid)->setInc($field,$change_fee);
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
			GeneralController::saveAll('__MEMBER_ACCOUNT__', $data, $condition,1);
			AccountController::addLog($uid_array, $trade_fee_array, $trade_code, $trade_type, $desc,$reward_code);
		} catch (Exception $e) {
			$M->rollback();
			return false;
		}
		$M->commit();
		return true;
	
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
	 * 账户详情
	 */
	public function info(){
		$info = M('MemberAccount')->where('uid = '.$this->member_info['uid'])->field(I('get.field','*'))->find();
		jsonReturn($info);
	}
	/**
	 * 提现
	 */
	public function withdraw(){}

}