<?php

namespace Addons\Core\Http\Response;

use Addons\Core\Tools\OutputEncrypt;
use Addons\Core\Http\Response\TextResponse;

class ApiResponse extends TextResponse {

	protected $result = 'api';
	private $encrypted = false;
	private $encryptedKey = null;
	protected $outputRaw = false;

	public function getFormatter()
	{
		if ($this->formatter == 'auto')
		{
			$request = app('request');
			$of = $request->input('of', null);
			if (!in_array($of, ['txt', 'text', 'json', 'xml', 'yaml']))
				$of = '';
			return $of;
		}
		return $this->formatter;
	}

	public function getMessage()
	{
		return null;
	}

	public function setData($data, $encrypted = false)
	{
		$this->encrypted = $encrypted;

		if ($encrypted)
		{
			$encrypt = new OutputEncrypt;
			$this->encryptedKey = $encrypt->getClientEncryptedKey();
			$this->data = empty($this->encryptedKey) ? null : $encrypt->encode(json_encode($data), false); //如果key不对,就不用耗费资源加密了
		} else {
			$this->data = json_decode(json_encode($data), true); //turn Object to Array
		}
		return $this;
	}

	public function getEncrypted()
	{
		return $this->encrypted;
	}

	public function getEncryptedKey()
	{
		return $this->encryptedKey;
	}

	public function getOutputData()
	{
		return ['encrypted' => $this->getEncrypted() ? ($this->getEncryptedKey() ?: true) : false]  + array_except(parent::getOutputData(), ['tipType', 'message']);
	}

}
