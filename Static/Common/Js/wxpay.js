/**
 * 调用微信JS api 支付
 */
function jsApiCall(jsApiParameters,return_url) {
	WeixinJSBridge.invoke('getBrandWCPayRequest', jsApiParameters,
			function(res) {
				if (res.err_msg == 'get_brand_wcpay_request:ok') {
					// 支付成功后跳转的地址
					window.location.href = return_url;
					return;
				} else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
					alert('请尽快完成支付哦！');
				} else if (res.err_msg == 'get_brand_wcpay_request:fail') {
					alert('支付失败');
				} else {
					alert('意外错误');
				}
			});
}
function payfor(jsApiParameters,return_url) {
	alert(jsApiParameters);
	if (typeof WeixinJSBridge == "undefined") {
		if (document.addEventListener) {
			document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		} else if (document.attachEvent) {
			document.attachEvent('WeixinJSBridgeReady', jsApiCall);
			document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		}
	} else {
		jsApiCall(jsApiParameters,return_url);
	}
}
