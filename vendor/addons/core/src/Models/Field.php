<?php
namespace Addons\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model {
	//不能批量赋值
	protected $guarded = [];

	public function exists($id, $field_class)
	{
		return $this->where('id', $id)->where('field_class', $field_class)->count() > 0;
	}

	public function getFields()
	{
		$result = [];
		$all = $this->orderBy('order_index','ASC')->get();
		foreach($all as $v)
			$result[$v['field_class']][$v['id']] = array_keyfilter($v->toArray(), 'id,text,extra');

		return $result;
	}
}

//order_index自动加1
Field::creating(function($field){
	$_field = Field::where('field_class', $field->field_class)->orderBy('order_index','DESC')->first();
	$field->order_index  = empty($_field) ? 1 : $_field->order_index + 1;
});