<?php

namespace Addons\Entrust\Models;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Addons\Core\Models\CacheTrait;
use Addons\Core\Models\BuilderTrait;
use Addons\Core\Models\PolyfillTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Addons\Entrust\Traits\PermissionTrait;
use Addons\Entrust\Contracts\PermissionInterface;

class Permission extends Model implements PermissionInterface
{
    use CacheTrait, BuilderTrait, PolyfillTrait;
    use PermissionTrait;

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
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('entrust.tables.permissions');
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
