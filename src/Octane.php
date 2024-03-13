<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 14:56.
 */

namespace HughCube\Laravel\Octane;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Laravel\Octane\Contracts\DispatchesTasks;

/**
 * @method static DispatchesTasks tasks()
 *
 * @see \Laravel\Octane\Octane
 */
class Octane extends \Laravel\Octane\Facades\Octane
{
    /**
     * @return Repository
     */
    public static function cache(): Repository
    {
        return Cache::store('octane');
    }
}
