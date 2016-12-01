<?php
/**
 * Created by PhpStorm.
 * User: Kyoralk
 * Date: 2016/11/29
 * Time: 22:07
 */
namespace Mobile\Model;
use Think\Model\RelationModel;

class RefundModel extends RelationModel{
    protected $_link = array(
        'order_goods' =>array(
            'mapping_type'=>self::HAS_ONE,
            'class_name'=>'order_goods',
            'foreign_key'=>'goods_id',
            'mapping_key' => 'goods_id',
            'mapping_name'=>'order_goods',
        ),
    );
}