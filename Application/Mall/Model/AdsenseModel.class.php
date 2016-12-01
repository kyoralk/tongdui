<?php
namespace Mall\Model;
use Think\Model\RelationModel;

class AdsenseModel extends RelationModel{
	protected $_link = array(
			'adsense_position' =>array(
					'mapping_type'=>self:: BELONGS_TO,
					'class_name'=>'adsense_position',
					'foreign_key'=>'position_id',
					'as_fields'=>'position_widht,position_height',
			),
	);
}