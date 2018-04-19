<?php

namespace Addons\Entrust\Tests\Models;

use Addons\Entrust\Traits\UserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use UserTrait;
    use SoftDeletes;

    protected $guarded = [];
}
