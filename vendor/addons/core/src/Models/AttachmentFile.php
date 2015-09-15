<?php

namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttachmentFile extends Model{
	use SoftDeletes;

	protected $guarded = ['id'];
	
	public function attachments()
	{
		return $this->hasMany(get_namespace($this).'\\Attachment', 'afid', 'id');
	}

	public function get_byhash($hash, $size)
	{
		return $this->where('hash',$hash)->where('size',$size)->first();
	}

	public function get_bybasename($basename)
	{
		return $this->where('basename',$basename)->first();
	}

	public function get_byhash_path($hash_path)
	{
		return $this->where('path',$hash_path)->first();
	}

	public function get($id)
	{
		return $this->find($id)->toArray();
	}
}