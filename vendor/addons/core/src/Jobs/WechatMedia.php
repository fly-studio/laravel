<?php

namespace Addons\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Addons\Core\Models\WechatMessageMedia;
use Addons\Core\Models\Wechat\Attachment;
class WechatMedia implements SelfHandling, ShouldQueue
{
    use Queueable;
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
        $media = WechatMessageMedia::find($this->mediaID);
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
