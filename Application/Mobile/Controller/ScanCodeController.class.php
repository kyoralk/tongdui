<?php
use Mobile\Controller\InitController;

class ScanCodeController extends InitController{
	/**
	 * 扫码
	 */
	public function todo(){
		if(I('get.from')=='app'){
			
		}else{
			$this->display('Public/download');
		}
	}
	
	
}