<?php
namespace Addons\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */

use Addons\Entrust\Contracts\PermissionInterface;
use Addons\Entrust\Traits\PermissionTrait;
use Illuminate\Database\Eloquent\Model;
use Addons\Core\Models\CacheTrait;
use Addons\Core\Models\CallTrait;
use Addons\Core\Models\PolyfillTrait;

class Permission extends Model implements PermissionInterface
{
    use CacheTrait, CallTrait, PolyfillTrait;
    use PermissionTrait;
    public $fire_caches = ['roles'];
    public $guarded = ['id'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('entrust.permissions_table');
    }

    /**
     * import the methods's permission of resource's route
     * 
     * @example
     * Permission::import([
     *     'member' => '用户',
     *     'catalog' => '分类'
     * ]);
     *
     * see config/entrust.php - import_fields
     * 
     * @param  array  $permissions 
     * @return 
     */
    public static function import(array $permissions, $format = '{{name}}.{{key}}')
    {
        foreach ($permissions as $permission => $text)
            foreach((array)config('entrust.import_fields') as $key)
                static::create([
                    'name' => str_replace(['{{name}}', '{{key}}'], [$permission, $key], $format),
                    'display_name' => trans('permission.import.'.$key, compact('key', 'text', 'permission')),
                ]);
    }

}
