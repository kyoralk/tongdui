<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;

class NotifyController extends InitController{
	protected $config;
	private $uid;
	private $order_sn;
	/**
	 * 通知前置操作,获取支付宝配置信息
	 */
	 public function _before_alipay(){
	 	$this->config = $this->payConfig('alipay');	
	 }
	 /**
	  * 通知前置操作,获取微信配置信息
	  */
	 public function _before_wxpay(){
	 	$this->config = $this->payConfig('wxpay');
	 }
	 /**
	  * 通知前置操作,获取银联配置信息
	  */
	 public function _before_unionpay(){
	 	$this->config = $this->payConfig('unionpay');
	 }
	 /**
	  * 通知前置操作,对客户端信息进行验签
	  */
	 public function _before_client(){
	 	if(!$this->verifySign(array('token'=>I('post.token'),'out_trade_no'=>I('post.out_trade_no')), I('post.sina'))){
	 		jsonReturn('','01023');
	 	}
	 }
	/**
	 * 支付宝通知
	 */
	public function alipay(){
		echo 'success';
	}
	/**
	 * 微信通知
	 */
	public function wxpay(){
		echo 'success';
	}
	/**
	 * 银联通知
	 */
	public function unionpay(){
		echo 'success';
	}
	/**
	 * 客户端通知
	 */
	public function client(){
		if($this->shuntByNo(I('post.out_trade_no'))){
			jsonReturn();
		}
	}
	/**
	 * 根据订单号分流操作
	 */
	private function shuntByNo($out_trade_no){
		$trade = explode('_', $out_trade_no);
		switch ($trade[0]){
			case 'REC':
				$this->recharge($trade[1]);
				break;
			case 'BUY':
				$this->buy($trade[1]);
				break;
			case 'UPG':
				$this->upgrade($trade[1]);
				break;
		}
		$this->checkGive();
		return true;
	}
	/**
	 * 充值
	 * @param string $out_trade_no
	 */
	private function recharge($out_trade_no){
		$Log = M('MemberAccountLog',C('DB_PREFIX_C'));
		$log_info = $Log->where('out_trade_no = "'.$out_trade_no.'"')->field('trade_code,trade_fee,uid,trade_status')->find();
		if($log_info['trade_status']==0){
			$M = M();
			$M->startTrans();
			try {
				$Log->where('out_trade_no = "'.$out_trade_no.'"')->setField('trade_status',1);
				M('MemberAccount',C('DB_PREFIX_C'))->where('uid = '.$log_info['uid'])->save(array($log_info['trade_code'].'_FEE'=>array('exp',$log_info['trade_code'].'_FEE'.'+'.$log_info['trade_fee'])));
			} catch (Exception $e) {
				$M->rollback();
				jsonReturn('','00000');
			}
			$M->commit();
			$this->setMemberInfo(array('uid'=>$log_info['uid']));
			if($log_info['trade_code'] == 'GWQ'){
				R('Reward/giveGWQ',array($log_info['trade_fee']));//充值购物券赠送购物券
				R('Upgrade/hgxfs');//升级合格消费商
				R('Reward/jdjs',array($log_info['trade_fee'],'CZGWQ'));//充值购物券送一卷通
				R('Reward/heijin',array($log_info['trade_fee'],'CZ'));//赠送黑金
			}
		}
		
	}
	/**
	 * 购物
	 * @param string $out_trade_no
	 */
	private function buy($out_trade_no){
		
		$PayTemporary = M('PayTemporary',C('DB_PREFIX_MALL'));
		$res = $PayTemporary->where('status = 0 AND out_trade_no = "'.$out_trade_no.'"')->find();
		if($res){
			$order_sn = explode(',', $res['order_sn']);
			if(count($order_sn)>1){
				$condition['order_sn'] = array('in',$order_sn);
			}else {
				$condition['order_sn'] = $order_sn[0];
			}
			$condition['pay_status'] = 0;
			$Order = M('OrderInfo',C('DB_PREFIX_MALL'));
			//查询符合条件的订单支付状态，并且判断，防止重复通知
			$list = $Order->where($condition)->field('order_sn,uid')->select();
			$this->order_sn = array_column($list, 'order_sn');
			$this->setMemberInfo(array('uid'=>$list[0]['uid']));
			if($res['yjt']>0){
				AccountController::change($list[0]['uid'], $res['yjt'], 'YJT', 3,true);//减少消费的一卷通
			}
			
			if($res['gwq']>0){
				AccountController::change($list[0]['uid'], $res['gwq'], 'GWQ', 3,true);//减少消费的购物券
			}
			
			if(!empty($this->order_sn)){
				$condition['order_sn'] = array('in',$this->order_sn);
				$Order->where($condition)->setField('pay_status',1);
				if($res['gwq']>0){ // 购物赠送购物券
					R('Reward/sendGWQ', array($res['send_gwj'], $res['order_sn']));
				}
				R('Upgrade/hgxfs',array(true));//升级合格消费商
				if($res['yjt']>0){
					R('Reward/jdjs',array($res['yjt'],'XFYJT'));//消费一卷通送一卷通
					R('Reward/heijin',array($res['yjt'],'XF'));//赠送黑金
				}
			}
			$PayTemporary->where('out_trade_no = "'.$out_trade_no.'"')->setField('status',1);
			
		}
		
	}
	/**
	 * 升级
	 * @param string $out_trade_no
	 */
	private function upgrade($out_trade_no){
		$this->order_sn = M('PayTemporary',C('DB_PREFIX_MALL'))->where('out_trade_no = "'.$out_trade_no.'"')->getField('order_sn');
		$Order = M('OrderInfo',C('DB_PREFIX_MALL'));
		//查询符合条件的订单支付状态，并且判断，防止重复通知
		$order_info = $Order->where('order_sn = "'.$this->order_sn.'"')->field('order_sn,pay_status,uid')->find();
		if(!empty($order_info)){
			$this->setMemberInfo(array('uid'=>$order_info['uid']));
			if($order_info['pay_status'] != 1){
				R('Upgrade/hzs',array($this->order_sn));//升级合作商

				$Order->where('order_sn = "'.$this->order_sn.'"')->setField('pay_status',1);
				
			}
		}
		
	}
	/**
	 * 检测赠送
	 */
	private function checkGive(){
		if(!empty($this->order_sn)){
			if(is_array($this->order_sn)){
				$condition['order_sn'] = array('in',$this->order_sn);
			}else{
				$condition['order_sn'] = $this->order_sn;
			}
			$order_goods = M('OrderGoods',C('DB_PREFIX_MALL'))->where($condition)->field('goods_id,spec_id,prosum')->select();
			if(!empty($order_goods)){
				$raw_data = M('Goods',C('DB_PREFIX_MALL'))->where(array('goods_id'=>array('in',array_column($order_goods, 'goods_id'))))->field('goods_name,give_integral,consumption_type,shop_price')->select();
				if(!empty($raw_data)){
					$integral= array_column($raw_data,'give_integral');//积分
					$goods_name = array_column($raw_data,'goods_name');//商品名称
					$gwj = array();//购物券
					foreach ($raw_data as $item){
						//如果是一卷通消费
						if($item['consumption_type'] == 2 ){
							$gwq_fee = $item['shop_price'] * $bi;
							$gwj['uid'][] = $this->member_info['uid'];
							$gwj['fee'][] = $item['shop_price'];
							$gwj['desc'][] = '购买'.$item['goods_name'].'赠送'.$gwq_fee.'购物券';
						}
					}
					if(!empty($gwj)){
						AccountController::changeALL($gwj['uid'], $gwj['fee'], 'GWQ', 4, $desc, $reward_code);
					}
					if($integral){
						R('Reward/integral',array($integral,$goods_name));//把积分分配成增值积分和资产包
					}
					R('Reward/agent',array($order_goods));//代理商奖励
					R('Love/grantgoods',array($condition));//捐产品
				}
				
			}

		}
	}
}