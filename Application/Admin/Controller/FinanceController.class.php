<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use General\Controller\AlipayController;

class FinanceController extends CommonController{

	private function store(){
		return M('Store',C('DB_PREFIX_MALL'))->where('store_id > 1')->field('store_id,store_name')->select();
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
        $content =  $Alipay->send($data, 'http://tongdui.hulianwangdai.com/withdraw_trans_notify-PHP-UTF-8/notify_url.php');
        $this->assign('content', $content);
        $this->display('pay');
    }


    /**
     * 充值列表
     */
    public function rechargelist() {

        $recharge_sn = I('get.recharge_sn');
        if ($recharge_sn) {
            $condition['recharge_sn'] = $recharge_sn;
            $this->assign('recharge_sn', $recharge_sn);
        }

        $recharge_num = I('get.recharge_num');
        if ($recharge_num) {
            $condition['recharge_num'] = $recharge_num;
            $this->assign('recharge_num', $recharge_num);
        }

        $user_name = I('get.recharge_name');
        if ($user_name) {
            $condition['recharge_name'] = $user_name;
            $this->assign('recharge_name', $user_name);
        }

        $data = page(M('ApplyRecharge'), $condition, 20,'view','apply_time DESC','*');

        $this->assign('goods_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('recharge_list');
    }
}