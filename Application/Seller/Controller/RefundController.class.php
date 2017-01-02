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
            }else{
                echo json_encode(['status'=>2, 'content'=>'保存出错']);
            }
        }
    }

    public function ndohuo() {
        $type = I('post.type');
        $where = 'refund_id ='.I('post.refund_id');
        $refund = M("Refund");
        $refund->sure_time = time();
        $refund->status = 1;
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
            
            // 如果是购物券商品, 则删除赠送的购物券，全部以购物券返回
            if ($goods['consumption_type'] == 3) {
                $extra = $orderGoods['gwq_send'] + $orderGoods['gwq_extra'];
                if ($orderGoods['comsuption_type'] == 3) { 
                    $finalGWQ = $refund['cash'] + $refund['yqt'] + $refund['gwq'] - $extra;
                    // 返还购物券回购物券
                    AccountController::change($refund['user_id'], $finalGWQ, 'YJT', 7, false, '订单'.$refund['order_sn'].'退货购物券');
                } else {
                    // 返回现金和一券通到一券通
                    AccountController::change($refund['user_id'], $refund['cash']+$refund['yqt'], 'YJT', 7, false, '订单'.$refund['order_sn'].'退货返还一券通');
                    // 返还购物券回购物券
                    AccountController::change($refund['user_id'], $refund['gwq'], 'YJT', 7, false, '订单'.$refund['order_sn'].'退货购物券');
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