<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;

class FreightController extends CommonController{
	
	public function templList(){
		$data = page(M('ShippingTemplate'), array('store_id'=>session('store_id')),20);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display('Freight/templ_list');
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
		$this->display('Freight/templ_info');
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
				'province_str'=>implode(',', $province_array),
				'store_id'=>session('store_id'),
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