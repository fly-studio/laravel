<?php

namespace Addons\Entrust\Traits;

use Illuminate\Support\Str;

trait HasEvents
{
    /**
     * Register an observer to the Addons\Entrust events.
     *
     * @param  object|string  $class
     * @return void
     */
    public static function entrustObserve($class)
    {
        $observables = [
            'roleAttached',
            'roleDetached',
            'permissionAttached',
            'permissionDetached',
            'roleSynced',
            'permissionSynced',
        ];

        $className = is_string($class) ? $class : get_class($class);

        foreach ($observables as $event) {
            if (method_exists($class, $event)) {
                static::registerEntrustEvent(Str::snake($event, '.'), $className.'@'.$event);
            }
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return mixed
     */
    protected function fireEntrustEvent($event, array $payload)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        return static::$dispatcher->dispatch(
            "entrust.{$event}: ".static::class,
            $payload
        );
    }

    /**
     * Register a entrust event with the dispatcher.
     *
     * @param  string  $event
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function registerEntrustEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen("entrust.{$event}: {$name}", $callback);
        }
    }

    /**
     * Register a role attached entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleAttached($callback)
    {
        static::registerEntrustEvent('role.attached', $callback);
    }

    /**
     * Register a role detached entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleDetached($callback)
    {
        static::registerEntrustEvent('role.detached', $callback);
    }

    /**
     * Register a permission attached entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionAttached($callback)
    {
        static::registerEntrustEvent('permission.attached', $callback);
    }

    /**
     * Register a permission detached entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionDetached($callback)
    {
        static::registerEntrustEvent('permission.detached', $callback);
    }

    /**
     * Register a role synced entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function roleSynced($callback)
    {
        static::registerEntrustEvent('role.synced', $callback);
    }

    /**
     * Register a permission synced entrust event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function permissionSynced($callback)
    {
        static::registerEntrustEvent('permission.synced', $callback);
    }
}
