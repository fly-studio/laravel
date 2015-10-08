<?php

namespace Addons\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Addons\Core\Tools\API;
use Addons\Core\Models\WechatAccount;
use Addons\Core\Models\WechatUser;
use Addons\Core\Models\WechatDepot;
use Addons\Core\Models\WechatTemplate;
use Addons\Core\Models\Attachment;

class WechatSend implements SelfHandling, ShouldQueue
{
	use Queueable;
	use InteractsWithQueue, SerializesModels;

	public $account;
	public $user;
	public $media;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(WechatAccount $account, WechatUser $user, $media)
	{
		$this->account = $account;
		$this->user = $user;
		$this->media = $media;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$api = new API($this->account->toArray(), $this->account->getKey());
		$data = [
			'touser' => $this->user->openid,
		];
		if ($this->media instanceof Attachment)
		{
			$type = $this->media->file_type();
			$type == 'audio' && $type = 'voice';
			$path = $this->media->_create_symlink();
			$media_id = $api->uploadMedia($path, $type);
			
			$data += [
				'msgtype' => $type,
				$type => ['media_id' => $media_id],
			];
			switch ($type) {
				case 'image':
				case 'voice':
					break;
				case 'video':
					$data[$type] += [
						'thumb_media_id' => '',
						'title' => $this->media->filename,
						'description' => $this->media->description,
					];
					break;
			}
			
			//图片、视频、
		} elseif ($this->media instanceof WechatDepot) { //素材
			$type = $this->media->type;

			

		} else { //String
			$data += [
				'msgtype' => 'text',
				'text' => ['content' => $media],
			];
		}
		return $api->sendCustomMessage($data);
	}
}
