<?php
namespace Ml;
class MoneyLog {
	public $status = array(
			3 => '会员充值',
			4 => '充值失败',
			6 => '会员投标',
			7 => '流标返现',
			8 => '冻结资金解冻',
			9 => '复审通过，借款人收到资金',
			10 => '通过平台直接给房东付房款',
			11 => '付完房款,待收房租增加',
			12 => '收到借款人还款',
			13 => '偿还会员投资金额',
	);
	/**
	 * 
	 * @param unknown $uid 用户id
	 * @param unknown $type 操作类型
	 * @param unknown $amoney 变化资金
	 * @param string $info  变化说明
	 * @return boolean
	 */	
	public function log($uid, $type, $amoney, $status, $OrderNo = '', $info=''){
		if(empty($amoney)) return false;
		$money = M("CommonMemberAccount")->where('uid='.$uid)->find();
		$data['uid'] = $uid;
		$data['type'] = $type;
		$data['info'] = $info;
		$data['affect_money'] = $amoney;
		$data['momey'] = $money['commission'];
		$data['collect_money'] = $money['collect'];
		$data['freeze_money'] = $money['frozen'];
		$data['borrow_money'] = $money['borrow_money'];
		$data['OrderNo'] = $OrderNo;
		$data['status'] = $status;
		$data['dateline'] = time();
		$data['add_ip'] = get_client_ip();
		$result = M('CommonMemberAccountlog')->add($data);
		if ($result){
			return true;
		} else {
			return false;
		}
	}

// 	public function ReadMoneyLog($uid){
// 		$uinfo = M('common_member')->count($uid);
// 		if($uinfo == 1){
// 			$list = M('member_moneylog')->where("uid = {$uid}")->select();
// 			foreach($list as $k=>$v){
// 				$list[$k]['type'] = $this->Conversion($type);
// 			}
// 			return $list;
// 		} else {
// 			return false;
// 		}
		
// 	}
// 	public function Conversion($type) {
// 		if(isset($this->status[$type])) {
// 			return $this->status[$type];
// 		} else {
// 			return '未定义的操作类型('.$type.')';
// 		}
// 	}
}//类定义结束