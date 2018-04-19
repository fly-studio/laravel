<?php

namespace Addons\Entrust\Tests\Models;

use Addons\Entrust\Models\Permission as Base;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Base
{
    use SoftDeletes;

    protected $guarded = [];
}
