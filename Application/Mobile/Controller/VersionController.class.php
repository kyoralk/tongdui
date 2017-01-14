<?php
/**
 * Created by liaopeng.
 * User: liaopeng
 * Date: 2017/1/12
 * Time: 9:44
 */

namespace Mobile\Controller;
use Think\Controller;

class VersionController extends Controller
{
    public function check_version()
    {
        die(json_encode(['version'=>"1.00"]));
    }
    public function test()
    {
        $arr=M('Refund')->find();
        echo M('Refund')->getLastSql();
    }


}