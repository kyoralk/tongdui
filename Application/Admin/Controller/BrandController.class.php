<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
use General\Util\Image;

class BrandController extends MallController{
	/**
	 * 品牌列表
	 */
	public function brandList(){




		$brand = M('Brand');
        if(I('get.brand_name'))
        {

            $condition['brand_name']=['like',"%".I('get.brand_name')."%"];
            $this->assign('brand_name',I('get.brand_name'));
        }
		$data = page($brand, $condition,20,'','brand_id desc');
		$this->assign("page", $data['page']);
		$this->assign('brand_list',$data['list']);
		$this->assign('content_header','品牌列表');
		$this->assign('right_menu',array('url'=>U('Brand/view'),'icon'=>'fa-plus','text'=>'添加品牌'));

		$this->display('Brand/brand_list');
	}
    /**
     * 品牌审批
     */
    function brand_apply()
    {
        if(I('get.brand_name'))
        {

            $condition['brand_name']=['like',"%".I('get.brand_name')."%"];
            $this->assign('brand_name',I('get.brand_name'));
        }
        $data = page(M("brand_apply"), $condition,20,'','status');
        $this->assign("page", $data['page']);
        $this->assign('brand_list',$data['list']);
        $this->assign('content_header','品牌审批');
        $this->display('Brand/brand_apply_list');
    }
    /**
     * 品牌审请驳回
     */
    function apply_bh($brand_id)
    {
       if(M("brand_apply")->where(["brand_id"=>$brand_id])->save(["status"=>2]))
       {
           $this->success("驳回成功",U("/Admin/Brand/brand_apply"));
       }

    }
    /**
     * 审批界面
     */
    function apply_jm($brand_id)
    {
        $class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
        $this->assign('class_list',$class_list);
        $info=M("brand_apply")->where(["brand_id"=>$brand_id])->find();
        $this->assign('brand_info',$info);
        $this->assign('content_header','品牌审批');
        $this->display('Brand/apply_info');

    }
    /**
     * 审批操作
     */
    function apply_check()
    {
        $Brand = M('Brand');

        $data = $Brand->create();
        $brand_apply_id = I('post.brand_apply_id',0);
        unset($data["brand_apply_id"]);
        if(M("brand_apply")->where(["brand_id"=>$brand_apply_id])->save(["status"=>1])){
            if($Brand->add($data)){

                $this->success('审批成功',U("Admin/Brand/brand_apply"));

            }else{
                $this->error('审批失败',U("Admin/Brand/brand_apply"));
            }

        }else{
            $this->error('审批失败',U("Admin/Brand/brand_apply"));
        }



    }
	/**
	 * 添加品牌模板页
	 */
	public function view(){
		$class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
		$this->assign('class_list',$class_list);
		$this->assign('content_header','添加品牌');
		$this->display('Brand/info');
	}
	/**
	 * 编辑品牌
	 */
	public function edit(){
		$class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
		$brand_info = M('Brand')->where('brand_id = '.I('get.brand_id'))->find();
		$brand_info['brand_logo'] = '/Uploads/'.C('MALL_BRAND').$brand_info['brand_logo'];
		$this->assign('brand_info',$brand_info);
		$this->assign('class_list',$class_list);
		$this->assign('content_header','编辑品牌');
		$this->display('Brand/info');
	}
	/**
	 * 保存品牌
	 */
	public function save(){
		$Brand = M('Brand');
		$data = $Brand->create();

		$brand_id = I('post.brand_id',0);
		if($_FILES['brand_logo']){
			$res = Image::upload('brand_logo', 'MALL_BRAND');
			$data['brand_logo'] = $res['savename'];
		}
		if($brand_id){
			if(is_numeric($Brand->save($data))){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
		}else{
			if($Brand->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 删除品牌
	 */
	public function delete(){
		$Brand = M('Brand');
		$brandinfo=$Brand->where('brand_id = '.I('get.brand_id'))->find();
		$brand_logo = $brandinfo['brand_logo'];
		if(!empty($brand_logo)){
			$brand_logo = './Uploads/'.C('MALL_BRAND').$brand_logo;
			if(file_exists($brand_logo)){
				unlink($brand_logo);
			}
		}
		if($Brand->where('brand_id = '.I('get.brand_id'))->delete()){
		    M("brand_apply")->where(["brand_name"=>$brandinfo["brand_name"],"brand_desc"=>$brandinfo["brand_desc"],"brand_logo"=>$brandinfo["brand_logo"],"gc_id"=>$brandinfo["gc_id"]])->delete();
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 删除logo
	 */
	public function logoDelete(){
		$file = I('get.path');
		$file = '.'.$file;
		if(file_exists($file)){
			if(unlink($file)){
				if(M('Brand')->where('brand_id = '.I('get.id'))->setField('brand_logo', '')){
					$this->ajaxJsonReturn('','删除成功',1);
				}else{
					$this->ajaxJsonReturn('','删除失败',0);
				}
			}
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}	
	/**
	 * 获取品牌
	 */
	public function getBrand(){
		//查找父目录
		$GoodsClass = M('GoodsClass');
		$gc_id = I('get.gc_id',0);
		if($gc_id){
			do{
				$gc_parent_id = $GoodsClass->where('gc_id = '.$gc_id)->getField('gc_parent_id');
				if($gc_parent_id){
					$gc_id = $gc_parent_id;
				}
			}while($gc_parent_id);
			$brand_list = M('Brand')->where('gc_id = '.$gc_id)->field('brand_id,brand_name')->select();
			$this->ajaxJsonReturn($brand_list);
		}else{
			$this->ajaxJsonReturn('','',0);
		}
	}
	
}