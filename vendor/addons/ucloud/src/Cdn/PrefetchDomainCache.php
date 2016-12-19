<?php
namespace Addons\Ucloud\Cdn;

use Addons\Ucloud\Factory;
class PrefetchDomainCache {
	private $factory;
	private $urls = [];
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	public function addUrl($url)
	{
		if (count($this->urls) >= 30) return false;
		$this->urls[] = $url;
		return $this;
	}

	public function handle()
	{
		$params = [];
		foreach($this->urls as $key => $url)
			$params['UrlList.'. $key] = $url;

		$result = $this->factory->http_get('PrefetchDomainCache', $params);
		return isset($result['RetCode']) && $result['RetCode'] == 0 ? $result['TaskId'] : false;
	}
}