<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
class ManualHistory extends Model {

	//不能批量赋值
	public $auto_cache = true;
	public $fire_caches = [];


	protected $guarded = ['id'];

	public function restore()
	{
		Manual::findOrFail($this->mid)->update([
			'title' => $this->title,
			'content' => $this->content,
		]);
	}
}