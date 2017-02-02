<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;
use General\Util\LBS;
use General\Util\Image;
class StoreController extends CommonController{
	/**
	 * 基本信息
	 */
	public function setting(){
		$store_info = M('Store')->where('store_id = '.session('store_id'))->find();
		if(empty($store_info['lat']) || empty($store_info['lng'])){
			$res = LBS::ipLoacation();
			$store_info['lng'] = $res['content']['point']['x'];
			$store_info['lat'] = $res['content']['point']['y'];
		}
		$this->assign('store_info',$store_info);
		$this->display();
	}
	/**
	 * 保存基本信息
	 */
	public function settingSave(){
		$data = array(
				'store_id'=>session('store_id'),
				'store_name'=>I('post.store_name'),
				'store_zy'=>I('post.store_zy'),
				'store_qq'=>I('post.store_qq'),
				'store_ww'=>I('post.store_ww'),
				'store_keywords'=>I('post.store_keywords'),
				'store_description'=>I('post.store_description'),
				'company_description'=>I('post.company_description'),
				'offline_address'=>I('post.offline_address'),
				'lng'=>I('post.lng'),
				'lat'=>I('post.lat'),
				'gwq_max'=>I('post.gwq_max'),
		);
		if(I('post.store_logo')){
			if(file_exists('./Uploads/Mall/Seller/'.session('store_id').'/Store/'.I('post.old_store_logo'))){
				unlink('.'.I('post.old_store_logo'));
			}
			$res = Image::createImg(array(I('post.store_logo')), 'MALL_SELLER',false,session('store_id').'/Store/');
			$data['store_logo'] = $res[0];
		}else{
			$data['store_logo'] = I('post.old_store_logo');
		}
		if(I('post.store_banner')){
			if(file_exists('.'.I('post.old_store_banner'))){
				unlink('.'.I('post.old_store_banner'));
			}
			$res = Image::createImg(array(I('post.store_banner')), 'MALL_SELLER',false,session('store_id').'/Store/');
			$data['store_banner'] = $res[0];
		}
		$Store = M('Store');
		if(is_numeric($Store->save($data))){
			 
			$lbs_data = array(
					'title'=>$data['store_name'],
					'address'=>$data['offline_address'],
					'tags'=>$data['store_keywords'],
					'latitude'=>$data['lat'],
					'longitude'=>$data['lng'],
					'coord_type'=>3,
					'geotable_id'=>'154774',
					'store_id'=>$data['store_id'],
					'store_logo'=>$data['store_logo'],
			);
			
			if(I('post.lbs_id')){
				$lbs_data['id'] = I('post.lbs_id');
				LBS::updatePoi($lbs_data);
			}else{
				$res = LBS::createPoi($lbs_data);
				$Store->where('store_id = '.session('store_id'))->setField('lbs_id',$res['id']);
			}
			
		
			
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
	/**
	 * 幻灯片设置
	 */
	public function slide(){

	    // 避免没有幻灯片，优先创建
        $hasNum = M('StoreSlide')->where('store_id ='.session('store_id'))->count();
        if (empty($hasNum)) {
            foreach ([1,2,3,4,5] as $key) {
                $StoreSlide = M('StoreSlide');
                $StoreSlide->add(array(
                        'store_id'=>session('store_id')
                    )
                );
            }
        }


		$slide = M('StoreSlide');
		$slide_list=$slide->where('store_id = '.session('store_id'))->select();
		if(empty($slide_list)){
			$slide_list = array('','','','','');
		}
		$this->assign('slide_list',$slide_list);
		$this->display();
	}
	/**
	 * 修改幻灯图片
	 */
	public function updateSlide(){
		$old_img = I('post.old_img');
		$slide_id = I('post.id');
		$file_path = './Uploads/'.C('MALL_SELLER').session('store_id').'/Store/'.$old_img;
		if(file_exists($file_path)){
			unlink($file_path);
		}
		$res = Image::createImg(array(I('post.new_img')), 'MALL_SELLER',false,session('store_id').'/Store/');
        print_r($res);
		if(M('StoreSlide')->where('store_id = '.session('store_id').' AND slide_id = "'.$slide_id.'"')->setField('img',$res[0])){
			$this->ajaxReturn(1,'JSON');
		}else{
			$this->ajaxReturn(0,'JSON');
		}
	}
	/**
	 * 修改幻灯的其他信息
	 */
	public function updateSlideOther(){
		$url = I('post.url');
		$sort = I('post.sort');
		$slide_id = I('post.slide_id');
		$goods_id = I('post.goods_id');
		$count = count($url);
		$StoreSlide = M('StoreSlide');
		$error = 0;

		for($i = 0;$i<$count;$i++){
		    if ($slide_id[$i]) {
		        $StoreSlide->where('slide_id = '.$slide_id[$i]);
                if(!is_numeric($StoreSlide->save(array('url'=>$url[$i],'sort'=>$sort[$i],'goods_id'=>$goods_id[$i])))){
                    $error++;
                }
            } else {
                if(!is_numeric($StoreSlide->add(array(
                        'url'=>$url[$i],
                        'sort'=>$sort[$i],
                        'goods_id'=>$goods_id[$i],
                        'store_id'=>session('store_id')
                    )
                ))){
                    $error++;
                }
            }
		}
		if($error>0){
			$this->error('更新失败');
		}else{
			$this->success('更新成功');
		}
	}
	/**
	 * 删除幻灯片信息
	 */
	public function deleteSlide(){
		$img = I('get.img');
		$file_path = './Uploads/'.C('MALL_SELLER').session('store_id').'/Store/'.$img;
		if(file_exists($file_path)){
			unlink($file_path);
		}
		if(is_numeric(M('StoreSlide')->where('slide_id = '.I('get.slide_id',0))->save(array('img'=>'','url'=>'','sort'=>'')))){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}
