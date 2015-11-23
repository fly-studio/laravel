<?php

namespace Addons\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Addons\Core\Models\WechatQrcode as WechatQrcodeModel;
use Addons\Core\Tools\Wechat\API;
use Addons\Core\Tools\Wechat\Attachment;
class WechatQrcode implements SelfHandling, ShouldQueue
{
	use Queueable;
	use InteractsWithQueue, SerializesModels;

	public $qrcodeID;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($id)
	{
		$this->qrcodeID = $id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$qr = WechatQrcodeModel::find($this->qrcodeID);
		if (empty($qr))
			return false;
		$account = $qr->account;
		$attachment = new Attachment($account->toArray(), $account->getKey());

		if (empty($qr->aid))
		{
			$a = $attachment->downloadByTicket($qr->ticket);
			!empty($a) && $qr->aid = $a->getKey();
		}
		
		$qr->save();
	}
}
