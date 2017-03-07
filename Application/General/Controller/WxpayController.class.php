<?php
/**
 * Created by PhpStorm.
 * User: Kyoralk
 * Date: 2017/3/7
 * Time: 10:03
 */

namespace General\Controller;


class WxpayController
{
    public $appId = 'wxd3c051e2c1463b40';
    public $mchId = '1397387202';
    public $payUrl;
    public $returnUrl;
    public $notifyUrl;
    public $paycode;
    public $config;
    public static $SSLCERT_PATH;
    public static $CURL_PROXY_HOST;
    public static $CURL_PROXY_PORT;
    public static $SSLKEY_PATH;
    public $key = '825cb27cdd2762314dbd4c77d7794241';

    public function query() {
        //获取access token
//        $wxAccessToken = Yii::$app->fileCache->get('wxAccessToken');
//        if (!$wxAccessToken) {
//            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
//            $content = Tools::object_array(json_decode(file_get_contents($url)));
//            $wxAccessToken = $content['access_token'];
//            Yii::$app->fileCache->set('wxAccessToken', $wxAccessToken, 7200);
//        }
        $out_trade_no = I('get.out_trade_no');
        $url = "https://api.mch.weixin.qq.com/pay/orderquery" ;
        //检测必填参数
        $this->setValue('out_trade_no', $out_trade_no);
        $this->setValue('appid', $this->appId);
        $this->setValue('mch_id', $this->mchId);
        $this->setValue('nonce_str', $this->mkNonceStr());
        $this->setValue('sign', $this->MakeSign()); //签名
//        var_dump($this->GetValues());exit;
        $xml = $this->ToXml($this->GetValues());
        //解析结果
        $this->clearValues();
        $response = $this->FromXml($this->postXmlCurl($xml, $url, false));

        if (isset($response['err_code'])) {
            if ($response['err_code'] == 'ORDERNOTEXIST') {
                echo '此交易订单号不存在';
            } else if ($response['err_code'] == 'SYSTEMERROR') {
                echo '系统错误';
            } else {
                echo '其他错误';
            }
        } else {
            if ($response['trade_state'] == 'SUCCESS') {
                echo '订单已支付'.($response['cash_fee']/100)."元";
            } else if ($response['trade_state'] == 'NOTPAY') {
                echo '订单未支付';
            }
        }
    }

    /**
     *
     * @param type $prepay_id
     */
    public function getJsApiParam($prepay_id) {
        $this->clearValues();
        $this->setValue('appId', $this->appId);
        $this->setValue('nonceStr', $this->mkNonceStr());
        $this->setValue('timeStamp', time());
        $this->setValue('package', "prepay_id=" . $prepay_id);
        $this->setValue('signType', 'MD5'); //固定使用MD5加密，旧接口中使用sha1
        $this->setValue('paySign', $this->MakeSign());

        return $this->GetValues();
    }

    /**
     * 清空values的值
     */
    public function clearValues() {
        $this->values = [];
    }

    /**
     * 创建随机字符
     * @param type $lengh
     * @return int
     */
    public function mkNonceStr($lengh = 16) {
        return microtime() * 1000000 + rand(1, 9999);
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams() {
        ksort($this->values);
        $buff = "";
        foreach ($this->values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v) && $k != 'key') {
                $buff .= $k . "=" . $v . "&";
            }
        }
        return trim($buff, "&");
    }

    /**
     * 获取指定配置项
     * @param type $name
     * @return type
     */
    public function getValue($name) {
        return $this->values[$name];
    }

    /**
     * 设置相关选项值
     * @param type $name
     * @param type $value
     */
    public function setValue($name, $value) {
        $this->values[$name] = $value;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign() {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 获取设置的值
     */
    public function GetValues() {
        return $this->values;
    }

    /**
     * 获取jsApi授权验证相关参数
     * @return type
     */
    public function getJsInitPram() {
        $this->clearValues();
        $this->setValue('noncestr', $this->mkNonceStr());
        $this->setValue('timestamp', time());
        $this->setValue('jsapi_ticket', $this->getJsTicket());
        $this->setValue('url', Yii::$app->request->getHostInfo() . Yii::$app->request->getUrl());
        $this->setValue('signature', $this->makeJsInitSign());
        $this->setValue('appId', $this->appId);
        return $this->GetValues();
    }

    /**
     * 获取jsTicket
     * @return type
     */
    public function getJsTicket() {
        $jsapiTicket = Yii::$app->fileCache->get('jsapiTicket');
        if (!$jsapiTicket) {
            $wxAccessToken = Yii::$app->fileCache->get('wxAccessToken');
            if (!$wxAccessToken) {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
                $content = Tools::object_array(json_decode(file_get_contents($url)));
                $wxAccessToken = $content['access_token'];
                Yii::$app->fileCache->set('wxAccessToken', $wxAccessToken, 7200);
            }
            if ($wxAccessToken) {//根据access_token获取jsapiticket
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$wxAccessToken}&type=jsapi";
                $jsContent = Tools::object_array(json_decode(file_get_contents($url)));
                if ($jsContent['errcode'] == 0) {
                    Yii::$app->fileCache->set('jsapiTicket', $jsContent, $jsContent['expires_in']);
                    $jsapiTicket = $jsContent;
                }
            }
        }
        return $jsapiTicket['ticket'];
    }

    /**
     * 生成JS权限验证签名
     * @return type
     */
    public function makeJsInitSign() {
        $str = $this->ToUrlParams();
        return sha1($str);
    }

    /**
     * 检测是否设置某项值
     * @param type $name
     * @return type
     */
    public function isValue($name) {
        return array_key_exists($name, $this->GetValues());
    }

    /**
     * 检测签名
     * @return boolean
     */
    public function checkSign() {
        if (!$this->isValue('sign')) {
            return true;
        }
        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            return true;
        } else {
            return FALSE;
        }
    }

    public function GetSign(){
        return $this->getValue('sign');
    }

    public function refund($param) {
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $this->clearValues();
        //设置相关参数
        $this->setValue('appid', $this->appId);
        $this->setValue('mch_id', $this->mchId);
        $this->setValue('device_info', $param['device_info'] ? $param['device_info'] : 'WEB');
        $this->setValue('nonce_str', $this->mkNonceStr());
        //$this->setValue('transaction_id', $param['transaction_id']);
        $this->setValue('out_trade_no', $param['out_trade_no']);
        $this->setValue('out_refund_no', $param['out_refund_no']);
        $this->setValue('total_fee', $param['total_fee']);
        $this->setValue('refund_fee', $param['refund_fee']);
        $this->setValue('refund_fee_type', $param['refund_fee_type'] ? $param['refund_fee_type'] : 'CNY');
        $this->setValue('op_user_id', $this->mchId);
        $this->setValue('sign', $this->MakeSign());
        //转化为xml
        $xml = $this->ToXml($this->GetValues());
        //发送到url,将微信服务器的数据返回
        return $this->FromXml($this->postXmlCurl($xml, $url));
    }

    /**
     * 生成xml字符
     * @throws WxPayException
     * */
    public function ToXml($arr) {
        if ($arr && is_array($arr)) {
            $xml = "<xml>";
            foreach ($arr as $key => $val) {
                if (is_numeric($val)) {
                    $xml.="<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $xml.="<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                }
            }
            $xml.="</xml>";
        }
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public static function FromXml($xml) {
        if (!$xml) {
            throw new HttpException(500,"xml数据异常！");
        }
        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 以post方式提交xml到对应的接口url
     * Enter description here ...
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($xml, $url, $useCert = false, $second = 50) {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        if (self::$CURL_PROXY_HOST != "0.0.0.0" && self::$CURL_PROXY_PORT != 0) {
            curl_setopt($ch, CURLOPT_PROXY, self::$CURL_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, self::$CURL_PROXY_PORT);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, self::$SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, self::$SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new HttpException(500,"curl出错，错误码:$error");
        }
    }

}