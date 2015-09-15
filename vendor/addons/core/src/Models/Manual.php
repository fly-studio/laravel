<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Tree;
use Addons\Core\Models\ManualHistory;
class Manual extends Tree {

	function histories()
	{
		return $this->hasMany(get_namespace($this).'\\ManualHistory');
	}
}

Manual::updating(function($manual){
	if ($manual->isDirty('title', 'content'))
	{
		$data = Manual::find($manual->id)->toArray();
		$data['mid'] = $data['id'];
		$data = array_keyfilter($data, 'title,content,mid'); 
		ManualHistory::create($data);
	}
});
