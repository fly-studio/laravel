<?php
namespace Addons\Core\Models\Wechat;

use Addons\Core\Models\Wechat\API;
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
		return NULL;
	}
}