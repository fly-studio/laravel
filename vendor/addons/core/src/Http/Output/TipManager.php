<?php

namespace Addons\Core\Http\Output;

use Illuminate\Support\Manager;

class TipManager extends Manager {


    /**
     * Create a Toast instance.
     *
     * @return \Addons\Core\Http\Output\Tips\Toast
     */
    public function createToastDriver()
    {
        return new Tips\Toast();
    }

    /**
     * Create a Back instance.
     *
     * @return \Addons\Core\Http\Output\Tips\Back
     */
    public function createBackDriver()
    {
        return new Tips\Back();
    }

    /**
     * Create a Redirect instance.
     *
     * @return \Addons\Core\Http\Output\Tips\Redirect
     */
    public function createRedirectDriver()
    {
        return new Tips\Redirect();
    }

    /**
     * Create a Refresh instance.
     *
     * @return \Addons\Core\Http\Output\Tips\Refresh
     */
    public function createRefreshDriver()
    {
        return new Tips\Refresh();
    }

    /**
     * Get the default session driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['.driver'];
    }

}