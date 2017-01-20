<?php
/**
 * Created by PhpStorm.
 * User: Kyoralk
 * Date: 2016/11/19
 * Time: 23:12
 */

namespace Seller\Model;

use Think\Model\RelationModel;

class RefundModel extends RelationModel {

    protected $_link = array(
        'order_info' =>array(
            'mapping_type'=>self::BELONGS_TO,
            'class_name'=>'order_info',
            'foreign_key'=>'order_sn',
            'mapping_name'=>'order_info',
        ),


    );

    public static $status = ['0'=>'提交', '1'=>'已确认', '2'=>'已发货', '3'=>'已退款', '4'=>'已换货', '5'=>'已驳回'];
}