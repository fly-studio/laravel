<?php

namespace Addons\Core\Tools;

use phpseclib\Crypt\RSA;
use Addons\Core\Tools\Encrypter;

class OutputEncrypt {

	public static $key;
	private $public;
	private $private;

	public function __construct($publicKey, $privateKey = null)
	{
		$this->public = $publicKey;
		$this->private = $privateKey;
	}

	public function encodeByPublic(string $data)
	{
		$rsa = new RSA;
		$rsa->setPublicKey();
		$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
		$rsa->loadKey($this->public);

		$key = $rsa->encrypt($data);

		return $key ? $key : null;
	}

	public function encodeByPrivate(string $data)
	{
		$private = $this->getRsaPrivateKey();

		$rsa = new RSA;
		$rsa->setPrivateKey();
		$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
		$rsa->loadKey($private);

		$key = $rsa->encrypt($data);

		return $key ? $key : null;
	}

	public function encode(string $data)
	{
		$e = new Encrypter($this->generateAesKey(), config('app.cipher'));

		return $e->encrypt($data, false);
	}

	public function decode(string $value, string $iv, string $mac)
	{
		$e = new Encrypter($this->generateAesKey(), config('app.cipher'));

		return $e->decrypt(compact('value', 'iv', 'mac'), false);
	}

	public function generateAesKey($refresh = false)
	{
		static $aes;

		if (empty($aes) || $refresh)
			$aes = Encrypter::generateKey(config('app.cipher'));

		return $aes;
	}

	public function generateRsaKeys($refresh = true)
	{
		static $rsaKeys;

		if (empty($rsaKeys) || $refresh) {

			$rsa = new RSA;
			$rsaKeys = $rsa->createKey(2048);
		}

		return $rsaKeys;
	}

	public function getRsaPublicKey()
	{
		return $this->generateRsaKeys()['publickey'];
	}

	public function getRsaPrivateKey()
	{
		return $this->generateRsaKeys()['privatekey'];
	}

}
