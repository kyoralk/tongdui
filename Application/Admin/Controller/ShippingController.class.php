<?php
namespace Admin\Controller;
use Admin\Controller\MallController;

class ShippingController extends MallController{
	/**
	 * 配送列表
	 */
	public function shippingList(){
		$data = page(M('Shipping'), $condition,20);
		$this->assign('shipping_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','配送方式列表');
		$this->assign('right_menu',array('url'=>U('Shipping/info'),'icon'=>'fa-plus','text'=>'添加方式'));
		$this->display('Shipping/shipping_list');
	}
	/**
	 * 配送方式详情
	 */
	public function info(){
		
		if(I('get.shipping_id')){
			$shipping_info = M('Shipping')->where('shipping_id = '.I('get.shipping_id'))->find();
			$this->assign('shipping_info',$shipping_info);
			$this->assign('content_header','编辑配送方式');
		}else{
			$this->assign('content_header','添加配送方式');
		}
		
		$this->display();
	}
	/**
	 * 保存配送方式
	 */
	public function save(){
		$shipping = M('Shipping');
		$data = array(
				'shipping_name'=>I('post.shipping_name'),
				'shipping_code'=>I('post.shipping_code'),
				'shipping_desc'=>I('post.shipping_desc'),
				'enabled'=>I('post.enabled'),
		);
		$shipping_id = I('post.shipping_id',0);
		if($shipping_id){
			if(is_numeric($shipping->where('shipping_id = '.$shipping_id)->save($data))){
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}else{
			if($shipping->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 修改稿配送方式状态
	 */
	public function updateStatus(){
		$data = array(
				'shipping_id' => I('get.shipping_id'),
				'enabled' =>I('get.value') == 1 ? 0: 1,
		);
		if(M('Shipping')->save($data)){
			$this->ajaxJsonReturn($data);
		}else{
			$this->ajaxJsonReturn('','',0);
		}
	}
	/**
	 * 运费模板列表
	 */
	public function templList(){
		$data = page(M('ShippingTemplate'), $condition,20);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','模板列表');
		$this->assign('right_menu',array('url'=>U('Shipping/templInfo'),'icon'=>'fa-plus','text'=>'添加模板'));
		$this->display('Shipping/templ_list');
	}
	/**
	 * 运费模板详情
	 */
	public function templInfo(){
		$shipping_list = M('Shipping')->where('enabled = 1')->select();
		$region_list = R('General/Region/getRegion');
		if(I('get.templ_id')){
			$templ_info = D('ShippingTemplate')->relation('shipping_area')->where('templ_id = '.I('get.templ_id'))->find();
			$this->assign('templ_info',$templ_info);
			//处理选中的物流公司
			$shipping_checked = explode(',', $templ_info['shipping_id_str']);
			$count = count($shipping_list);
			for($i = 0;$i<$count;$i++){
				if(in_array($shipping_list[$i]['shipping_id'], $shipping_checked)){
					$shipping_list[$i]['checked'] = 1;
				}
			}
			//处理选中的区域
			$rgion_checked_1 = explode(',', $templ_info['province_str']);
			$rgion_checked_2 = array_column($templ_info['shipping_area'], 'area_id');
			$count = count($region_list);
			for($i = 0;$i<$count;$i++){
				if(in_array($region_list[$i]['id'], $rgion_checked_1)){
					$region_list[$i]['checked'] = 1;
				}
				$c_count = count($region_list[$i]['child']);
				for($j=0;$j<$c_count;$j++){
					if(in_array($region_list[$i]['child'][$j]['id'], $rgion_checked_2)){
						$region_list[$i]['child'][$j]['checked'] = 1;
					}
				}
			}
			$area_list = M('Region',C('DB_PREFIX_C'))->where(array('id'=>array('IN',$rgion_checked_2)))->getField('name',true);
			$this->assign('area_list',$area_list);
			$this->assign('content_header','编辑运费模板');
		}else{
			$this->assign('content_header','添加运费模板');
		}
		$this->assign('shipping_list',$shipping_list);
		$this->assign('region_list',$region_list);
		$this->display('Shipping/templ_info');
	}
	/**
	 * 保存运费模板
	 */
	public function templSave(){
		$templ_id = I('post.templ_id',0);
		$shipping_id_array = I('post.shipping_id');
		$province_array = I('post.province');
		$region_id_array = I('post.region_id');
		if(empty($shipping_id_array)){
			$this->error('请选择物流公司');
			exit();
		}
		if(empty($region_id_array)){
			$this->error('请选择配送区域');
			exit();
		}
		$data = array(
				'templ_name'=>empty(I('post.templ_name'))?'运费模板':I('post.templ_name'),
				'type'=>I('post.type'),
				'first_fee'=>empty(I('post.first_fee'))? 0 : I('post.first_fee'),
				'next_fee'=>empty(I('post.next_fee'))? 0 : I('post.next_fee'),
				'shipping_id_str'=>implode(',', $shipping_id_array),
				'province_str'=>implode(',', $province_array)
		);
		$Tmpl = M('ShippingTemplate');
		$Area = M('ShippingArea');
		try {
			if($templ_id){
				$Tmpl->where('templ_id = '.$templ_id)->save($data);
				$Area->where('templ_id = '.$templ_id)->delete();
			}else{
				$templ_id = $Tmpl->add($data);
			}			
		} catch (Exception $e) {
			$this->error('保存失败');
			exit();
		}
		$area = array();
		foreach ($shipping_id_array as $shipping_id){
			foreach ($region_id_array as $region_id){
		 		$area[] = array(
		 				'templ_id'=>$templ_id,
		 				'area_id'=>$region_id,
		 				'shipping_id'=>$shipping_id,
		 		);
		 	}
		}
		if($Area->addAll($area)){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
	/**
	 * 删除运费模板
	 */
	public function templDel(){
		 if(D('ShippingTemplate')->relation(true)->delete(I('get.templ_id'))){
		 	$this->success('删除成功');
		 }else{
		 	$this->error('删除失败');
		 }
	}
}