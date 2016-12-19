<?php
namespace Addons\Ucloud;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
class Factory {
	private $config = [];
	private $app;

	public function __construct($app, $configDriver = 'default') {
		$this->app = $app;
		$this->config = config('ucloud.'.$configDriver);
	}

	public function buildParams($params)
	{
		$params['DomainId'] = $this->config['domain_id'];
		$params['PublicKey'] = $this->config['public_key'];
		ksort($params);
		$params['Signature'] = $this->genericSignature($params);
		return $params;
	}

	protected function genericSignature($params)
	{
		ksort($params);
		# 参数串排序
		$params_data = '';

		foreach($params as $key => $value) {
			$params_data .= $key;
			$params_data .= $value;
		}

		$params_data .= $this->config['private_key'];
		return sha1($params_data);
		# 生成的Signature值
	}

	public function setConfig($config)
	{
		$this->config = $config;
	}

	public function getConfig() 
	{
		return $this->config;
	}

	public function http_get($action, $params = [])
	{
		$params['Action'] = $action;
		$params = $this->buildParams($params);

		$stack = HandlerStack::create();
		$stack->push(
			Middleware::log(
				app('log'),
				new MessageFormatter('GuzzleHttp {uri}'.PHP_EOL.PHP_EOL.'{request}'.PHP_EOL.PHP_EOL.'{response}'.PHP_EOL.PHP_EOL.'{error}')
			)
		);
		$client = new \GuzzleHttp\Client([
			'handler' => $stack,
			'verify' => false,
			'timeout' => 20,
		]);
		for($i = 0; $i <= 5; ++$i)
		{
			try {
				$res = $client->get($this->config['url'], ['query' => $params]);
				if ($res->getStatusCode() == 200)
				{
					$result = json_decode($res->getBody(), true);
					if ($result !== null)
						return $result;
				}
			} catch (RequestException $e) {
				logger($e->getMessage());
			} catch (ClientException $e) {
				logger($e->getMessage());
			}
			
			usleep(500);
		}
		return false;
	}

	public function make($action)
	{
		return $this->app->make($action);
	}

}