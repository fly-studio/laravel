<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\Pay\BizPayUrl;
use Addons\Core\Tools\Wechat\Pay\CloseOrder;
use Addons\Core\Tools\Wechat\Pay\DownloadBill;
use Addons\Core\Tools\Wechat\Pay\MicroPay;
use Addons\Core\Tools\Wechat\Pay\NotifyReply;
use Addons\Core\Tools\Wechat\Pay\OrderQuery;
use Addons\Core\Tools\Wechat\Pay\Refund;
use Addons\Core\Tools\Wechat\Pay\RefundQuery;
use Addons\Core\Tools\Wechat\Pay\Report;
use Addons\Core\Tools\Wechat\Pay\Results;
use Addons\Core\Tools\Wechat\Pay\Reverse;
use Addons\Core\Tools\Wechat\Pay\ShortUrl;
use Addons\Core\Tools\Wechat\Pay\UnifiedOrder;

use Exception;

/**
 *
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 *
 */
class Pay
{

	protected $config;
	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	/**
	 *
	 * 统一下单，UnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param UnifiedOrder $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function unifiedOrder(UnifiedOrder $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		//检测必填参数
		if (! $input->isOutTradeNoSet()) {
			throw new Exception('缺少统一支付接口必填参数out_trade_no！');
		} elseif (! $input->isBodySet()) {
			throw new Exception('缺少统一支付接口必填参数body！');
		} elseif (! $input->isTotalFeeSet()) {
			throw new Exception('缺少统一支付接口必填参数total_fee！');
		} elseif (! $input->IsTrade_typeSet()) {
			throw new Exception('缺少统一支付接口必填参数trade_type！');
		}

		//关联参数
		if ($input->GetTrade_type() == 'JSAPI' && ! $input->IsOpenidSet()) {
			throw new Exception('统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！');
		}
		if ($input->GetTrade_type() == 'NATIVE' && ! $input->isProductIdSet()) {
			throw new Exception('统一支付接口中，缺少必填参数product_id！trade_type为NATIVE时，product_id为必填参数！');
		}

		//异步通知url未设置，则使用配置文件中的url
		if (! $input->IsNotify_urlSet()) {
			$input->SetNotify_url($this->api->notify_url); //异步通知url
		}

		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setSpbillCreateIp($_SERVER['REMOTE_ADDR']); //终端ip
		//$input->setSpbillCreateIp('1.1.1.1');
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		//签名
		$input->setSign($this->api->mchkey);
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 查询订单，OrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param OrderQuery $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function orderQuery(OrderQuery $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/orderquery';
		//检测必填参数
		if (! $input->isOutTradeNoSet() && ! $input->isTransactionIdSet()) {
			throw new Exception('订单查询接口中，out_trade_no、transaction_id至少填一个！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 关闭订单，CloseOrder中out_trade_no必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param CloseOrder $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function closeOrder(CloseOrder $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/closeorder';
		//检测必填参数
		if (! $input->isOutTradeNoSet()) {
			throw new Exception('订单查询接口中，out_trade_no必填！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 申请退款，Refund中out_trade_no、transaction_id至少填一个且
	 * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param Refund $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function refund(Refund $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
		//检测必填参数
		if (! $input->isOutTradeNoSet() && ! $input->isTransactionIdSet()) {
			throw new Exception('退款申请接口中，out_trade_no、transaction_id至少填一个！');
		} elseif (! $input->isOutRefundNoSet()) {
			throw new Exception('退款申请接口中，缺少必填参数out_refund_no！');
		} elseif (! $input->isTotalFeeSet()) {
			throw new Exception('退款申请接口中，缺少必填参数total_fee！');
		} elseif (! $input->isRefundFeeSet()) {
			throw new Exception('退款申请接口中，缺少必填参数refund_fee！');
		} elseif (! $input->isOpUserIdSet()) {
			throw new Exception('退款申请接口中，缺少必填参数op_user_id！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();
		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, true, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 查询退款
	 * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
	 * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
	 * RefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param RefundQuery $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function refundQuery(RefundQuery $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/refundquery';
		//检测必填参数
		if (! $input->isOutRefundNoSet() && ! $input->isOutTradeNoSet() && ! $input->isTransactionIdSet() && ! $input->isRefundIdSet()) {
			throw new Exception('退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 * 下载对账单，DownloadBill中bill_date为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param DownloadBill $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function downloadBill(DownloadBill $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/downloadbill';
		//检测必填参数
		if (! $input->isBillDateSet()) {
			throw new Exception('对账单接口中，缺少必填参数bill_date！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		if (substr($response, 0, 5) == '<xml>') {
			return '';
		}
		return $response;
	}

	/**
	 * 提交被扫支付API
	 * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
	 * 由商户收银台或者商户后台调用该接口发起支付。
	 * MicroPay中body、out_trade_no、total_fee、auth_code参数必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param MicroPay $input
	 * @param int $time_out
	 */
	public function micropay(MicroPay $input, $time_out = 10)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/micropay';
		//检测必填参数
		if (! $input->isBodySet()) {
			throw new Exception('提交被扫支付API接口中，缺少必填参数body！');
		} elseif (! $input->isOutTradeNoSet()) {
			throw new Exception('提交被扫支付API接口中，缺少必填参数out_trade_no！');
		} elseif (! $input->isTotalFeeSet()) {
			throw new Exception('提交被扫支付API接口中，缺少必填参数total_fee！');
		} elseif (! $input->isAuthCodeSet()) {
			throw new Exception('提交被扫支付API接口中，缺少必填参数auth_code！');
		}

		$input->setSpbillCreateIp($_SERVER['REMOTE_ADDR']); //终端ip
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串

		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 撤销订单API接口，Reverse中参数out_trade_no和transaction_id必须填写一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param Reverse $input
	 * @param int $time_out
	 * @throws Exception
	 */
	public function reverse(Reverse $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
		//检测必填参数
		if (! $input->isOutTradeNoSet() && ! $input->isTransactionIdSet()) {
			throw new Exception('撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！');
		}

		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, true, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 测速上报，该方法内部封装在report中，使用时请注意异常流程
	 * Report中interface_url、return_code、result_code、user_ip、execute_time_必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param Report $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function report(Report $input, $time_out = 1)
	{
		$url = 'https://api.mch.weixin.qq.com/payitil/report';
		//检测必填参数
		if (! $input->isInterfaceUrlSet()) {
			throw new Exception('接口URL，缺少必填参数interface_url！');
		}
		if (! $input->isReturnCodeSet()) {
			throw new Exception('返回状态码，缺少必填参数return_code！');
		}
		if (! $input->isResultCodeSet()) {
			throw new Exception('业务结果，缺少必填参数result_code！');
		}
		if (! $input->isUserIpSet()) {
			throw new Exception('访问接口IP，缺少必填参数user_ip！');
		}
		if (! $input->isExecuteTimeSet()) {
			throw new Exception('接口耗时，缺少必填参数execute_time_！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setUserIp($_SERVER['REMOTE_ADDR']); //终端ip
		$input->setTime(date('YmdHis')); //商户上报时间
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		return $response;
	}

	/**
	 *
	 * 生成二维码规则,模式一生成支付二维码
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param BizPayUrl $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function bizpayurl(BizPayUrl $input, $time_out = 6)
	{
		if (! $input->isProductIdSet()) {
			throw new Exception('生成二维码，缺少必填参数product_id！');
		}

		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setTimeStamp(time()); //时间戳
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名


		return $input->getValues();
	}

	/**
	 *
	 * 转换短链接
	 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
	 * 减小二维码数据量，提升扫描速度和精确度。
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param ShortUrl $input
	 * @param int $time_out
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function shorturl(ShortUrl $input, $time_out = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/tools/shorturl';
		//检测必填参数
		if (! $input->IsLong_urlSet()) {
			throw new Exception('需要转换的URL，签名用原串，传输需URL encode！');
		}
		$input->setAppid($this->api->appid); //公众账号ID
		$input->setMchId($this->api->mchid); //商户号
		$input->setSubMchId($this->api->sub_mch_id); //子商户号
		$input->setNonceStr($this->api->generateNonceStr()); //随机字符串


		$input->setSign($this->api->mchkey); //签名
		$xml = $input->toXml();

		$start_time_stamp = $this->getMillisecond(); //请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $time_out);
		$result = Results::Init($response, $this->api->mchkey);
		$this->reportCostTime($url, $start_time_stamp, $result); //上报请求花费时间


		return $result;
	}

	/**
	 *
	 * 支付结果通用通知
	 * @param function $callback
	 * 直接回调函数使用方法: notify(you_function);
	 * 回调类成员函数方法:notify(array($this, you_function));
	 * $callback  原型为：function function_name($data){}
	 */
	public function notify($callback, &$msg)
	{
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		//如果返回成功则验证签名
		try {
			$result = Results::Init($xml, $this->api->mchkey);
		} catch (Exception $e) {
			$msg = $e->errorMessage();
			return false;
		}

		return call_user_func($callback, $result);
	}

	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public function replyNotify($xml)
	{
		echo $xml;
	}

	/**
	 *
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 * @param string $usrl
	 * @param int $start_time_stamp
	 * @param array $data
	 */
	private function reportCostTime($url, $start_time_stamp, $data)
	{
		//如果不需要上报数据
		if (!$this->api->debug)
			return;
	
		//仅失败上报
		if (array_key_exists('return_code', $data) && $data['return_code'] == 'SUCCESS' && array_key_exists('result_code', $data) && $data['result_code'] == 'SUCCESS')
			return;
		

		//上报逻辑
		$endTimeStamp = $this->getMillisecond();
		$objInput = new Report();
		$objInput->setInterfaceUrl($url);
		$objInput->setExecuteTime($endTimeStamp - $start_time_stamp);
		//返回状态码
		if (array_key_exists('return_code', $data)) {
			$objInput->setReturnCode($data['return_code']);
		}
		//返回信息
		if (array_key_exists('return_msg', $data)) {
			$objInput->setReturnMsg($data['return_msg']);
		}
		//业务结果
		if (array_key_exists('result_code', $data)) {
			$objInput->setResultCode($data['result_code']);
		}
		//错误代码
		if (array_key_exists('err_code', $data)) {
			$objInput->setErrCode($data['err_code']);
		}
		//错误代码描述
		if (array_key_exists('err_code_des', $data)) {
			$objInput->setErrCodeDes($data['err_code_des']);
		}
		//商户订单号
		if (array_key_exists('out_trade_no', $data)) {
			$objInput->setOutTradeNo($data['out_trade_no']);
		}
		//设备号
		if (array_key_exists('device_info', $data)) {
			$objInput->setDeviceInfo($data['device_info']);
		}

		try {
			$this->report($objInput);
		} catch (Exception $e) {
			//不做任何处理
		}
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 *
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws Exception
	 */
	private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}

		if ($useCert == true) {
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $this->api->sslcert_path);
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $this->api->sslkey_path);
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
			$error = curl_error($ch);
			curl_close($ch);
			throw new Exception('curl出错：' . $error);
		}
	}

	/**
	 * 获取毫秒级别的时间戳
	 */
	private function getMillisecond()
	{
		//获取毫秒的时间戳
		return microtime(TRUE);
	}
}

