<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;

class AccountController extends CommonController{
	/**
	 * 账户详情
	 */
	public function info(){
		$account = M('Store')->where('store_id = '.session('store_id'))->field('bank_account_name,bank_account_number,bank_name')->find();
		$this->assign('account',$account);
		$this->display();
	}
	/**
	 * 保存账户信息
	 */
	public function save(){
		if(is_numeric(M('Store')->where('store_id = '.session('store_id'))->save(array('bank_account_name'=>I('post.bank_account_name'),'bank_account_number'=>I('post.bank_account_number'))))){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
	/**
	 * 结算中心
	 */
	public function accounts()
    {
        if(I("get.time"))
        {
            $time=explode('~',I("get.time"));
            $start=strtotime($time[0]);
            $end=strtotime($time[1]);
            $condition['order_time']=['between',"$start,$end"];
            $this->assign("time",I('get.time'));
        }
        if (I('get.order_sn')) {
            $condition['order_sn'] = I('get.order_sn');
            $this->assign('order_sn', I('get.order_sn'));
        }
        $condition['settlement_total']=['gt','0'];
        $condition['store_id']=session('store_id');
        $field = 'order_sn,order_time,settlement_total,settlement_no,settlement_already,settlement_status';
      //  page($obj,$condition,$num=10,$model='',$order='',$field='*',$group='',$count=0)
        $data = page(M("order_info"), $condition,10,'','order_sn desc',$field);
        $total=M("order_info")->where(['store_id'=>session("store_id")])->field('sum(settlement_total) as total,sum(settlement_no) as no,sum(settlement_already) as wei')->find();

        $is_hand=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'1'])->field("sum(total) as sum_money")->find();
        $finish=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'3'])->field("sum(apply_total) as sum_money")->find();
        $is_hand['sum_money'] or $is_hand['sum_money']=0;
        $this->assign('is_hand',$is_hand['sum_money']);
        $finish['sum_money'] or $finish['sum_money']=0;
        $this->assign('finish',$finish['sum_money']);

        $this->assign("store_id",session("store_id"));
        $in_hand=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'0'])->field("sum(total) as sum_money")->find();
        $in_hand['sum_money'] or $in_hand['sum_money']=0;
        $no_hand=$total['no']-$in_hand['sum_money']-$is_hand['sum_money'];

        $this->assign('in_hand',$in_hand['sum_money']);
        $this->assign('no_hand',$no_hand);
        $this->assign('total',$total);
        $this->assign('datalist',$data['list']);
        $this->assign('page',$data['page']);
        $this->display();
    }
    /**
     * 结算申请
     */
    public function apply()
    {
        if(I("get.time"))
        {
            $time=explode('~',I("get.time"));
            $condition['time']=[['egt',"$time[0]"],['elt',"$time[1]"]];
            $this->assign("time",I('get.time'));
        }
        $total=M("order_info")->where(['store_id'=>session("store_id")])->field('sum(settlement_total) as total,sum(settlement_no) as no,sum(settlement_already) as wei')->find();
        $this->assign("store_id",session("store_id"));
        $in_hand=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'0'])->field("sum(total) as sum_money")->find();
        $is_hand=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'1'])->field("sum(total) as sum_money")->find();
        $is_hand['sum_money'] or $is_hand['sum_money']=0;
        $finish=M('settlement')->where(['store_id'=>session("store_id"),'status'=>'3'])->field("sum(total) as sum_money")->find();
        $in_hand['sum_money'] or $in_hand['sum_money']=0;
        $no_hand=$total['no']-$in_hand['sum_money']-$is_hand['sum_money'];
        $this->assign('in_hand',$in_hand['sum_money']);
        $this->assign('no_hand',$no_hand);
        $this->assign('is_hand',$is_hand['sum_money']);
        $finish['sum_money'] or $finish['sum_money']=0;
        $this->assign('finish',$finish['sum_money']);
        $condition['store_id']=session("store_id");
        $data = page(M("settlement"), $condition,10,'','id desc',"*");
        $this->assign("list",$data['list']);
        $this->assign("page",$data['page']);
        $this->assign("total",$total);
        $this->display();
    }
    public function check_apply()
    {
        $store_info=M("store","ms_mall_")->where(["store_id"=>session("store_id")])->find();
        $store_info["bank_account_number"] or die(json_encode(['msgcode'=>1,"message"=>"请您绑定支付宝账号"]));
        $total=M("order_info")->where(['store_id'=>session("store_id")])->field('total,sum(settlement_no) as no')->find();
        $in_hand=M('settlement')->where(['store_id'=>session("store_id"),'status'=>0])->field("sum(total) as sum_money")->find();
        $no_hand=$total['no']-$in_hand['sum_money'];
        if(I("post.money")>$no_hand)
        {
            die(json_encode(['msgcode'=>1,"message"=>"金额超过您的未结算金额"]));
        }else{
            $data['time']=date("Y-m-d H:i:s");
            $data['total']=I("post.money");
            $data['store_id']=I("post.store_id");
            M("settlement")->add($data);
            die(json_encode(['msgcode'=>0]));
        }


    }
}