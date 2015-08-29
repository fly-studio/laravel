<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Cache;
class Field extends Model {

	//不能批量赋值
	public $auto_cache = true;
	public $fire_caches = ['fields'];


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

//order_index自动加1
Field::creating(function($field){
	$_field = Field::where('field_class', $field->field_class)->orderBy('order_index','DESC')->first();
	$field->order_index  = empty($_field) ? 1 : $_field->order_index + 1;
});