<?php

namespace Addons\Entrust\Tests\Models;

use Addons\Entrust\Models\Team as Base;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Base
{
    use SoftDeletes;

    protected $guarded = [];
}
