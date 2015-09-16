<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Tree;
use Addons\Core\Models\ManualHistory;
class Manual extends Tree {

	function histories()
	{
		return $this->hasMany(get_namespace($this).'\\ManualHistory', 'mid', 'id');
	}
}

Manual::updating(function($manual){
	if ($manual->isDirty('title', 'content'))
	{
		$data = Manual::find($manual->getKey(), ['title', 'content', 'id'])->toArray();
		$data['mid'] = $data['id'];unset($data['id']);
		ManualHistory::create($data);
	}
});
