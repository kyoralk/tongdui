<?php
/**
 * Created by PhpStorm.
 * User: Kyoralk
 * Date: 2017/3/7
 * Time: 10:34
 */

namespace General\Controller;


class PaymentController
{
    public $payUrl;
    public $returnUrl;
    public $notifyUrl;
    public $paycode;
    public $config;
    public static $SSLCERT_PATH;
    public static $CURL_PROXY_HOST;
    public static $CURL_PROXY_PORT;
    public static $SSLKEY_PATH;

    /**
     * 初始化
     * param ['config'=>['key'=>'value','key2'=>'value2'],'order'=>['item'=>'value','item2'=>'value2']]
     * @param type $param
     */
    public function __construct($param = []) {
        $this->getConfig($param['config']);
    }

    public function getConfig($config = []) {
        $this->config = $config;
    }

    public function getCode() {

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
            throw new \yii\web\HttpException(500,"xml数据异常！");
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
            throw new \yii\web\HttpException(500,"curl出错，错误码:$error");
        }
    }

    /**
     * 将数组拼接为参数字符串
     * @param type $param
     * @return string
     */
    public static function getUrlParam() {
        return TRUE;
    }

    /**
     * 回调方法
     * @return boolean
     */
    public function notify(){
        return TRUE;
    }

    /**
     * 记录日志
     * @param type $content
     * @param type $file
     */
    public function log($content,$file=''){
        if(!$file){
            $file = Yii::$app->runtimePath.'/logs/pay.log';
        }
        $res = fopen($file, 'a+');
        fwrite($res, date("Y-m-d H:i:s") .' '.$content."\r\n");
        fclose($res);
    }


}