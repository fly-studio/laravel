<?php namespace Addons\Core\Models;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Zizaco\Entrust\Contracts\EntrustPermissionInterface;
use Zizaco\Entrust\Traits\EntrustPermissionTrait;
use Addons\Core\Models\Model;
use Illuminate\Support\Facades\Config;

class Permission extends Model implements EntrustPermissionInterface
{
	use EntrustPermissionTrait;

	public $auto_cache = true;
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
		$this->table = Config::get('entrust.permissions_table');
	}

}
