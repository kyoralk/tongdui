<?php
/**
 * Created by PhpStorm.
 * User: Kyoralk
 * Date: 2016/11/29
 * Time: 23:21
 */

namespace Admin\Controller;

use Seller\Model\RefundModel;
use Think\Controller;
class RefundController extends MallController{

    /**
     * 登陆检测
     */
    public function tui(){
        $content_header = '退货列表';
        $condition ['type'] = '退货';
        $data = page(M('Refund'), $condition,16,'','refund_id desc');
        $this->assign('status_array', RefundModel::$status);
        $this->assign('order_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('content_header',$content_header);
        $this->display('index');
    }

    public function huan(){
        $content_header = '换货列表';
        $condition ['type'] = '换货';
        $data = page(M('Refund'), $condition,16,'','refund_id desc');
        $this->assign('status_array', RefundModel::$status);
        $this->assign('order_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('content_header',$content_header);
        $this->display('index');
    }

}