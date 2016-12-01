<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class ArticleModel extends RelationModel{
	
	protected $_link = array(
	
			'article_class' =>array(
					'mapping_type'=>self::BELONGS_TO,
					'class_name'=>'article_class',
					'foreign_key'=>'ac_id',
					'as_fields'=>'ac_name',
			),
	
	);
	
}