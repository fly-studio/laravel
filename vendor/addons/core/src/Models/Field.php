<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\FieldTrait;
use Cache;
class Field extends Model {
	use FieldTrait;
	//不能批量赋值
	public $auto_cache = true;
	public $fire_caches = ['fields'];

	public $casts = [
		'extra' => 'array',
	];


	protected $guarded = [];

	public function exists($id, $field_class)
	{
		return $this->where('id', $id)->where('field_class', $field_class)->count() > 0;
	}

	public function getFields()
	{
		return $this->rememberCache('fields', function(){
			$result = [];
			$all = $this->orderBy('order_index','ASC')->get();
			foreach($all as $v)
				$result[$v['field_class']][$v['id']] = array_keyfilter($v->toArray(), 'id,title,extra');
			return $result;
		});
	}
}
