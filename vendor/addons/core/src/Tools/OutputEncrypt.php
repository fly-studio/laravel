<?php

namespace Addons\Core\Tools;

use phpseclib\Crypt\RSA;
use Addons\Core\Tools\Encrypter;

class OutputEncrypt {

	private $publicKey;
	private $privateKey;
	private $aesKey;

	public function __construct($publicKey, $privateKey = null)
	{
		$this->publicKey = $publicKey;
		$this->privateKey = $privateKey;
	}

	public function encodeByPublic(string $value, string $aesKey = null)
	{
		$encoded = $this->encodeAesToRaw($value, $aesKey);

		$aesEncrypted = $this->makePublicRsa()->encrypt($encoded['aesBase64']);

		return [
			'aesEncrypted' => !empty($aesEncrypted) ? base64_encode($aesEncrypted) : null,
			'value' => !empty($aesEncrypted) ? base64_encode($encoded['value']) : null
		];
	}

	public function encodeByPrivate(string $value, string $aesKey = null)
	{
		$encoded = $this->encodeAesToRaw($value, $aesKey);

		$aesEncrypted = $this->makePrivateRsa()->encrypt($encoded['aesBase64']);

		return [
			'aesEncrypted' => !empty($aesEncrypted) ? base64_encode($aesEncrypted) : null,
			'value' => !empty($aesEncrypted) ? base64_encode($encoded['value']) : null
		];
	}

	public function decodeByPublic(string $value, string $aesEncrypted)
	{
		$decoded = $this->makePublicRsa()->decrypt(base64_decode($aesEncrypted));

		if (empty($decoded))
			return null;

		return $this->decodeAesFromRaw($value, $decoded);
	}

	public function decodeByPrivate(string $value, string $aesEncrypted)
	{
		$decoded = $this->makePrivateRsa()->decrypt(base64_decode($aesEncrypted));

		if (empty($decoded))
			return null;

		return $this->decodeAesFromRaw($value, $decoded);
	}

	private function makePrivateRsa()
	{
		$rsa = new RSA;
		$rsa->setPrivateKey();
		$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
		$rsa->loadKey($this->privateKey);

		return $rsa;
	}

	private function makePublicRsa()
	{
		$rsa = new RSA;
		$rsa->setPublicKey();
		$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
		$rsa->loadKey($this->publicKey);

		return $rsa;
	}

	public function encodeAes(string $value, string $aesKey = null)
	{
		$e = new Encrypter($aesKey ?? $this->aesKey, config('app.cipher'));

		return $e->encrypt($value, false);
	}

	public function decodeAes(string $value, string $iv, string $mac, string $aesKey = null)
	{
		$e = new Encrypter($aesKey ?? $this->aesKey, config('app.cipher'));

		return $e->decrypt(compact('value', 'iv', 'mac'), false);
	}

	private function encodeAesToRaw(string $value, string $aesKey = null)
	{
		$aes = [
			'iv' => '',
			'mac' => '',
			'key' => $aesKey ?? $this->generateAesKey()
		];

		$encoded = $this->encodeAes($value, $aes['key']);

		$aes['iv'] = $encoded['iv'];
		$aes['mac'] = $encoded['mac'];
		$aes['key'] = base64_encode($aes['key']);

		$aesBase64 = json_encode($aes, JSON_PARTIAL_OUTPUT_ON_ERROR);

		return ['aesBase64' => $aesBase64, 'value' => $encoded['value']];
	}

	private function decodeAesFromRaw(string $value, string $decoded)
	{
		$aes = json_decode($decoded, true);
		$aes['key'] = base64_decode($aes['key']);

		return $this->decodeAes(base64_decode($value), $aes['iv'], $aes['mac'], $aes['key']);
	}

	public function generateAesKey()
	{
		return $this->aesKey = Encrypter::generateKey(config('app.cipher'));
	}

	public function generateRsaKeys()
	{
		$rsa = new RSA;
		$rsaKeys = $rsa->createKey(2048);

		$this->publicKey = $rsaKeys['publickey'];
		$this->privateKey = $rsaKeys['privatekey'];

		return $rsaKeys;
	}

}
