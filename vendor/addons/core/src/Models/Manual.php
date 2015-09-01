<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\ManualHistories;
class Manual extends Model {

	//不能批量赋值
	public $auto_cache = true;
	public $fire_caches = [];


	protected $guarded = ['id'];

	function histories()
	{
		return $this->hasMany('Addons\\Core\\Models\\Manual');
	}
}

Manual::updating(function($manual){
	$data = Manual::find($manual->id)->toArray();
	$data['mid'] = $data['id'];
	$data = array_keyfilter($data, 'title,content,mid'); 
	ManualHistories::create($data);
	return true;
})