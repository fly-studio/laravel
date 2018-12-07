<?php

namespace Addons\Server\Protocols\TagV2;

use Addons\Server\Contracts\AbstractServer;

trait PackageOffsetTrait {

	public function bootPackageOffsetTrait(AbstractServer $server)
	{
		$server->set([
			'open_length_check' => true,
			'package_length_type' => 'N',
			'package_length_offset' => 6,
			'package_body_offset' => 10,
		]);
	}
}
