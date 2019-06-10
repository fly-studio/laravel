<?php

namespace Addons\Server\Structs\Config;

use Illuminate\Support\Arr;
use Addons\Func\Contracts\MutatorTrait;
use Addons\Server\Structs\Config\Host;

class ServerConfig {

	use MutatorTrait;

	protected $host;

	public $daemon = false;
	public $worker_num = 1;
	public $task_worker_num = 1;
	public $max_request = 0;
	public $max_connection = null;
	public $user = null;
	public $group = null;
	public $sub_hosts = []; // array<Host>
	public $backlog = 128;
	public $heartbeat_check_interval = 60;
	public $heartbeat_idle_time = 60;

	//SSL
	public $ssl_cert_file = null;
	public $ssl_key_file = null;
	public $ssl_ciphers = 'EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5';//'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';
	public $ssl_method = SWOOLE_SSLv3_METHOD;

	//HTTP
	public $upload_tmp_dir = null;
	public $http_parse_post = true;
	public $http_parse_cookie = true;
	public $http_compression = true;
	public $package_max_length = 2 * 1024 * 1024;
	public $document_root = null;
	public $enable_static_handler = true;
	public $open_http2_protocol = false; // HTTP2
	public $static_handler_locations = ['/static/'];

	public function __construct(Host $host, array $config)
	{
		$this->initDefault();

		$this->host = $host;
		foreach (Arr::except($config, ['on']) as $key => $val)
			$this->{$key} = $val;
	}

	public static function build($host, int $port, int $protocol = SWOOLE_SOCK_TCP, array $config = [])
	{
		return new static(Host::build($host, $port, $protocol), $config);
	}

	protected function initDefault()
	{
		$processUser = posix_getpwuid(posix_geteuid());
		$this->user = $processUser['name'];
		$this->upload_tmp_dir = sys_get_temp_dir();
		$this->document_root = public_path();
	}
}
