<?php
namespace General\Controller;
use Think\Controller;
use Think\Model;
use General\Util\SMS;
class GeneralController extends Controller{
	
	protected function _initialize(){
		
	}
	/**
	 * 验证码
	 */
	public function verifyCode(){
		$config = array(
				'fontSize'=>empty(I('get.font_size'))?35:I('get.font_size'),// 验证码字体大小
				'length'=>empty(I('get.length'))?4:I('get.length'),     // 验证码位数
				'useNoise'=>false,// 关闭验证码杂点
				'fontttf'=>'5.ttf',
				'useCurve'=>false,//关闭曲线混淆
				'reset'=>false,
				 
		);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}
	/**
	 * 验证码检测
	 * @param unknown $code
	 * @param string $id
	 * @return boolean
	 */
	protected function checkVerify($code, $id = ''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}
	/**
	 * 判断是否是手机访问
	 */
	protected function isMobile(){
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
			return true;
			//此条摘自TPM智能切换模板引擎，适合TPM开发
			if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
				return true;
				//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
				if (isset ($_SERVER['HTTP_VIA']))
					//找不到为flase,否则为true
					return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
					//判断手机发送的客户端标志,兼容性有待提高
					if (isset ($_SERVER['HTTP_USER_AGENT'])) {
						$clientkeywords = array(
								'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
						);
						//从HTTP_USER_AGENT中查找手机浏览器的关键字
						if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
							return true;
						}
					}
					//协议法，因为有可能不准确，放到最后判断
					if (isset ($_SERVER['HTTP_ACCEPT'])) {
						// 如果只支持wml并且不支持html那一定是移动设备
						// 如果支持wml和html但是wml在html之前则是移动设备
						if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
							return true;
						}
					}
					return false;
	}	
	/**
	 * 登陆检测
	 */
	protected function isLogin(){
		if(session('?uid')){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 格式化返回数据
	 */
	protected function formatReturnData($result,$info='',$status=1){
		return array(
				'result'=>$result,
				'info'=>$info,
				'status'=>$status
		);
	}
	/**
	 * 重新封装系统提供的ajaxReturn
	 */
	protected function ajaxJsonReturn($result,$info='',$status=1){
		$data = $this->formatReturnData($result,$info,$status);
		$this->ajaxReturn($data,'JSON');
	}
	/**
	 * 发送短信
	 * @param string $mobile
	 * @param int $id
	 */
	protected function sendSMS($mobile,$sign,$id = 0){
		$now_time = time();
		if(!session('?send_time') || $now_time - session('send_time')>=60){
			if(md5(C('SECRET_KEY').$mobile) == $sign){
				$tmpl = C('SMS_TMPL');
				switch ($id){
					case 0:
						$msg = str_replace('{$var}', session('sms_code',randstr(6,true)), $tmpl[$id]);
						break;
				}
				session('send_time',time());
				return SMS::send($mobile, $msg);
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	/**
	 * 检测验证码
	 * @param string $sms_code
	 */
	protected function checkSMSCode($sms_code){
		if($sms_code == session('sms_code')){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 检测手机号
	 * @param number $mobile
	 * @return boolean
	 */
	protected function isMobileNum($mobile) {
		if (!is_numeric($mobile)) {
			return false;
		}
		return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? false : true;
	}
	/**
	 * 批量更新
	 * @param string $table 表名格式__全部大写的表名__
	 * @param array $data 要修改的内容 格式 array('字段名'=>array(值1,值2,值3,值4,))
	 * @param array $condition 修改条件 array('字段名'=>array(值1,值2,值3,值4,))
	 * @return 更新的记录数
	 */
	protected function saveAll($table,$data,$condition,$add = 0){
		$condition_key = key($condition);
		if(empty($condition[$condition_key])){
			E('更新条件不能为空');
			exit();
		}
		$condition_str = implode(',', $condition[$condition_key]);
		$deep = 0;
		$count = count($data);
		$count -=1;
		$sql = 'UPDATE '.$table.' SET ';
		foreach ($data as $key=>$value){
			$sql .=$key.' = CASE '.$condition_key;
			$i = 0;
			foreach ($value as $item){
				$sql .=' WHEN '.$condition[$condition_key][$i].' THEN ';
				if(empty($item)){
					$sql.='""';
				}else{
					if(is_numeric($item)){
						$sql.= $item;
					}else{
						if($add){
							$sql.= $item;
						}else{
							$sql.= '"'.$item.'"';
						}
					}
				}
				$i++;
			}
			$sql .=' ELSE '.$key;
			if($deep<$count){
				$sql .=' END,';
			}else{
				$sql .=' END ';
			}
			$deep++;
		}
		$sql.=' WHERE '.$condition_key.' IN ('.$condition_str.')';
		$Model = new Model();
		$Model->execute($sql); 
		file_put_contents("sql.log", $sql."\r\n", FILE_APPEND);
// 		writeTXT($Model->getLastSql());
	}
	/**
	 * 获取分销中的各种配置
	 * @param int $type
	 */
	public function ruleConfig($type){
		$rc = require './Common/Conf/Rule/'.$type.'.php';
		return $rc;
	}
	/**
	 * 支付方式配置信息
	 * @param string $key
	 */
	protected function payConfig($key = ''){
		$config = require_once './Common/Conf/pay.php';
		if(empty($key)){
			return $config;
		}else{
			return $config[$key];
		}
	}
	/**
	 * 设置产品分类
	 */
	protected function setGoodsClass(){
		$class_list = M('GoodsClass',C('DB_PREFIX_MALL'))->field('gc_id,gc_name,gc_parent_id,gc_icon_img')->select();
		$goods_class['all'] = list_to_tree($class_list,'gc_id','gc_parent_id');
		foreach ($goods_class['all'] as $item){
			unset($item['_child']);
			$goods_class['root'][] = $item;
		}
		foreach ($goods_class['all'] as $item){
			$goods_class['_child'][$item['gc_id']] = $item['_child'];
		}
		F('goods_class',$goods_class);
		return $goods_class;
	}
	/**
	 * 验签
	 * @param array $params
	 * @param string $sign
	 */
	protected function verifySign($params,$sign){
		if($sign == md5(implode(',', $params))){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 商城设置
	 */
	protected function setting(){
		if(F('setting_1')){
			$set = F('setting_1');
		}else{
			$set_list = M('Setting',C('DB_PREFIX_C'))->where('item = 1 ')->select();
			foreach ($set_list as $item){
				$set[$item['item_key']] = $item['item_value'];
			}
			F('setting_1',$set);
		}
		return $set;
	}

    /**
     * 检测图片是否存在
     */
	public function checkpic() {
        $url = I('get.imgurl');
        if ($url[0] == '/') {
            $url = substr($url, 1);
        }

        if (!is_file($url)) {
            $res = [
                'error' => 1,
                'imgurl' => '/Uploads/no.jpg' ,
            ];
        } else {
            $res = [
                'error' => 0,
                'imgurl' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$url,
            ];
        }


        echo json_encode($res);
    }

    /**
     * 自动收货
     */
    public function autosure() {
        $set = M('Setting',C('DB_PREFIX_C'))->where('item_key = "autoday" ')->find();
        $day = $set['item_value'] * 86400;
        $orders = M("OrderInfo")->where('order_time <= '.(time() - $day).' and receipt_status != 1 and shipping_status = 1 and pay_status = 1 and refund_status !=1 ')->select();
        $amount = 0;
        if ($orders) {
            foreach ($orders as $order) {
                if(M('OrderInfo')->where('order_sn = "'.$order['order_sn'].'"')->setField('receipt_status',1)){
                    $orderGoods = M('OrderGoods')->where('order_sn = "'.$order['order_sn'].'"')->select();
                    if ($orderGoods) {
                        foreach ($orderGoods as $og) {
                            $goods = M("Goods")->where('goods_id ='.$og['goods_id'])->find();
                            if ($goods) {
                                // 一券通商品收货进行九代结算
                                if ($goods['consumption_type']== 2) {
                                    $amount+= $og['price'] * $og['prosum'];
                                }
                            }
                            if ($amount > 0)
                                R('Reward/jdjs',array($amount,'XFYJT'));
                        }

                    }
                }
            }
        }
    }
}