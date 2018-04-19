<?php

namespace Addons\Entrust\Tests\Models;

use Addons\Entrust\Models\Role as Base;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Base
{
    use SoftDeletes;

    protected $guarded = [];
}
