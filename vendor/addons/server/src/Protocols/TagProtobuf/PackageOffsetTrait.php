<?php

namespace Addons\Server\Protocols\TagProtobuf;

use Addons\Server\Contracts\AbstractServer;

trait PackageOffsetTrait {

	public function bootPackageOffsetTrait(AbstractServer $server)
	{
		$server->set([
			'open_length_check' => true,
			'package_length_type' => 'N',
			'package_length_offset' => 2,
			'package_body_offset' => 6,
		]);
	}
}
