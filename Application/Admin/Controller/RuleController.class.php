<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
/**
 * 
* @名称: RuleController
* @描述: 会员各种参数设置
* @author 一壶茶水 zhangbin1126@126.com 
* @date 2016年8月8日 下午5:17:54
* @version V1.0
 */
class RuleController extends MallController{
	/**
	 * 九代分享赠送一卷通
	 */
	public function jdfxzsyjt(){
		for($i = 1;$i<=9;$i++){
			$temp_1 = null;
			$temp_2 = null;
			for($j = 1;$j<=3;$j++){
				$temp_1[] = $_POST['value_1_'.$i.'_'.$j];
				$temp_2[] = $_POST['value_2_'.$i.'_'.$j];
			}
			$config_1['D'.$i] = $temp_1;
			$config_2['D'.$i] = $temp_2;
		}
		$config_0 = array('TJGS'=>I('post.TJGS'),'GWQGDCZ'=>I('post.GWQGDCZ'));
		$data[] = array(
				'type'=>4,
				'name'=>'合格消费商升级条件',
				'bfb'=>1,
				'config'=>serialize($config_0),
		);
		$data[] = array(
				'type'=>4,
				'name'=>'一卷通购物赠送一卷通',
				'bfb'=>1,
				'config'=>serialize($config_1),
		);
		$data[] = array(
				'type'=>4,
				'name'=>'充值购物券赠送一卷通 ',
				'bfb'=>1,
				'config'=>serialize($config_2),
		);
		$Rule = M('Rule');
		$Rule->startTrans();
		try {
			$Rule->where('type = 4')->delete();
			$Rule->addAll($data);
		} catch (Exception $e) {
			$Rule->rollback();
			$this->error('设置失败');
			exit();
		}
		$Rule->commit();
		$this->createConf(array('SJTJ'=>$config_0,'XFYJT'=>$config_1,'CZGWQ'=>$config_2), 4);
		$this->success('设置成功');
	}
	public function duipeng(){
		$bobi = I('post.bobi');
		$config = array(
				array(
						array(
								'min_fee'=>I('post.min_fee_1'),
								'max_fee'=>I('post.max_fee_1'),
								'bi'=>I('post.bi_1'),
								'cap_type'=>I('post.cap_type_1'),
								'cap_fee'=>I('post.cap_fee_1')
						),
						array(
								'min_fee'=>I('post.min_fee_2'),
								'max_fee'=>I('post.max_fee_2'),
								'bi'=>I('post.bi_2'),
								'cap_type'=>I('post.cap_type_2'),
								'cap_fee'=>I('post.cap_fee_2')
						),
						array(
								'min_fee'=>I('post.min_fee_3'),
								'max_fee'=>I('post.max_fee_3'),
								'bi'=>I('post.bi_3'),
								'cap_type'=>I('post.cap_type_3'),
								'cap_fee'=>I('post.cap_fee_3')
						),
							
				),
				array(
						'DUIPENG'=>array(
								'open'=>I('post.open_d'),
								'bobi'=>array('1'=>$bobi[0],'2'=>$bobi[1],'3'=>$bobi[2],'4'=>$bobi[3]),
						),
				),
				array('JIANDIAN'=>array(
						'open'=>I('post.open_j'),
						'bobi'=>I('post.bobi_j'),
						'cap_type'=>I('post.cap_type_j'),
						'cap_fee'=>I('post.cap_fee_j'),
				)),
				array('CHONGXIAO'=>I('post.chongxiao')),
				array(
						'JF'=>array(
								'zzjf'=>I('post.zzjf'),
								'zcb'=>I('post.zcb'),
								'dcsf'=>I('post.dcsf'),
								'zzb'=>I('post.zzb'),
								'zzys'=>I('post.zzys'),
						)
						
				),
		);
		if(is_numeric(M('Rule')->where('type = 5')->setField('config',serialize($config)))){
			$this->createConf($config, 5);
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
		
	}
	public function tongyong(){
		$config_1=array(
				'GWQ'=>I('post.GWQ_1'),
				'YJT'=>I('post.YJT_1'),
				'CZ_GWQ_S_GWQ'=>I('post.CZ_GWQ_S_GWQ'),
		);
		$config_2=array(
				'JF'=>I('post.JF_2'),
				'YJT'=>I('post.YJT_2'),
				'TX_FEE'=>I('post.TX_FEE'),
				'TX_MIN'=>I('post.TX_MIN'),
				'TX_BASE'=>I('post.TX_BASE'),
		);
		$config_3=array(
				'YJT_H'=>I('post.YJT_H'),
				'GWQ_H'=>I('post.GWQ_H'),
				'FXZSYJT_H'=>I('post.FXZSYJT_H'),
				'HJS'=>I('post.HJS'),
				'TX_FEE_H'=>I('post.TX_FEE_H'),
		);
		$data[] = array(
				'type'=>1,
				'name'=>'充值 ',
				'bfb'=>1,
				'config'=>serialize($config_1),
		);
		$data[] = array(
				'type'=>1,
				'name'=>'提现 ',
				'bfb'=>1,
				'config'=>serialize($config_2),
		);
		$data[] = array(
				'type'=>1,
				'name'=>'提现 ',
				'bfb'=>1,
				'config'=>serialize($config_3),
		);
		M('Pond')->where('code = "HEIJIN"')->setField('total',I('post.heijin'));
		$Rule = M('Rule');
		$Rule->startTrans();
		try {
			$Rule->where('type = 1')->delete();
			$Rule->addAll($data);
		} catch (Exception $e) {
			$Rule->rollback();
			$this->error('设置失败');
			exit();
		}
		$Rule->commit();
		$this->createConf(array('CZ'=>$config_1,'TX'=>$config_2,'HJ'=>$config_3), 1);
		$this->success('设置成功');
		
	}
	public function dailishang(){
		$data = array(
				'type'=>6,
				'name'=>'代理商 ',
				'bfb'=>1,
				'config'=>serialize($_POST),
		);
		$Rule = M('Rule');
		$Rule->startTrans();
		try {
			$Rule->where('type = 6')->delete();
			$Rule->add($data);
		} catch (Exception $e) {
			$Rule->rollback();
			$this->error('设置失败');
			exit();
		}
		$Rule->commit();
		$this->createConf($_POST, 6);
		$this->success('设置成功');
	}
	/**
	 * 设置详情
	 */
	public function info(){
		$type = I('get.type');
		if(I('get.type')){
			$heijin = M('Pond')->where('code = "HEIJIN"')->getField('total');
			$this->assign('heijin',$heijin);
		}
		$list = M('Rule')->where('type = '.$type)->select();
		$count = count($list);
		for($i = 0;$i<$count;$i++){
			$list[$i]['config'] = unserialize($list[$i]['config']);
		}
		$this->assign('content_header','规则设置');
		if($type == 6){
			$list = $list[0]['config'];
		}
		$this->assign('list',$list);
		switch (I('get.type')){
			case 4:
				$this->display('jdfx');
				break;
			case 5:
				$this->display('duipeng');
				break;
			case 6:
				$this->display('dailishang');
				break;
			default:
				$this->display('tongyong');
		}
		
	}
	
	public function save(){
		$name = I('post.name');
		$type = I('post.type');
		$key = I('post.key');
		$value = I('post.value');
		$count = count($name);
		for($i=0;$i<$count;$i++){
			$config = array();
			$config_count = count($key[$i+1]);
			for($j = 0;$j<$config_count;$j++){
				$config[$key[$i+1][$j]] = $value[$i+1][$j];
			}
			$data[$i] = array(
					'name'=>$name[$i],
					'type'=>$type,
					'config'=>serialize($config),
			);
		}
		
		
		$Rule = M('Rule');
		$Rule->startTrans();
		try {
			$Rule->where('type = '.$type)->delete();
			$Rule->addAll($data);
		} catch (Exception $e) {
			$Rule->rollback();
			$this->error('保存失败');
			exit();
		}
		if($type == 1){
			$config = array();
			foreach ($data as $item){
				$config = array_merge($config,unserialize($item['config']));
			}
		}
		$this->createConf($config,$type);
		$Rule->commit();
		$this->success('保存成功');
	}
	
	
	/**
	 * 写入配置信息
	 * @param array $data
	 * @param int $type
	 */
	private function createConf($data,$type){
		$string = '<?php return '.var_export($data,true).';';
		$myfile = fopen('./Common/Conf/Rule/'.$type.'.php', "w") or die("Unable to open file!");
		fwrite($myfile, $string);
		fclose($myfile);
	}
	private function createConf2($data){
		
	}
}