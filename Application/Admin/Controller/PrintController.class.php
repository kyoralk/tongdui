<?php
/**
 * Created by liaopeng.
 * User: liaopeng
 * Date: 2017/1/6
 * Time: 17:00
 */

namespace Admin\Controller;
use Admin\Controller\CommonController;

class PrintController extends CommonController
{
    function index()
    {
       // file_put_contents("c:/1.txt",json_encode($_POST));
        file_put_contents("c:/1.txt","111111");
        $T = isset($_POST['T']) && trim($_POST['T']) ? trim($_POST['T']) : '';
        $F = isset($_POST['F']) && trim($_POST['F']) ? trim($_POST['F']) : '';
        $W = isset($_POST['W']) && trim($_POST['W']) ? trim($_POST['W']) : '';
        $action = isset($_POST['action']) && trim($_POST['action']) ? trim($_POST['action']) : '';
        $GetType = isset($_POST['GetType']) && trim($_POST['GetType']) ? trim($_POST['GetType']) : '';
        $admin_user = isset($_POST['admin_user']) && trim($_POST['admin_user']) ? trim($_POST['admin_user']) : '';
        $hash_code = isset($_POST['hash_code']) && trim($_POST['hash_code']) ? trim($_POST['hash_code']) : '';
        $user=M('member')->where(['username'=>$admin_user])->find();
        $user or die("106");
        $user['password'] ==md5($hash_code) or die("107");
        if ($action == 'd') {
            $now_time = local_date('Y-m-d H:i:s', time());
            $lastday_time = local_date('Y-m-d H:i:s', time() - 86400);
            $start_time = isset($_POST['start_time']) && trim($_POST['start_time']) ? trim($_POST['start_time']) : $lastday_time;
            $end_time = isset($_POST['end_time']) && trim($_POST['end_time']) ? trim($_POST['end_time']) : $now_time;
            $start_time = strtotime($start_time) - 8 * 60 * 60;
            $end_time = strtotime($end_time) - 8 * 60 * 60;
            if ($start_time == $end_time) {
                $end_time = $start_time + 86400;
            }
            $store_info = M('Store')->where('uid = '.$user['uid'])->find();
            $shop_name=$store_info['store_name'];
            $shop_url="";
            $shop_address=$store_info['company_location'].' '.$store_info['company_address'];
            $service_phone=$user['mobile'];

            header('Content-Type: text/xml;');
            echo '<?xml version="1.0" encoding="utf-8" ?>';
            echo '<order_list>';
            $order_ids=M('M')->where(['shipping_status'=>0,'pay_status'=>1,'receipt_status'=>0,'order_time'=>['between',[$start_time,$end_time]]])->select();
            foreach ($order_ids as $val) {
                $val['total_fee']==$val['shipping_fee']=0;
                $val['country']='中国';
                $val['province']='';
                $val['city']='';
                $val['best_time']=date('Y-m-d H:i:s',time());
                echo '<order>';
                echo '<shop_name><![CDATA[' . $shop_name . ']]></shop_name>';
                echo '<shop_url><![CDATA[' . $shop_url . ']]></shop_url>';
                echo '<shop_address><![CDATA[' . $shop_url . ']]></shop_address>';
                echo '<service_phone><![CDATA[' . $service_phone . ']]></service_phone>';
                echo '<order_id><![CDATA[' . $val['order_sn'] . ']]></order_id>';
                echo '<order_sn><![CDATA[' . $val['order_sn'] . ']]></order_sn>';
                echo '<user_name><![CDATA[' . $val['consignee'] . ']]></user_name>';
                echo '<add_time><![CDATA[' . date('Y-m-d H:i:s', $val['order_time']) . ']]></add_time>';
                echo '<total_fee><![CDATA[' . $val['total_fee'] . ']]></total_fee>';
                echo '<goods_amount_a><![CDATA[' . $val['total'] . ']]></goods_amount_a>';
                echo '<order_amount><![CDATA[' . $val['total'] . ']]></order_amount>';
                echo '<shipping_fee><![CDATA[' . $val['shipping_fee'] . ']]></shipping_fee>';
                echo '<consignee><![CDATA[' . $val['consignee'] . ']]></consignee>';
                echo '<country><![CDATA[' . $val['country'] . ']]></country>';
                echo '<province><![CDATA[' . $val['province'] . ']]></province>';
                echo '<city><![CDATA[' . $val['city'] . ']]></city>';
                echo '<district><![CDATA[' . $val['address'] . ']]></district>';
                echo '<best_time><![CDATA[' . $val['best_time'] . ']]></best_time>';
                echo '<invoice_no><![CDATA[]]></invoice_no>';
                echo '<address><![CDATA[' . $val['address'] . ']]></address>';
                echo '<zipcode><![CDATA[]]></zipcode>';
                echo '<tel><![CDATA[]]></tel>';
                echo '<mobile><![CDATA[' . $val['mobile'] . ']]></mobile>';
                $val['composite_status'] = "1001";
                if($val['shipping_status']==1)
                {
                    $val['composite_status'] = "1003";
                }
                echo '<composite_status><![CDATA[' . $val['composite_status'] . ']]></composite_status>';
                echo '<order_status><![CDATA[0]]></order_status>';
                echo '<shipping_status><![CDATA[' . $val['shipping_status'] . ']]></shipping_status>';
                echo '<pay_status><![CDATA[' . $val['pay_status'] . ']]></pay_status>';
                echo '<pay_time><![CDATA[' . date('Y-m-d H:i:s', $val['order_time']) . ']]></pay_time>';
                echo '<shipping_name><![CDATA[ ]]></shipping_name>';
                echo '<pay_name><![CDATA[ ]]></pay_name>';
                echo '<to_buyer><![CDATA[ ]]></to_buyer>';
                echo '<postscript><![CDATA[ ]]></postscript>';
                echo '<shipping_time><![CDATA[]]></shipping_time>';

                $goods_list = array();
                $goods_attr = array();


                $res = $db->query($sql);
                while ($row = $db->fetchRow($res)) {
                    if ($row['is_real'] == 0) {
                        $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';
                        if (file_exists($filename)) {
                            include_once($filename);
                            if (!empty($_LANG[$row['extension_code'] . '_link'])) {


                                $row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
                            }
                        }
                    }
                    $row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
                    $row['formated_goods_price'] = price_format($row['goods_price']);


                    $goods_attr[] = explode(' ', trim($row['goods_attr']));

                    if ($row['extension_code'] == 'package_buy') {
                        $row['storage'] = '';
                        $row['brand_name'] = '';
                        $row['package_goods_list'] = get_package_goods($row['goods_id']);
                    }
                    $goods_list[] = $row;
                }
                $attr = array();
                $arr = array();
                foreach ($goods_attr AS $index => $array_val) {
                    foreach ($array_val AS $value) {
                        $arr = explode(':', $value);
                        $attr[$index][] = @array('name' => $arr[0], 'value' => $arr[1]);
                    }
                }
                $orderarraylist = explode(",", $F);
                //explode
                foreach ($orderarraylist as $orderstrval) {
                    if ($orderstrval != '') echo '<' . $orderstrval . '><![CDATA[' . $order[$orderstrval] . ']]></' . $orderstrval . '>';
                };
                echo '</order>';
                echo '<goods_list>';
                foreach ($goods_list as $goods) {
                    echo '<goods>';
                    echo '<order_sn><![CDATA[' . $order['order_sn'] . ']]></order_sn>';
                    echo '<order_id><![CDATA[' . $order['order_id'] . ']]></order_id>';
                    echo '<goods_name><![CDATA[' . strip_tags(str_replace('&', '', str_replace('/', '', str_replace('>', '', str_replace('<', '', str_replace('nbsp;', '', $goods['goods_name'])))))) . ']]></goods_name>';
                    echo '<goods_sn><![CDATA[' . $goods['goods_sn'] . ']]></goods_sn>';
                    echo '<goods_attr><![CDATA[' . $goods['goods_attr'] . ']]></goods_attr>';
                    echo '<goods_price><![CDATA[' . $goods['formated_goods_price'] . ']]></goods_price>';
                    echo '<goods_number><![CDATA[' . $goods['goods_number'] . ']]></goods_number>';
                    echo '<formated_subtotal><![CDATA[' . $goods['formated_subtotal'] . ']]></formated_subtotal>';
                    echo '<goods_weight><![CDATA[' . $goods['goods_weight'] . ']]></goods_weight>';
                    echo '<goods_thumb><![CDATA[' . $goods['goods_thumb'] . ']]></goods_thumb>';
                    echo '<goods_img><![CDATA[' . $goods['goods_img'] . ']]></goods_img>';
                    echo '<suppliers_name><![CDATA[' . $goods['suppliers_name'] . ']]></suppliers_name>';
                    echo '<brand_name><![CDATA[' . $goods['brand_name'] . ']]></brand_name>';
                    echo '</goods>';
                }
                echo '</goods_list>';
            }
            echo '</order_list>';

        }
    }
    function get_post()
    {
        $str='{"action":"d","start_time":"2017-01-05 16:57:49","end_time":"2017-01-06 16:57:48","shipping_status":"0","pay_status":"0","admin_user":"admin","hash_code":"admin","F":"integral,integral_money,bonus,discount,pack_fee ,card_fee,money_paid,card_message,inv_payee,inv_content,tax,insure_fee,goods_amount,surplus,order_sn_ext,delivery_sn,order_note1,order_note2,order_note3,order_note4,order_note5,order_note6,order_note7,order_note8,order_note9,order_note10","DefAS":"and order_status=1","V":"V2.7"}
';
        echo "<pre>";
        print_r(json_decode($str));
    }


}