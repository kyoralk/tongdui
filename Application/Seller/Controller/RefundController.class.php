<?php
namespace Seller\Controller;
use Mobile\Controller\AccountController;
use Seller\Controller\CommonController;
use Seller\Model\RefundModel;

class RefundController extends CommonController{

    public function index() {
        $content_header = '退换货列表';
        switch (I('get.type')){
            case 1:
                $condition ['type'] = '退货';
                $content_header = '退货列表';
                break;
            case 2:
                $condition ['type'] = '换货';
                $content_header = '换货列表';
                break;
        }

        $condition ['store_id'] = session('store_id');
        $data = page(D('Refund'), $condition,16,'','refund_id desc');
        //不修改MODEL下增加图片显示
        foreach($data["list"] as $key=>$val){
            $goods_ids.=$val["goods_id"].",";
        }
        if($goods_ids)
        {

            $goods_ids=rtrim($goods_ids,",");
            $img=M("goods_img","ms_mall_")->where("goods_id",["in",'"'.$goods_ids.'"'])->select();
            foreach($data["list"] as $k=>$v)
            {

                foreach($img as $iv)
                {
                    if($v["goods_id"]==$iv["goods_id"])
                    {
                        $data["list"][$key]["goods_img"]=$iv["save_path"].$iv["save_name"];

                    }
                }
            }
        }

        $this->assign('status_array', RefundModel::$status);
        $this->assign('order_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('content_header',$content_header);
        $this->display();
    }

    public function judge() {
        $content_header = '审核退换货';
        $model = M('Refund')->where('refund_id ='.I('get.refund_id'))->find();
        $goods = M('OrderGoods')->where('goods_id ='.$model['goods_id'].' and order_sn="'.$model['order_sn'].'"')->find();
        $img=M("goods_img","ms_mall_")->where("goods_id",$model["goods_id"])->find();
        if($img)
        {
            $goods["goods_img"]=$img["save_path"].$img["save_name"];
        }
        $this->assign('goods', $goods);
        $this->assign('model', $model);
        $this->assign('content_header',$content_header);
        $this->display();
    }

    public function dohuo() {
        $type = I('post.type');
        $where = 'refund_id ='.I('post.refund_id');
        $refund = M("Refund");
        $refund->sure_time = time();
        $refund->status = 1;
        $refund->where($where)->save();
        if ($type == "退货") {
            echo json_encode(['status'=>1]);
        } else {
            // 换货生成新订单
            $order = D('OrderInfo')->relation('order_goods')->where('order_sn='.I('post.order_sn'))->find();
            $data = $order;
            $data['order_sn'] = time().randstr(4,true);
            $data['total'] = 0;
            $data['yjt'] = 0;
            $data['gwq'] = 0;
            $data['settlement_total'] = 0;
            $data['integral'] = 0;
            $data['is_exchange'] = 1;

            if(D('OrderInfo')->relation('order_goods')->add($data)){
                $refund->status = 4;
                $refund->where($where)->save();
                echo json_encode(['status'=>1]);
            } else {
                echo json_encode(['status'=>2, 'content'=>'保存出错']);
            }
        }
    }

    /**
     * 退货换货不同意返回
     */
    public function ndohuo() {
        $type = I('post.type');
        $where = 'refund_id ='.I('post.refund_id');
        $refund = M("Refund");
        $refund->remark = I('post.remark');
        $refund->sure_time = time();
        $refund->status = 5;
        $refund->where($where)->save();

        echo json_encode(['status'=>1]);
    }

    public function tuikuan() {
        $refund_id = I('post.refund_id');
        $refund = M('Refund')->where('refund_id ='.$refund_id)->find();
        if ($refund['status'] == 2) {

            // 剥离其在订单中使用的一券通购物券和现金
            $_order = M('OrderInfo');
            $_order->cash = $_order->cash - $refund['cash'];
            $_order->yjt = $_order->yjt - $refund['yqt'];
            $_order->gwq = $_order->gwq - $refund['gwq'];
            $_order->where('order_sn ='.$refund['order_sn'])->save();


            $orderGoods = M('OrderGoods')->where('goods_id ='.$refund['goods_id'].' and order_sn ="'.$refund['order_sn'].'"')->find();
            $goods = M('Goods')->where('goods_id = '.$refund['goods_id'])->find();
            //liaopeng 2017-1-19号添加，退货后，捐赠记录修改,退货后结算金额修改
	        // [因订单金额非单品金额，故应增加减去对应捐赠金额的记录，不应直接摸0]
//            $love=M("love","ms_mall_")->where(["order_sn"=>$refund['order_sn'],"fee"=>$goods["love_amount"]])->find();
//            M("love","ms_mall_")->where(["out_trade_no"=>$love['out_trade_no']])->save(["fee"=>0]);
            //修改结算金额
            $back_amout=$goods["cost_price"]*$orderGoods["prosum"];
            $orderinfo = $_order->where('order_sn ='.$refund['order_sn'])->find();
            $new_data['settlement_total']=$orderinfo["settlement_total"]-$back_amout;
            $new_data['settlement_no']=$orderinfo["settlement_no"]-$back_amout;
            $_order->where('order_sn ='.$refund['order_sn'])->save($new_data);


            // 如果是购物券商品, 则删除赠送的购物券，全部以购物券返回
            // 獲取贈送的購物券
            $extra = $this->sendGWJ($goods) * $orderGoods['prosum'];
            $extra = 0;
            if ($goods['consumption_type'] == 3) {
                // 按商品金額來算
                $finalGWQ = $refund['cash'] + $refund['yqt'] + $refund['gwq'] - $extra;
                // 返还购物券回购物券
                AccountController::change($refund['user_id'], $finalGWQ, 'GWQ', 7, false, '订单'.$refund['order_sn'].'退货购物券');
            } else {
                // 返回现金和一券通到一券通
                AccountController::change($refund['user_id'], $refund['cash']+$refund['yqt'], 'YJT', 7, false, '订单'.$refund['order_sn'].'退货返还一券通');
                // 返还购物券回购物券
                AccountController::change($refund['user_id'], $refund['gwq'], 'GWQ', 7, false, '订单'.$refund['order_sn'].'退货购物券');
            }


            // 增加减去爱心基金的记录
	        if ($goods) {
		        $amount =$goods['love_amount'];
		        if ($amount) {
			        $trade_code = $goods['consumption_type'] == 2? "1":"2";
			        $data = array(
				        'out_trade_no'=>serialNumber(),
				        'uid'=> $orderinfo['uid'],
				        'fee'=> -$amount * $orderGoods['prosum'],
				        'type'=>$trade_code,
				        'order_sn'=>$orderGoods['order_sn'],
				        'grant_time'=>time(),
			        );
			        M('Love',C('DB_PREFIX_MALL'))->add($data);
		        }
	        }


            $refund = M("Refund");
            $refund->status = 3;
            if ($refund->where('refund_id ='.$refund_id)->save()) {
                echo json_encode(['status'=>1]);
            }else{
                echo json_encode(['status'=>2, 'content'=>'保存出错']);
            }
        } else {
            echo json_encode(['status'=>3, 'content'=>'该订单已退款或未到退款时机']);
        }
    }

}