<?php
/**
 * Created by liaopeng.
 * User: liaopeng
 * Date: 2017/1/12
 * Time: 9:44
 * 这个文件本来做版本更新接口与第三方支付的关闭接口，现在用于加上我需要添加的所有接口。
 *
 */
namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use General\Controller\GeneralController;

class VersionController extends CommonController
{
    /**
     * 获取版本
     */
    public function check_version()
    {
        die(json_encode(['version'=>"1.00"]));
    }

    /**
     * 获取提现列表
     */
    public function getaccount()
    {
        $page=I("get.page");
        $page or $page = 1;
        $start=$page-1;
        $end=$start*10;
        $data=M('ApplyWithdraw','ms_common_')->where(['uid'=>$this->member_info['uid']])->order(['apply_no'=>'desc'])->limit($start,$end)->select();
        jsonReturn($data);
    }
    /**
     * 获取店铺状态
     */
    public function get_store()
    {
        $store=M("store","ms_mall_")->where(['uid'=>$this->member_info['uid']])->find();
        jsonReturn($store);
    }
    public  function test()
    {
        $prosum=1;
        M("goods")->where(["goods_id"=>214])->setDec('stock',$prosum);
        echo M("goods")->getLastSql();
    }


}