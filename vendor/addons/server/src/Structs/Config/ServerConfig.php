<?php

namespace Addons\Server\Structs\Config;

use Addons\Func\Contracts\MutatorTrait;
use Addons\Server\Structs\Config\Listen;

class ServerConfig {

	use MutatorTrait;

	protected $listen;

	public $daemon = false;
	public $worker_num = 1;
	public $task_worker_num = 1;
	public $max_request = 0;
	public $max_connection = null;
	public $user = null;
	public $group = null;
	public $sub_listens = []; // array<Listen>
	public $backlog = 128;
	public $heartbeat_check_interval = 5;
	public $heartbeat_idle_time = 60;

	//SSL
	public $ssl_cert_file = null;
	public $ssl_ciphers = 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';
	public $ssl_method = SWOOLE_SSLv3_CLIENT_METHOD;

	//HTTP
	public $upload_tmp_dir = null;
	public $http_parse_post = true;
	public $package_max_length = 2 * 1024 * 1024;
	public $document_root = null;
	public $enable_static_handler = true;
	public $open_http2_protocol = false; // HTTP2

	public function __construct(Listen $listen, array $config)
	{
		$this->initDefault();

		$this->listen = $listen;
		foreach (array_except($config, ['listen']) as $key => $val)
			$this->{$key} = $val;
	}

	public static function build(Listen $listen, array $config)
	{
		return new static($listen, $config);
	}

	protected function initDefault()
	{
		$processUser = posix_getpwuid(posix_geteuid());
		$this->user = $processUser['name'];
		$this->upload_tmp_dir = sys_get_temp_dir();
	}
}
