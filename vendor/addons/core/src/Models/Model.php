<?php
namespace Addons\Core\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

use Addons\Core\Models\CacheTrait;
use Cache;
class Model extends BaseModel {
	use CacheTrait;
	
	
}
