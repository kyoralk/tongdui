<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
use General\Util\Image;

class GoodsController extends MallController{
	/**
	 * 商品列表
	 */
	public function goodsList(){
		if(I('get.seller')==1){
			$this->assign('seller',1);
			$condition['store_id'] = array('gt',$this->system_store_id);
		}else{
			$this->assign('seller',0);
			$condition['store_id'] = $this->system_store_id;
		}
		if(!empty(I('get.examine_status'))){
			$condition['examine_status'] = I('get.examine_status');
			$this->assign('examine_status',I('get.examine_status'));
		}
		if(!empty(I('get.keywords'))){
			$condition['goods_name|keywords'] =array('like',array('%'.I('get.keywords').'%'),'AND');
			$this->assign('keywords',I('get.keywords'));
		}
		if(!empty(I('get.consumption_type'))){
			$condition['consumption_type'] = I('get.consumption_type');
			$this->assign('consumption_type',$condition['consumption_type']);
		}
		$data = page(M('Goods'), $condition,20,'','goods_id desc','goods_id,sort,goods_name,shop_price,is_on_sale,is_best,is_new,is_hot,floor_show,examine_status,consumption_type');
		$this->assign('goods_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','商品列表');
		$this->assign('all_goods',I('get.all_goods'));
		$this->display('Goods/goods_list');
	}
    /**2017-02-04 liaopeng
     * 更新商品排序
     */
    function sort()
    {
        M("goods")->where(["goods_id"=>$_POST["gid"]])->save(["sort"=>$_POST["sort"]]);
        die("ok");
    }
	/**
	 * 添加商品模板页
	 */
	public function view(){
		$this->assign('class_list',R('Class/classTree'));
		$this->assign('tag_list',$this->tags(''));
		$this->assign('content_header','添加商品');
		$this->display('Goods/info');
	}
	/**
	 * 编辑商品
	 */
	public function edit(){
		$goods_info = D('Goods')->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
		$this->assign('goods_info',$goods_info);
		$this->assign('class_list',R('Class/classTree'));
		$this->assign('tag_list',$this->tags($goods_info['goods_tag']));
		$this->assign('content_header','编辑商品');
		$this->display('Goods/info');
	}
	/**
	 * 保存商品
	 */
	public function save(){
		//通用信息
		$goods_id = empty(I('post.goods_id'))? 0 : I('post.goods_id');
		$data['goods_name'] = I('post.goods_name');
		$data['small_name'] = I('post.small_name');
		$data['goods_sn'] = 'MS_'.time().rand(1000,9999);//商品货号
		$data['gc_id'] = empty(I('post.gc_id')) ? 0 : I('post.gc_id');//商品分类id
		$data['brand_id'] = empty(I('post.brand_id')) ? 0 : I('post.brand_id');//商品分类id
		$data['market_price'] = empty(I('post.market_price')) ? 0: I('post.market_price');//市场价
		$data['shop_price'] = empty(I('post.shop_price')) ? 0 : I('post.shop_price');//本店售价
		$data['max_buy'] = empty(I('post.max_buy')) ? 0 : I('post.max_buy');//最大购买量
		$data['goods_desc'] = $_POST['goods_desc'];//商品描述
		$data['goods_weight'] = empty(I('post.goods_weight')) ? 0 : I('post.goods_weight');//商品种类
		$data['is_on_sale'] = I('post.is_on_sale',0);//是否上架
		$data['freight_type'] = I('post.freight_type');//运费计算方式
		$data['freight'] = empty(I('post.freight_type'))? 0 : I('post.freight_type');//运费
		$data['keywords'] = I('post.keywords');//seo关键字
		$data['description'] = I('post.description');//seo描述
		$data['mid'] = empty(I('post.mid')) ? 0 : I('post.mid');//商品模型id
		$data['examine_status'] = 1;//1：审核通过,  2：未审核 ,3： 审核不通过
		$data['goods_tag'] = implode(',', I('post.goods_tag_checkbox')).I('post.goods_tag_radio');//商品标签
		$data['consumption_type'] = I('post.consumption_type');
 		$data['yjt_can'] =  I('post.yjt_can');//可使用的一卷通数量
 		$data['gwq_can'] = I('post.gwq_can');//可使用的购物券数量
        $data['gwq_send'] =  I('post.gwq_send');//可使用的购物券数量
        $data['gwq_extra'] =  I('post.gwq_extra');//可使用的购物券数量
		$data['give_integral'] = I('post.give_integral');//赠送多少积分
		$data['promote_price'] = empty(I('post.promote_price')) ? 0 : I('post.promote_price');//促销价格

        $data['is_cash'] = isset($_POST['is_cash']) ? 1: 0;//是否允许现金
        $data['is_yqt'] =  isset($_POST['is_yqt']) ? 1: 0;//是否允许一券通
        $data['is_gwq'] = isset($_POST['is_gwq']) ? 1: 0;//是否允许购物券

		//处理促销时间
		$promote_start_date = empty(I('post.promote_start_date')) ? 0 : I('post.promote_start_date');
		$promote_end_date = empty(I('post.promote_end_date'))? 0 : I('post.promote_end_date');
		$data['promote_start_date'] = $promote_start_date == 0 ? 0 : strtotime($promote_start_date);//促销开始时间
		$data['promote_end_date'] = $promote_end_date == 0 ? 0 : strtotime($promote_end_date);//促销结束时间
		//处理上传图片
		if($_FILES['goods_img']){
			$_FILES['goods_img'] = $this->unsetFile($_FILES['goods_img'], I('post.unset_file'));
			if(!empty($_FILES['goods_img'])){
				$res = Image::upload('goods_img', 'MALL_SELLER',true,$this->system_store_id.'/Goods/');
				if($res){
					foreach ($res as $item){
						$data['goods_img'][] = array(
								'save_name' => $item['savename'],
								'save_path' => $item['savepath'],
						);
					}
				}
			}
		}
		//扩展信息
		$extend_id = I('post.extend_id');
		$atv_id = I('post.atv_id');
		$add_price = I('post.add_price');
		$atv_id_count = count($atv_id);
		for($i=0;$i<$atv_id_count;$i++){
			if($extend_id[$i]){
				$data['goods_extend'][$i]['extend_id'] = $extend_id[$i];
			}
			$data['goods_extend'][$i]['atv_id'] = $atv_id[$i];
			$data['goods_extend'][$i]['add_price'] = empty($add_price[$i]) ? 0 : $add_price[$i];
		}
		$Goods = D('Goods');
		
		if($goods_id){
			$data['goods_id'] = $goods_id;
			//判断是否删除了就的扩展信息
			if(empty($extend_id)){
				M('GoodsExtend')->where('goods_id = '.$goods_id)->delete();
			}
			//更新
			if($Goods->relation(true)->save($data)){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
		}else{
			//新增
			if($Goods->relation(true)->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 删除图片
	 */
	public function removeImg(){
		$goods_id = I('get.goods_id');
		$img_name = I('get.img_name');
		$store_id = I('get.store_id')?I('get.store_id'):$this->system_store_id;
		$general_path = './Uploads/'.C('MALL_SELLER').$store_id.'/Goods/';
		$file_0 = $general_path.$img_name;
		$file_l = $general_path.'Thumb/l_'.$img_name;
		$file_m = $general_path.'Thumb/m_'.$img_name;
		$file_s = $general_path.'Thumb/s_'.$img_name;
		try {
			unlink($file_0);
			unlink($file_l);
			unlink($file_m);
			unlink($file_s);
		} catch (Exception $e) {
			
		}
		$condition['goods_id'] = $goods_id;
		$condition['save_name'] = $img_name;
		if(M('GoodsImg')->where($condition)->delete()){
			$this->ajaxJsonReturn('','',1);
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}
	/**
	 * 删除扩展信息
	 */
	public function removeExtend(){
		if(M('GoodsExtend')->where('extend_id = '.I('get.extend_id'))->delete()){
			$this->ajaxJsonReturn('','',1);
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}
	/**
	 * 修改产品状态
	 * 上架，新品，精品，热销
	 */
	public function updateStatus(){
		$data['goods_id']=I('get.goods_id');
		$data[I('get.type')]=I('get.value')==1 ? 0 : 1;
		if(M('Goods')->save($data)){
			$result = array(
					'goods_id'=>$data['goods_id'],
					'value'=>$data[I('get.type')],
			);
			$this->ajaxJsonReturn($result);
		}else{
			$this->ajaxJsonReturn('','',0);
	
		}
	}
	/**
	 * 审核
	 */
	public function examine(){
		$data['examine_status'] = I('param.examine_status');
		if($data['examine_status'] == 3){
			$data['is_on_sale'] = 0;
		}
        if($data['examine_status'] == 1){
            $data['is_on_sale'] = 1;
        }
		if(is_array(I('param.goods_id'))){
			$condition['goods_id'] = array('in',I('param.goods_id'));
		}else{
			$condition['goods_id'] = I('param.goods_id');
		}

		M('Goods')->where($condition)->save($data);
        $this->success('设置成功');
	}
	/**
	 * 上架/下架
	 */
	public function onsale(){
		$condition['goods_id'] = array('in',I('param.goods_id'));
		if(M('Goods')->where($condition)->setField('is_on_sale',I('param.examine_status'))){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
	
	public function consumption(){
		$condition['goods_id'] = array('in',I('param.goods_id'));
		if(M('Goods')->where($condition)->setField('consumption_type',I('param.examine_status'))){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
	/**
	 * 删除file对象中的元素
	 */
	private function unsetFile($file,$unset_file){
		foreach ($unset_file as $unset){
			unset($file['name'][$unset]);
			unset($file['type'][$unset]);
			unset($file['tmp_name'][$unset]);
			unset($file['error'][$unset]);
			unset($file['size'][$unset]);
		}
		return $file;
	}
	/**
	 * 处理选中的标签
	 */
	private function tags($goods_tag){
		$check_tag_array = explode(',', $goods_tag);
		$tag_list = TagController::getTagList('Mall');
		$data = array();
		foreach ($tag_list['tag_radio'] as $item){
			if(in_array($item, $check_tag_array)){
				$data['tag_radio'][] = array(
						'tag'=>$item,
						'checked'=>1,
				);
			}else{
				$data['tag_radio'][] = array(
						'tag'=>$item,
						'checked'=>0,
				);
			}
		}
		foreach ($tag_list['tag_checkbox'] as $item){
			if(in_array($item, $check_tag_array)){
				$data['tag_checkbox'][] = array(
						'tag'=>$item,
						'checked'=>1,
				);
			}else{
				$data['tag_checkbox'][] = array(
						'tag'=>$item,
						'checked'=>0,
				);
			}
		}
		return $data;
	}

    /**
     * 当前分类下的模型
     */
    private function modelList($gc_id_array){
        $Model = M('Model');
        $i = count($gc_id_array) - 1;
        while (!empty($gc_id_array[$i])){
            $model_list = M('Model')->where('gc_id = '.$gc_id_array[$i])->field('mid,model_name')->select();
            if(empty($model_list)){
                $i--;
            }else{
                break;
            }
        }
        return $model_list;
    }

    /**
     * 把常用分类保存的cookie中
     * @param array $gc_name_array
     */
    private function lastClassToCookie($gc_name_array){
        $str = I('get.gc_id_path').'||'.implode('>', $gc_name_array);
        $gc_id_history = empty(cookie('gc_id_history'))? array(): cookie('gc_id_history');
        array_unshift($gc_id_history, $str);
        $gc_id_history = array_unique($gc_id_history);//插入元素到数组首位
        cookie('gc_id_history',$gc_id_history);
    }
    /**
     * 规格信息
     */
    private function goodsSpec(){
        $spec_id = I('post.spec_id');
        $spec_name = I('post.spec_name');
        $spec_cover = I('post.spec_cover');
        $spec_cost_price = I('post.spec_cost_price');
        $spec_add_price = I('post.spec_add_price');
        $spec_inventory = I('post.spec_inventory');
        $spec_name_array = array();
        $spec_value_array = array();
        foreach ($spec_name as $item){
            $spec_name_temp = array();
            $spec_value_temp = array();
            foreach ($item as $loop){
                $temp = explode('@', $loop);
                $spec_name_temp[] = $temp[1];
                $spec_value_temp[] = $temp[0];
                $data['goods_extend'][]['atv_id'] = $temp[0];
            }
            $spec_name_array[] = implode(' ', $spec_name_temp);
            asort($spec_value_temp);//排序
            $spec_value_array[] = implode(',', $spec_value_temp);
        }
        $i = 0;
        while (!empty($spec_name_array[$i])){
            if($spec_id[$i]){
                $data['goods_spec'][$i]['spec_id'] = $spec_id[$i];
            }
            if(strlen($spec_cover[$i])>50){
                $res = Image::createImg(array($spec_cover[$i]), 'MALL_SELLER',true,cookie('fake_store_id').'/Goods/');
                $data['goods_spec'][$i]['spec_cover'] = $res[0];
            }else{
                $data['goods_spec'][$i]['spec_cover'] = $spec_cover[$i];
            }
            $data['goods_spec'][$i]['spec_name'] = $spec_name_array[$i];
            $data['goods_spec'][$i]['spec_value'] = $spec_value_array[$i];
            $data['goods_spec'][$i]['spec_cost_price'] = empty($spec_cost_price[$i])? 0 : $spec_cost_price[$i];
            $data['goods_spec'][$i]['spec_add_price'] = empty($spec_add_price[$i])? 0 : $spec_add_price[$i];
            $data['goods_spec'][$i]['spec_inventory'] = empty($spec_inventory[$i])? 0 : $spec_inventory[$i];
            $data['goods_spec'][$i]['store_id'] = cookie('fake_store_id');
            $i++;
        }
        return $data;
    }
    /**
     * 设置封面图片
     */
    public function setCover(){
        try {
            $condition['goods_id'] = I('get.goods_id');
            $GoodsImg = M('GoodsImg');
            $GoodsImg->where($condition)->setField('is_cover',0);
            $condition['save_name'] = I('get.img_name');
            M('GoodsImg')->where($condition)->setField('is_cover',1);
        } catch (Exception $e) {
            $this->ajaxReturn(0,'JSON');
            exit();
        }
        $this->ajaxReturn(1,'JSON');
    }

    /**
     * 商品信息【商家商品-旧】
     */
    public function oldinfo(){
        $gc_id_path = empty(I('get.gc_id_path')) ? false : I('get.gc_id_path');
        if(!empty(I('get.goods_id'))){
            $goods_info = D('Goods')->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
            cookie('fake_store_id', $goods_info['store_id']);

            if(!$gc_id_path){
                $gc_id_path = $goods_info['gc_id_path'];
            }
            $this->assign('goods_info',$goods_info);
            $this->assign('store_id', $goods_info['store_id']);
            $this->assign('brand_name',M('Brand')->where('brand_id = '.$goods_info['brand_id'])->getField('brand_name'));
        }
        $gc_id_array = explode('-', $gc_id_path);
        $condition['gc_id'] = array('IN',$gc_id_array);
        $gc_name_array = M('GoodsClass')->where($condition)->order('gc_id')->getField('gc_name',true);
        $this->lastClassToCookie($gc_name_array);//把常用分类保存的cookie中
        $this->assign('gc_id',end($gc_id_array));
        $this->assign('gc_id_path',$gc_id_path);
        $this->assign('gc_name_path',implode('>', $gc_name_array));
        $this->assign('model_list',$this->modelList($gc_id_array));
        $this->assign('class_list', $this->getClass('sc_id,sc_parent_id,sc_name'));
        $this->display('info');
    }

    /**
     * 商品信息【商家商品-新】
     */
    public function info(){
        layout('seller_layout');
        $gc_id_path = empty(I('get.gc_id_path')) ? false : I('get.gc_id_path');
        if(!empty(I('get.goods_id'))){
            $goods_info = D('Goods')->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
            cookie('fake_store_id', $goods_info['store_id']);

            if(!$gc_id_path){
                $gc_id_path = $goods_info['gc_id_path'];
            }
            $this->assign('goods_info',$goods_info);
            $this->assign('store_id', $goods_info['store_id']);
            $this->assign('brand_name',M('Brand')->where('brand_id = '.$goods_info['brand_id'])->getField('brand_name'));
        }
        $gc_id_array = explode('-', $gc_id_path);
        $condition['gc_id'] = array('IN',$gc_id_array);
        $gc_name_array = M('GoodsClass')->where($condition)->order('gc_id')->getField('gc_name',true);
        $this->lastClassToCookie($gc_name_array);//把常用分类保存的cookie中
        $this->assign('gc_id',end($gc_id_array));
        $this->assign('gc_id_path',$gc_id_path);
        $this->assign('gc_name_path',implode('>', $gc_name_array));
        $this->assign('model_list',$this->modelList($gc_id_array));
        $this->assign('class_list', $this->getClass('sc_id,sc_parent_id,sc_name'));
        $this->display('newinfo');
    }

    /**
     * 获取店铺分类
     * @param string $sc_parent_id 取出某一种分类
     * @param string $field 要查询的字段
     * @param string $list_to_tree 是否转换成树状结构
     */
    public function getClass($field = '*',$sc_parent_id = '',$list_to_tree = true){
        $condition['store_id'] = cookie('fake_store_id');
        if(is_numeric($sc_parent_id)){
            $condition['sc_parent_id'] = $sc_parent_id;
        }
        $goods_class_list = M('GoodsClass',C('DB_PREFIX_MALL').'store_')->where($condition)->field($field)->select();
        if($list_to_tree){
            $goods_class_list = list_to_tree($goods_class_list,'sc_id','sc_parent_id');
        }
        return $goods_class_list;
    }

    /**
     * 添加产品第一步选择分类
     */
    public function selectClass(){
        layout('seller_layout');
        $gc_parent_id = I('get.gc_id',0);
        $goods_id = I('get.goods_id');
        $class_list = M('GoodsClass')->where('gc_parent_id = '.$gc_parent_id)->field('gc_id,gc_name')->select();
        if(IS_AJAX){
            $this->ajaxJsonReturn($class_list,'',1);
        }else{
            $gc_id_histroy = cookie('gc_id_history');
            foreach ($gc_id_histroy as $item){
                $temp = explode('||', $item);
                $gc_id_path[] = array('path_id_str'=>$temp[0],'path_name_str'=>$temp[1]);
            }
            $this->assign('gc_id_path',$gc_id_path);
            $this->assign('class_list',$class_list);
            $this->assign('goods_id',I('get.goods_id'));
            $this->display('Goods/class_list');
        }
    }

    public function savenew() {
        $examine = 0;
        //通用信息
        $goods_id = empty(I('post.goods_id'))? 0 : I('post.goods_id');
        if ($goods_id) {
            $goods = M('Goods')->where('goods_id ='.$goods_id)->find();
            if ($goods) {
                $examine = $goods['examine_status'];
            }
        }

        $data['goods_name'] = I('post.goods_name');
        $data['small_name'] = I('post.small_name');
        $data['spec_type'] = empty(I('post.spec_type'))? 1 : I('post.spec_type');
        $data['gc_id'] = empty(I('post.gc_id')) ? 0 : I('post.gc_id');//商品分类id
        $data['gc_id_path'] = empty(I('post.gc_id_path')) ? 0 : I('post.gc_id_path');//商品分类id_path
        $data['brand_id'] = empty(I('post.brand_id')) ? 0 : I('post.brand_id');//商品分类id
        $data['market_price'] = empty(I('post.market_price')) ? 0: I('post.market_price');//市场价
        $data['shop_price'] = empty(I('post.shop_price')) ? 0 : I('post.shop_price');//本店售价
        $data['cost_price'] = empty(I('post.cost_price')) ? 0 : I('post.cost_price');//成本价
        $data['max_buy'] = empty(I('post.max_buy')) ? 0 : I('post.max_buy');//最大购买量
        $data['goods_desc'] = I('post.goods_desc');//商品描述
        $data['goods_weight'] = empty(I('post.goods_weight')) ? 0 : I('post.goods_weight');//商品种类
        $data['is_on_sale'] = I('post.is_on_sale',0);//是否上架
        $data['freight_type'] = I('post.freight_type');//运费计算方式
        $data['freight'] = empty(I('post.freight'))? 0 : I('post.freight');//运费
        $data['keywords'] = I('post.keywords');//seo关键字
        $data['description'] = I('post.description');//seo描述
        $data['mid'] = empty(I('post.mid')) ? 0 : I('post.mid');//商品模型id
        $data['store_id'] = I('post.store_id');//商品模型id
        $data['store_tuijian'] = I('post.store_tuijian');//店铺推荐
        $data['store_class_id'] = I('post.store_class_id');//店铺中分类
        $data['promote_price'] = empty(I('post.promote_price')) ? 0 : I('post.promote_price');//促销价格
        $data['consumption_type'] = I('post.consumption_type');
        $data['yjt_can'] =  I('post.yjt_can');//可使用的一卷通数量
        $data['gwq_can'] = I('post.gwq_can');//可使用的购物券数量
        $data['gwq_send'] =  I('post.gwq_send');//可使用的购物券数量
        $data['gwq_extra'] =  I('post.gwq_extra');//可使用的购物券数量
        $data['give_integral'] = I('post.give_integral');//赠送多少积分
        $data['promote_price'] = empty(I('post.promote_price')) ? 0 : I('post.promote_price');//促销价格

        $data['stock'] = empty(I('post.stock')) ? 0 : I('post.stock');//库存
        $data['is_cash'] = isset($_POST['is_cash']) ? 1: 0;//是否允许现金
        $data['is_yqt'] =  isset($_POST['is_yqt']) ? 1: 0;//是否允许一券通
        $data['is_gwq'] = isset($_POST['is_gwq']) ? 1: 0;//是否允许购物券
        $data['love_amount'] = $_POST['love_amount'] ? floatval($_POST['love_amount']): 0;// 捐赠的数额
        //处理促销时间
        $promote_start_date = empty(I('post.promote_start_date')) ? 0 : I('post.promote_start_date');
        $promote_end_date = empty(I('post.promote_end_date'))? 0 : I('post.promote_end_date');
        $data['promote_start_date'] = $promote_start_date == 0 ? 0 : strtotime($promote_start_date);//促销开始时间
        $data['promote_end_date'] = $promote_end_date == 0 ? 0 : strtotime($promote_end_date);//促销结束时间
        //处理上传图片
        if($_FILES['goods_img']){
            $_FILES['goods_img'] = $this->unsetFile($_FILES['goods_img'], I('post.unset_file'));
            if(!empty($_FILES['goods_img'])){
                $res = Image::upload('goods_img', 'MALL_SELLER', true, I('post.store_id').'/Goods/');
                if($res){
                    foreach ($res as $item){
                        $data['goods_img'][] = array(
                            'save_name' => $item['savename'],
                            'save_path' => $item['savepath'],
                        );
                    }
                }
            }
        }
        //规格信息
        //如果是单一规格删除所有规格
        if($data['spec_type']==1){
            M('GoodsSpec')->where('goods_id = '.$goods_id)->delete();
        }else{
            $spec_info = $this->goodsSpec();
            if(!empty($spec_info['goods_spec'])){
                $data['goods_spec'] = $spec_info['goods_spec'];
                $data['goods_extend'] = $spec_info['goods_extend'];
            }else{
                $data['spec_type']=1;
            }
        }
        $Goods = D('Goods');
        if ($examine) {
            if ($examine == '3') {
                $data['examine_status'] = 2;
            } else {
                $data['examine_status'] = $examine;
            }
        } else {
            $data['examine_status'] =  $examine == 0 ? 2 : 1; //处理审核状态
        }

        if($goods_id){
            $data['goods_id'] = $goods_id;
            //删除扩展信息
            M('GoodsExtend')->where('goods_id = '.$goods_id)->delete();
            $spec_id_remove = I('post.spec_id_remvoe');
            //判断是否存需要删除的规格
            if(!empty($spec_id_remove)){
                M('GoodsSpec')->where(array('spec_id'=>array('in',$spec_id_remove)))->delete();
            }

            //更新
            if(is_numeric($Goods->relation(true)->save($data))){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }else{
            $data['goods_sn'] = 'MS_'.serialNumber(); //商品货号
            if($Goods->relation(true)->add($data)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }
    function appraisal()
    {
        $evaluate=M("evaluate_goods","ms_mall_");
        if(I("get.status")&&I("get.status")<>"99")
        {
            $condition["status"]=I("get.status");

        }
        $this->assign("status",I("get.status"));
        $list=page($evaluate,$condition,10);
        $goodsids='';
        $memberids='';
        $storeids="";
        foreach($list["list"] as $key=>$val)
        {
            $goodsids.=$val["goods_id"].",";
            $memberids.=$val["uid"].",";
            $storeids.=$val["store_id"].",";
        }
        $goodsids=rtrim($goodsids,",");
        $memberids=rtrim($memberids,",");
        $storeids=rtrim($storeids,",");
        $goodslist=M("goods","ms_mall_")->where(["goods_id"=>["in",$goodsids]])->select();
        $memberlist=M("member","ms_common_")->where(["uid"=>["in",$memberids]])->select();
        $storelist=M("store","ms_mall_")->where(["store_id"=>["in",$storeids]])->select();
        foreach($list["list"] as $key=>$val)
        {
            foreach($goodslist as $gv)
            {
                if($val["goods_id"]==$gv["goods_id"])
                {
                    $list["list"][$key]["goods_name"]=$gv["goods_name"];
                }
            }
            foreach($memberlist as $mv)
            {
             //   $tempname="";
                if($val["uid"]==$mv["uid"])
                {
                  //  $tempname=ltrim($mv["username"],"u");
                   // if($this->isMobileNum($tempname))
//                    {
//                        $tempname="u".substr($tempname,0,3)."***".substr($tempname,8,3);
//                    }else{
                        $tempname=$mv["username"];
                   // }
                    $list["list"][$key]["username"]=$tempname;
                }
            }
            foreach($storelist as $sv)
            {
                if($val["store_id"]==$sv["store_id"])
                {
                    $list["list"][$key]["store_name"]=$sv["store_name"];
                }
            }
        }

        $this->assign("list",$list["list"]);
        $this->assign("page",$list["page"]);
        $this->display();
    }
    public function appraisal_check()
    {
        if(M("evaluate_goods","ms_mall_")->where(["gid"=>I("get.id")])->save(["status"=>I("get.status")]))
        {
            $this->success("操作成功");
        }else{
            $this->error("操作失败");
      }
    }
}

