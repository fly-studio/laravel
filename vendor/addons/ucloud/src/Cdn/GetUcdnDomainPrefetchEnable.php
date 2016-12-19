<?php
namespace Addons\Ucloud\Cdn;

use Addons\Ucloud\Factory;
class GetUcdnDomainPrefetchEnable {
	private $factory;
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	public function handle()
	{
		$result = $this->factory->http_get('GetUcdnDomainPrefetchEnable');
		return isset($result['RetCode']) && $result['RetCode'] == 0 ? $result['Enable'] : false;
	}
}