<?php
namespace Addons\Core\Tools;

use phpseclib\Crypt\RSA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Encryption\Encrypter;

class Encrypt {

	public static $keys;

	public function encode($data)
	{
		$keys = $this->getKeys();
		$e = new Encrypter($keys['key'], config('app.cipher'));
		return $e->encrypt($data);
	}

	public function getKeys()
	{
		if (!empty(static::$keys)) return static::$keys;

		static::$keys = session('encrypt');
		if (empty(static::$keys))
		{
			$rsa = new RSA();
			static::$keys = [
				'key' => (random_bytes(config('app.cipher') == 'AES-128-CBC' ? 16 : 32)),
			] + $rsa->createKey();
		}
		session(['encrypt' => static::$keys]);
		session()->save();
		return static::$keys;
	}



}