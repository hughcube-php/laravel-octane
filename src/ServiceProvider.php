<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:32 下午.
 */

namespace HughCube\Laravel\Octane;

use HughCube\Laravel\Octane\Commands\ListClassPropertiesCommand;
use HughCube\Laravel\Octane\Commands\PrepareCommand;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
        $this->commands([
            PrepareCommand::class,
            ListClassPropertiesCommand::class,
        ]);
    }

    /**
     * Register the provider.
     */
    public function register()
    {

    }
}
