<?php
namespace Mobile\Controller;
use General\Controller\GeneralController;

class ReleaseController extends GeneralController{
	
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}	
	/**
	 * 释放
	 */
	public function todo(){
		$now_time = time();
		$M = M();
		M()->startTrans();
		try {
			$this->added($now_time);//未体现积分增值
			$res = $this->getRelease();//待释放积分
			if(!empty($res)){
				$trade_fee_array = $res['trade_fee_array'];
				$uid_array = $res['uid_array'];
				$this->saveAll('__RELEASE__', $data, $condition);//更新释放数据
				$this->addJF($restrade_fee_array, $uid_array);//增加积分
			}
		} catch (Exception $e) {
			$M->rollback();
			exit();
		}
		$M->commit();
	}
	/**
	 * 获取待释放数据
	 */
	private function getRelease($now_time){
		$seconds = 2592000;//一个月的秒数
		$where['not_release'] = array('gt',0);
		$where['release_time'] = array('elt',$now_time-2592000);
		$pr_release_list = M('Release')->where($where)->select();
		//组装释放数据
		if(!empty($pr_release_list)){
			foreach ($pr_release_list as $release){
				$release_fee = $release['integral'] * $release['release_proportion'];
				$condition['id'] = $release['id'];
				$data['not_release'][] = 'not_release - '.$release_fee;//减少未释放
				$data['already_release'][] = 'already_release + '.$release_fee;//增加已释放
				$data['release_time'][] = $now_time;
				$trade_fee_array[] = $release_fee;
				$uid_array = $release['uid'];
				
			}
			return array('data'=>$data,'condition'=>$condition,'trade_fee_array'=>$trade_fee_array,'uid_array'=>$uid_array);
		}else{
			return array();
		}
	}
	/**
	 * 积分增值
	 */
	private function added($now_time){
		$rc = $this->ruleConfig(5);
		$zzb = $rc[4]['JF']['zzb']/100;//增值比
		$zzys = $rc[4]['JF']['zzys'];//增值月数
		$where['add_time'] = array('elt',$now_time-2592000*$zzys);
		$uid_array = M('Release')->where($where)->field('uid')->group('uid')->select();
		$account = M('MemberAccount')->where(array('uid'=>array('in',array_column($uid_array, 'uid'))))->getField('uid,JF_FEE');
		if(!empty($account)){
			foreach ($account as $item){
				$trade_fee = $item['JF_FEE'] * $zzb;
				$data['JF_FEE'][] = 'JF_FEE + '.$trade_fee;
				$condition['uid'] = $item['uid'];
				$trade_fee_array[] = $trade_fee;
			}
			$this->saveAll('__MEMBER_ACCOUNT__', $data, $condition);
			$this->addLog($trade_fee_array, $condition['uid'],'未提现积分增值');
		}
	}
	/**
	 * 增加积分
	 * @param array $trade_fee_array
	 * @param array $uid_array
	 */
	private function addJF($trade_fee_array,$uid_array){
		foreach ($trade_fee_array as $trade_fee){
			$data['JF_FEE'][] = 'JF_FEE +'.$trade_fee;
		}
		$condition = array('uid'=>$uid_array);
		$this->saveAll('__RELEASE__', $data, $condition);//更新释放数据
		$this->addLog($trade_fee_array, $uid_array);
	}
	/**
	 * 添加日志
	 * @param array $trade_fee_array
	 * @param array $uid_array
	 */
	private function addLog($trade_fee_array,$uid_array,$desc = '释放增值积分'){
		$proportion = 1;
		$now_time = time();
		$i = 0;
		while (!empty($uid_array[$i])){
			$data[] = array(
					'out_trade_no'=>serialNumber(),
					'uid'=>$uid_array[$i],
					'trade_fee'=>$trade_fee_array[$i],
					'trade_code'=>'JF',
					'trade_type'=>4,
					'final_fee'=>$trade_fee[$i]*$proportion,
					'time_start'=>$now_time,
					'desc'=>$desc,
					'reward_code'=>'ZZJF',
			);
			$i++;
		}
		M('MemberAccountLog')->addAll($data);
	}
}