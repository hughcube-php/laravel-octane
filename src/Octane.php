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
use Laravel\Octane\Swoole\WorkerState;
use Swoole\Http\Server;

/**
 * @see \Laravel\Octane\Octane
 */
class Octane extends \Laravel\Octane\Facades\Octane
{
    public static function getCache(): Repository
    {
        return Cache::store('octane');
    }

    /**
     * @throws
     * @phpstan-ignore-next-line
     */
    public static function getWorkerState(): null|WorkerState
    {
        if (class_exists(WorkerState::class) && static::$app->bound(WorkerState::class)) {
            return static::$app->make(WorkerState::class);
        }

        return null;
    }

    /**
     * @throws
     * @phpstan-ignore-next-line
     */
    public static function getSwooleServer(): null|Server
    {
        if (class_exists(Server::class) && static::$app->bound(Server::class)) {
            return static::$app->make(Server::class);
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function isSwoole(): bool
    {
        return static::getSwooleServer() instanceof Server;
    }
}
