<?php

namespace Addons\Core\Http\Response;

use Illuminate\Support\Arr;
use Addons\Core\Tools\OutputEncrypt;
use Addons\Core\Contracts\Protobufable;
use Addons\Core\Http\Response\TextResponse;

class ApiResponse extends TextResponse implements Protobufable {

	protected $result = 'api';
	private $encrypted = false;

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

	public function setData($data, $rsaPublicKey = false)
	{
		if (!empty($rsaPublicKey))
		{
			$rsaPublicKey = is_string($rsaPublicKey) ? $rsaPublicKey : urldecode(request()->header('X-RSA'));

			$encryptor = new OutputEncrypt($rsaPublicKey);

			$encrypted = [
				'iv' => '',
				'mac' => '',
				'key' => $encryptor->generateAesKey()
			];

			$encoded = $encryptor->encode(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR));

			$encrypted['iv'] = $encoded['iv'];
			$encrypted['mac'] = $encoded['mac'];

			$this->encrypted = $encryptor->encodeByPublic(
				json_encode(
					array_map(function($v) {
						return base64_encode($v);
					}, $encrypted),
					JSON_PARTIAL_OUTPUT_ON_ERROR
				)
			);

			$this->data = !empty($this->encrypted) ? $encoded['value'] : null; //如果无法加密成功，则不用返回数据，避免浪费传输

		} else {
			$this->encrypted = null;

			$this->data = json_decode(json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR), true); //turn Object to Array
		}

		return $this;
	}

	public function getEncrypted()
	{
		return $this->encrypted;
	}

	public function getOutputData()
	{
		$encrypted = $this->getEncrypted();
		$data = Arr::except(parent::getOutputData(), ['tipType', 'message']);

		if (!empty($encrypted))
			return ['encrypted' => base64_encode($encrypted), 'data' => base64_encode($data['data'])] + $data;

		return $data;
	}

	public function toProtobuf(): \Google\Protobuf\Internal\Message
	{
		$message = parent::toProtobuf();

		if (!empty($this->getEncrypted()))
			$message->setEncrypted($this->getEncrypted());

		return $message;
	}

}
