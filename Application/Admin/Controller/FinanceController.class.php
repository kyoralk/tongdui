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

		if (I('get.order_sn')) {
		    $condition['oi.order_sn'] = I('get.order_sn');
		    $this->assign('order_sn', I('get.order_sn'));
        }

		$field = 'order_sn,settlement_no,bank_account_number,bank_account_name,order_time';
		$settlement_list = M('OrderInfo',C('DB_PREFIX_MALL'))->alias('oi')->join(C('DB_PREFIX_MALL').'store s on oi.store_id = s.store_id')->where($condition)->field($field)->select();

//        $data = page(M('OrderInfo',C('DB_PREFIX_MALL'))->alias('oi')->join(C('DB_PREFIX_MALL').'store s on oi.store_id = s.store_id'), $condition, 20,'view','','*');

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
       // return ['1'=>'未审核', '2'=>'通过', '3'=>'驳回', '4'=>'正在打款', '5'=>'已完成'];
        return ['1'=>'未审核', '2'=>'通过', '3'=>'驳回', '4'=>'已完成'];
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
			$condition['trade_type'] = [1,7, 'or'];

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
		if($recharge_sn > 2)
        {
            $status=0;
        }

		if ($status !== false) {

			$condition['trade_status'] = $status;
			if(!$recharge_sn){
			if($status==1)
            {
                $condition=array(
                    'trade_type'=>["gt",3],
                    '_complex'=>$condition,
                    '_logic'=>'or'
                );
            }
            }
            if(!$recharge_sn) {
                if ($status == 0) {
                    $condition['trade_type'] = ["lt", 4];

                }
            }

			$this->assign('trade_status', $status);
		}

		$data = page(M('MemberAccountLog'), $condition, 20,'view','time_start DESC','*');

		$this->assign('account_types', $this->account_type());
		$this->assign('reward_types', $this->reward_type());
		$this->assign('trade_types', $this->trade_types());
		$this->assign('trade_statuss', $this->trade_status());

		foreach ($data["list"] as $key=>$val)
        {
            if($val["trade_type"]>3)
            {
                $data["list"][$key]["trade_status"]=1;
            }
        }


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
    public function lp_account() {
        if (I('post.user_name')) {
            $user = M('Member')->where('username ="'.I('post.user_name').'"')->find();
            if (empty($user)) {
                $this->error('没有该会员',U('Member/memberList','rank='.I('get.rank')));
            } else {
                $amount = I('post.amount');
                $amount or $this->error('调整金额必须大于零');
                $type = I('post.type');
                $minus = (I('post.htype')==1)?true:false;

                AccountController::change($user['uid'], $amount, $type, 7, $minus, '平台调整操作');
                $this->success('操作成功',U('Member/memberList','rank='.I('get.rank')));
            }
        }else{
            $uid=I('get.uid');
            $user = M('Member')->where('uid ="'.$uid.'"')->find();
            $user or $this->error('没有该会员',U('Member/memberList','rank='.I('get.rank')));
            $this->assign('user_name',$user['username']);
            $this->assign('rank',I('get.rank'));
            $this->display('lp_account');
        }
    }

    public function love_list() {
        $gwj=M('Love', C("DB_PREFIX_MALL"))->where(["type"=>1])->field("sum(fee) as sum")->find()["sum"];
        $yjt=M('Love', C("DB_PREFIX_MALL"))->where(["type"=>2])->field("sum(fee) as sum")->find()["sum"];
        $this->assign("gwj",$gwj);
        $this->assign("yjt",$yjt);
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
        $type=I('get.type');
        if($type)
        {
            $condition['type']=$type;
            $this->assign('type', $type);
        }

        $data = page(M('Love', C("DB_PREFIX_MALL")), $condition, 20,'view','grant_time DESC','*');

        $this->assign('goods_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('love_list');
    }
    /**
     * 结算申请列表
     */
    public function apply_hand()
    {
        C('DB_PREFIX',C('DB_PREFIX_MALL'));
        $this->assign('status',99);
        if(I('get.status') && I('get.status') < 99)
        {
            $condition['status']=I('get.status');
            $this->assign('status',I('get.status'));
        }
        $this->assign('status',I('get.status'));
        if(I('get.time'))
        {
            $time=explode('~',I('get.time'));
            $condition['time']=[['egt',"$time[0]"],['elt',"$time[1]"]];
            $this->assign('time',I('get.time'));
        }
        if(I('get.username'))
        {
            $condition['st.username']=I('get.username');
            $this->assign('username',I('get.username'));
        }
        $data = page(D('ApplyView'), $condition, 20,'','id desc','*');
      //  echo D('ApplyView')->getLastSql();
        $total=M("order_info")->where(["receipt_status"=>1])->field('sum(settlement_total) as total,sum(settlement_no) as no,sum(settlement_already) as wei')->find();
        //待审批
        $hand=M('settlement')->where(['status'=>'0'])->field('sum(total) as total')->find();
        $hand['total'] or $hand['total']=0.00;
        //待打款
        $apply=M('settlement')->where(['status'=>'1'])->field('sum(total) as total')->find();
        $apply['total'] or $apply['total']=0.00;
        //未结算
        $no_apply=$total['no']-$hand['total'];

        $this->assign('total',$total['total']);
        $this->assign('is_hand',$total['wei']);
        $this->assign('hand',$hand['total']);
        $this->assign('no_apply',$no_apply);
        $this->assign('apply',$apply['total']);
        $this->assign('list',$data['list']);

        $this->assign('page',$data['page']);
        $this->display();
    }
    /**
     * 结算审批
     */
    public function check_apply()
    {
        $untreated=I("post.sq_money")-I("post.apply_total");
        M("settlement","ms_mall_")->where(['id'=>I("post.id")])->save(['apply_total'=>I("post.apply_total"),"status"=>"1","untreated"=>$untreated]);
        die(json_encode(['msgcode'=>0]));
    }

    /**
     * 结算驳回
     */
    public function bh_apply()
    {
        M("settlement","ms_mall_")->where(['id'=>I("post.id")])->save(["status"=>"2"]);
        die(json_encode(['msgcode'=>0]));
    }
    /**
     * 确认己打款
     */
    public  function dk_apply()
    {
        $data=M("settlement","ms_mall_")->where(['id'=>I("post.id")])->find();
        $total=$data['apply_total'];
        while($total)
        {
            $order=M("order_info","ms_mall_")->where(["store_id"=>$data['store_id'],"settlement_status"=>["neq",1],"settlement_no"=>["gt",0],"receipt_status"=>1])->field("order_sn,settlement_total,settlement_already,settlement_no")->find();
            if($order['settlement_no']>$total)
            {
                //部分结算
                $save["settlement_no"]=$order["settlement_no"]-$total;
                $save["settlement_already"]=$total+$order["settlement_already"];
                $save["settlement_status"]=2;
                M("order_info","ms_mall_")->where(['order_sn'=>$order["order_sn"]])->save($save);
                $total=0;

            }else{
                //全部结算
                $save["settlement_no"]=0;
                $save["settlement_already"]=$order["settlement_already"]+$order["settlement_no"];
                $save["settlement_status"]=1;
                M("order_info","ms_mall_")->where(['order_sn'=>$order["order_sn"]])->save($save);
                $total-=$order['settlement_no'];
            }

        }

        M("settlement","ms_mall_")->where(['id'=>I("post.id")])->save(["status"=>"3"]);
        die(json_encode(['msgcode'=>0]));
    }
    /**
     * 设置为己打款 2017-04-14 liaopeng
     */
    public function finished()
    {
        if(M("apply_withdraw","ms_common_")->where(["apply_no"=>$_GET["apply_no"]])->save(["status"=>4]))
        {
            $this->success("设置成功",U("Finance/withdrawlist"));
        }else{
            $this->error("设置失败",U("Finance/withdrawlist"));
        }
    }
}