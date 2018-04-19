<?php

namespace Addons\Entrust\Tests\Models;

use Addons\Entrust\Contracts\Ownable;

class OwnableObject implements Ownable
{
    public function ownerKey($owner)
    {
        return 1;
    }
}
