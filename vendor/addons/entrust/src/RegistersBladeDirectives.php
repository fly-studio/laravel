<?php

namespace Addons\Entrust;

use Illuminate\Support\Facades\Blade;

/**
 * This class is the one in charge of registering
 * the blade directives making a difference
 * between the version 5.2 and 5.3
 */
class RegistersBladeDirectives
{
    /**
     * Handles the registration of the blades directives.
     *
     * @param  string  $laravelVersion
     * @return void
     */
    public function handle($laravelVersion = '5.3.0')
    {
        if (version_compare(strtolower($laravelVersion), '5.3.0-dev', '>=')) {
            $this->registerWithParenthesis();
        } else {
            $this->registerWithoutParenthesis();
        }

        $this->registerClosingDirectives();
    }

    /**
     * Registers the directives with parenthesis.
     *
     * @return void
     */
    protected function registerWithParenthesis()
    {
        // Call to Addons\Entrust::hasRole.
        Blade::directive('role', function ($expression) {
            return "<?php if (app('entrust')->hasRole({$expression})) : ?>";
        });

        // Call to Addons\Entrust::can.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('entrust')->can({$expression})) : ?>";
        });

        // Call to Addons\Entrust::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('entrust')->ability({$expression})) : ?>";
        });

        // Call to Addons\Entrust::canAndOwns.
        Blade::directive('canAndOwns', function ($expression) {
            return "<?php if (app('entrust')->canAndOwns({$expression})) : ?>";
        });

        // Call to Addons\Entrust::hasRoleAndOwns.
        Blade::directive('hasRoleAndOwns', function ($expression) {
            return "<?php if (app('entrust')->hasRoleAndOwns({$expression})) : ?>";
        });
    }

    /**
     * Registers the directives without parenthesis.
     *
     * @return void
     */
    protected function registerWithoutParenthesis()
    {
        // Call to Addons\Entrust::hasRole.
        Blade::directive('role', function ($expression) {
            return "<?php if (app('entrust')->hasRole{$expression}) : ?>";
        });

        // Call to Addons\Entrust::can.
        Blade::directive('permission', function ($expression) {
            return "<?php if (app('entrust')->can{$expression}) : ?>";
        });

        // Call to Addons\Entrust::ability.
        Blade::directive('ability', function ($expression) {
            return "<?php if (app('entrust')->ability{$expression}) : ?>";
        });

        // Call to Addons\Entrust::canAndOwns.
        Blade::directive('canAndOwns', function ($expression) {
            return "<?php if (app('entrust')->canAndOwns{$expression}) : ?>";
        });

        // Call to Addons\Entrust::hasRoleAndOwns.
        Blade::directive('hasRoleAndOwns', function ($expression) {
            return "<?php if (app('entrust')->hasRoleAndOwns{$expression}) : ?>";
        });
    }

    /**
     * Registers the closing directives.
     *
     * @return void
     */
    protected function registerClosingDirectives()
    {
        Blade::directive('endrole', function () {
            return "<?php endif; // app('entrust')->hasRole ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; // app('entrust')->can ?>";
        });

        Blade::directive('endability', function () {
            return "<?php endif; // app('entrust')->ability ?>";
        });

        Blade::directive('endOwns', function () {
            return "<?php endif; // app('entrust')->hasRoleAndOwns or canAndOwns ?>";
        });
    }
}
