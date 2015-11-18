<?php
namespace Addons\Core\Tools\Wechat;

use Addons\Core\Tools\Wechat\API;
use Addons\Core\Models\Attachment as AttachmentModel;
class Attachment {

	private $api;

	public function __construct($options, $waid = NULL)
	{
		$this->api = $options instanceof API ? $options : new API($options, $waid);
	}

	public function downloadByMediaID($media_id, $ext, $is_video = FALSE)
	{
		$data = $this->api->getMedia($media_id, $is_video);
		if (!empty($data))
		{
			$file_path = tempnam(sys_get_temp_dir(),'');
			$fp = fopen($file_path,'wb+');
			fwrite($fp, $data);
			fclose($fp);

			return (new AttachmentModel)->savefile(0, $file_path, 'wechat-media-id-'.$media_id.','.date('Ymdhis').'.'.$ext);
		}
		else 
			throw new \Exception("Wechat [$media_id] download failure: [".$this->api->errCode."]". $this->api->errMsg);
			
		return NULL;
	}

	public function downloadByTicket($ticket)
	{
		$url = $this->api->getQRUrl($ticket);
		return (new AttachmentModel)->download(0, $url, 'wechat-qr-'.$ticket.','.date('Ymdhis').'.jpg');
	}
}