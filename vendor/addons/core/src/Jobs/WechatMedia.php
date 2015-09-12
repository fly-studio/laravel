<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Addons\Core\Models\WechatMedia as WechatMediaModel;
use Addons\Core\Models\Wechat\Attachment;
class WechatMedia extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $mediaID;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->mediaID = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $media = WechatMediaModel::find($this->mediaID);
        if (empty($media))
            return false;
        $message = $media->message;
        $account = $message->account;
        $attachment = new Attachment($account->toArray(), $account->getKey());

        if (empty($media->aid))
        {
            $a = $attachment->downloadByMediaID($media->media_id, $media->format, $message->type == $message::TYPE_VIDEO);
            !empty($a) && $media->aid = $a->getKey();
        }
        
        if (empty($media->thumb_aid) && !empty($media->thumb_media_id))
        {
            $a = $attachment->downloadByMediaID($media->thumb_media_id, 'jpg');
            !empty($a) && $media->thumb_aid = $a->getKey();
        }
        $media->save();
    }
}