<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use General\Controller\AlipayController;
use Admin\Controller\AccountController;

class FinanceController extends CommonController{

	private function store(){
		return M('Store',C('DB_PREFIX_MALL'))->where('store_id > 1 and store_status =1')->field('store_id,store_name')->select();
	}
	public function glist(){
		C('DB_PREFIX',C('DB_PREFIX_MALL'));
		$goods_name = I('get.goods_name');
		$goods_sn = I('get.goods_sn');
		$store_id = I('get.store_id');
		if(!empty($goods_name)){
			$condition['goods_name'] = array('like','%'.$goods_name.'%');
			$this->assign('goods_name',$goods_name);
		}
		if(!empty($goods_sn)){
			$condition['goods_sn'] = $goods_sn;
			$this->assign('goods_sn',$goods_sn);
		}
		if(!empty($store_id)){
			$condition['g.store_id'] = $store_id;
			$this->assign('store_id',$store_id);
		}else{
			$condition['g.store_id'] = array('gt',1);
			$this->assign('store_id',0);
		}
		$data = page(D('GoodsView'), $condition,20,'view',$order,'*');
		$this->assign('store_list',$this->store());
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display();
	}
	/**
	 * 发货明细
	 */
	public function deliveryDetail(){
		C('DB_PREFIX',C('DB_PREFIX_MALL'));
		$goods_name = I('get.goods_name');
		$order_sn = I('get.order_sn');
		$store_id = I('get.store_id');
		$goods_id = I('get.goods_id');
		if(!empty($goods_name)){
			$condition['goods_name'] = array('like','%'.$goods_name.'%');
			$this->assign('goods_name',$goods_name);
		}
		if(!empty($order_sn)){
			$condition['order_sn'] = $order_sn;
			$this->assign('order_sn',$order_sn);
		}
		if(!empty($store_id)){
			$condition['store_id'] = $store_id;
			$this->assign('store_id',$store_id);
		}else{
			$condition['store_id'] = array('gt',1);
			$this->assign('store_id',0);
		}
		if(!empty($goods_id)){
			$condition['goods_id'] = $goods_id;
		}
		$data = page(D('OrderView'), $condition,20,'view',$order,'order_sn,goods_name,cost_price,price,prosum,order_time',$group);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('store_list',$this->store());
		$this->display('delivery_detail');
	}
	/**
	 * 结算列表
	 */
	public function settlementList(){
		C('DB_PREFIX',C('DB_PREFIX_MALL'));
		$store_id = I('get.store_id');
		if(!empty($store_id)){
			$condition['store_id'] = $store_id;
			$this->assign('store_id',$store_id);
		}else{
			$condition['store_id'] = array('gt',1);
			$this->assign('store_id',0);
		}
		$store_name = I('get.store_name');

		if ($store_name) {

         //   $condition['store_name'] = I('get.store_name');
            $store = M('Store')->where('store_name like "%'.$store_name.'%"')->select();


            if ($store) {
                foreach ($store as $v)
                {
                    $store_id_list=$v['store_id'].',';
                }
                $store_id_list=trim($store_id_list,',');

                $store_id = $store['store_id'];
                $condition['store_id'] = array("in",$store_id_list);
                $this->assign('store_id', $store_id);
                $this->assign('store_name',$store_name);
            }
        }

		$data = page(D('SettlementView'), $condition,20,'view',$order,'store_id,store_name,bank_account_number,bank_account_name,sum(settlement_total) settlement_total,sum(settlement_already) settlement_already,sum(settlement_no) settlement_no,settlement_status,settlement_time','store_id');

		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('store_list',$this->store());
		$this->display('settlement_list');
	}
	/**
	 * 结算
	 */
	public function settlement(){
		$condition['oi.store_id'] = I('get.store_id');
		$condition['oi.settlement_status'] = 0;
		$field = 'order_sn,settlement_no,bank_account_number,bank_account_name';
		$settlement_list = M('OrderInfo',C('DB_PREFIX_MALL'))->alias('oi')->join(C('DB_PREFIX_MALL').'store s on oi.store_id = s.store_id')->where($condition)->field($field)->select();

		$order_sn = implode(',', array_column($settlement_list, 'order_sn'));
// 		$batch_fee = array_sum(array_column($settlement_list, 'settlement_no'));
		$batch_fee = 1;
		//$detail_data = serialNumber().'^'.$settlement_list['bank_account_number'].'^'.$settlement_list['bank_account_name'].'^'.$batch_fee.'^'.'商家结算';
		$detail_data = serialNumber().'^zhangbin11262126.com^张彬^'.$batch_fee.'^'.'商家结算';
		//必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
		$pay_date = date('Ymd',time());
		$batch_no =$pay_date.serialNumber();
		$data = array(
				'batch_no'=>$pay_date.serialNumber(),
				'pay_date'=>$pay_date,
				'order_sn'=>$order_sn,
				'batch_fee'=>$batch_fee,
				'batch_num'=>1,
				'detail_data'=>$detail_data,
		);
		$Alipay =  new AlipayController();
		$Alipay->send($data);
	}

	/*
	 * 结算明细
	 */
	public function settlement_detail() {
		$condition['oi.store_id'] = I('get.store_id');
		$condition['oi.settlement_status'] = 0;
		$store = M('MallStore', 'ms_')->where('store_id ='.$condition['oi.store_id'])->select();
		$this->assign('store', $store[0]);

		$field = 'order_sn,settlement_no,bank_account_number,bank_account_name,order_time';
		$settlement_list = M('OrderInfo',C('DB_PREFIX_MALL'))->alias('oi')->join(C('DB_PREFIX_MALL').'store s on oi.store_id = s.store_id')->where($condition)->field($field)->select();

        $data = page(M('OrderInfo',C('DB_PREFIX_MALL'))->alias('oi')->join(C('DB_PREFIX_MALL').'store s on oi.store_id = s.store_id'), $condition, 20,'view','','*');
        var_dump($data);exit;
		$this->assign('settlement_list', $settlement_list);

		$this->display('settlement_detail');
	}

    /**
     * 提现列表
     */
	public function withdrawlist() {

        $withdraw_type = I('get.withdraw_type');
        $status = I('get.status');
        $user_name = I('get.user_name');
        if ($withdraw_type){
            $condition['withdraw_type'] = $withdraw_type;
            $this->assign('withdraw_type',$withdraw_type);
        }
        if ($status){
            $condition['status'] = $status;
            $this->assign('status',$status);
        }
        if ($user_name) {
            $condition['account_name'] = $user_name;
            $this->assign('user_name', $user_name);
        }

        $data = page(M('ApplyWithdraw'), $condition, 20,'view','apply_time DESC','*');

        $this->assign('withdrawTypes', $this->withdraw_type());
        $this->assign('withdrawStatus', $this->withdraw_status());
        $this->assign('goods_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('withdraw_list');
    }

    private function withdraw_type(){
        return ['YJT'=>'一券通', 'JF'=>'积分'];
    }

    public static function withdraw_status() {
        return ['1'=>'未审核', '2'=>'通过', '3'=>'驳回', '4'=>'正在打款', '5'=>'已完成'];
    }

    /**
     * 提现详情
     */
    public function drawDetail() {
        $apply_no = I('get.apply_no');
        if ($apply_no) {
            $model = M('ApplyWithdraw')->where('apply_no ='.$apply_no)->find();
            $this->assign('model' , $model);
            $status = $this->withdraw_status();
            $status = $status[$model['status']];
            $this->assign('status', $status);
        }

        if (I('get.apply_no') && I('get.status')) {
            $model = M('ApplyWithdraw');
            if ($model->where('apply_no ='.$apply_no)->save($_GET)) {
                $this->redirect('Finance/withdrawlist');
            } else {
                echo '出错';
            }
        }

        $this->display('draw_detail');
    }

    /**
     * 个人提现取现
     *
     */
    public function withdrawPay() {

        $withdraw = M('ApplyWithdraw')->where('apply_no ="'.I('get.apply_no').'"')->find();
        if (empty($withdraw)) {
            echo json_encode( [ 'status'=>0,  'msg' =>'未获取提现订单'  ] );
        }
//        else
//        {
//            $withdraw2 = M('ApplyWithdraw');
//            $withdraw2->where('apply_no ="'.I('get.apply_no').'"')->save(['status'=>4]);
//        }

        $order_sn = $withdraw['apply_no'];
// 		$batch_fee = array_sum(array_column($settlement_list, 'settlement_no'));
        $batch_fee = $withdraw['withdraw_money']-$withdraw['withdraw_fee'];
        //$detail_data = serialNumber().'^'.$settlement_list['bank_account_number'].'^'.$settlement_list['bank_account_name'].'^'.$batch_fee.'^'.'商家结算';
        $detail_data = serialNumber().'^327993561@qq.com^杨帅^'.$batch_fee.'^'.'用户提现';
        //必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
        $pay_date = date('Ymd',time());

        $data = array(
            'batch_no'=>$withdraw['apply_no'].serialNumber(),
            'pay_date'=>$pay_date,
            'order_sn'=>$order_sn,
            'batch_fee'=>$batch_fee,
            'batch_num'=>1,
            'detail_data'=>$detail_data,
        );
        $Alipay =  new AlipayController();
        $content =  $Alipay->send($data, 'http://tongdui.yingrongkeji.com/withdraw_trans_notify-PHP-UTF-8/notify_url.php');
        $this->assign('content', $content);
        $this->display('pay');
    }


    /**
     * 充值列表
     */
    public function rechargelist() {

        
		// $recharge_sn = I('get.account_type');
		// if ($recharge_sn) {
			$condition['trade_type'] = 1;
			$this->assign('account_type', 1);
		// }

		// $recharge_num = I('get.reward_code');
		// if ($recharge_num) {
		// 	$condition['reward_code'] = $recharge_num;
		// 	$this->assign('reward_code', $recharge_num);
		// }

        $user_name = I('get.user_name');
        if ($user_name) {
            $member = M('Member')->where('username = "'.$user_name.'"')->find();
            if ($member) {
                $condition['uid'] = $member['uid'];
            }
            $this->assign('user_name', $user_name);
        }


		$status = I('get.trade_status');
        $status = ($status == -1)? false:$status;
		if ($status !== false) {
			$condition['trade_status'] = $status;
			$this->assign('trade_status', $status);
		} else {
            $this->assign('trade_status', -1);
        }


		$data = page(M('MemberAccountLog'), $condition, 20,'view','time_start DESC','*');
		$this->assign('account_types', $this->account_type());
		$this->assign('reward_types', $this->reward_type());
		$this->assign('trade_statuss', $this->trade_status());
		$this->assign('trade_types', $this->trade_types());
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display('_account_list');
        // $this->display('recharge_list');
    }

	public function accountlist() {

		$recharge_sn = I('get.account_type');
		if ($recharge_sn) {
			$condition['trade_type'] = $recharge_sn;
			$this->assign('account_type', $recharge_sn);
		}

		$recharge_num = I('get.reward_code');
		if ($recharge_num) {
			$condition['reward_code'] = $recharge_num;
			$this->assign('reward_code', $recharge_num);
		}
        $user_name=I("get.user_name");
		if($user_name)
        {
            $member['username'] = array('like','%'.$user_name.'%');
            $member_id=M("member")->field("uid")->where($member)->select();

            foreach ($member_id as $v)
            {
                $memberlist.=$v['uid'].',';
            }
            $memberlist=trim($memberlist,',');
            $condition['uid']=array("in",$memberlist);
            $this->assign('user_name', $user_name);
        }
        if ($recharge_num) {
            $condition['reward_code'] = $recharge_num;
            $this->assign('reward_code', $recharge_num);
        }
		$status = I('get.trade_status');

		if ($status !== false) {

			$condition['trade_status'] = $status;
			$this->assign('trade_status', $status);
		}

		$data = page(M('MemberAccountLog'), $condition, 20,'view','time_start DESC','*');
		$this->assign('account_types', $this->account_type());
		$this->assign('reward_types', $this->reward_type());
		$this->assign('trade_types', $this->trade_types());
		$this->assign('trade_statuss', $this->trade_status());
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display('account_list');
	}

	private function account_type(){
		return ['1'=>'充值', '2'=>'提现', '3'=>'消费', '4'=>'赠送', '5'=>'手续费', '6'=>'捐款'
		, '7'=>'系统'];
	}

	private function reward_type() {
		return ['DPJ'=>'对碰奖', 'JDJ'=>'见点奖', 'CXJ'=>'重消奖', 'ZZJF'=>'增值积分', 'HJ'=>'黑金', 'JMF'=>'加盟费', 'GHJ'=>'供货价'];
	}

	private function trade_status() {
		return ['1'=>'支付成功', '0'=>'未完成'];
	}

	private function trade_types() {
		return ['YJT'=>'一券通', 'GWQ'=>'购物券'];
	}

	

	// 账户变更
	public function account() {

		if (I('post.user_name')) {
			$user = M('Member')->where('username ="'.I('post.user_name').'"')->find();
			if (empty($user)) {
				$this->error('没有该会员');
			} else {
				$amount = I('post.amount');
				$type = I('post.type');
				$minus = (I('post.htype')==1)?true:false;

				AccountController::change($user['uid'], $amount, $type, 7, $minus, '平台调整操作');
				$this->success('操作成功');
			}
		}

		$this->display('account');
	}
}