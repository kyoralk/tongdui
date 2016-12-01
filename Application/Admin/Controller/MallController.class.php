<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class MallController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_MALL'));//动态设置数据库表前缀
	}
}
