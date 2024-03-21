<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 14:56.
 */

namespace HughCube\Laravel\Octane;

use HughCube\Laravel\Octane\Listeners\WaitTaskComplete;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Laravel\Octane\Contracts\DispatchesTasks;
use Laravel\Octane\Swoole\WorkerState;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Psr\SimpleCache\InvalidArgumentException;
use Swoole\Server;

/**
 * @method static DispatchesTasks tasks()
 * @see \Laravel\Octane\Octane
 */
class Octane extends \Laravel\Octane\Facades\Octane
{
    public static function getCache(): Repository
    {
        return Cache::store('octane');
    }

    /**
     * @param  callable  $callable
     *
     * @return void
     */
    public static function task(callable $callable): void
    {
        static::tasks()->dispatch([$callable]);
    }

    /**
     * @throws
     *
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
     * @return bool
     */
    public static function isSwoole(): bool
    {
        /** @phpstan-ignore-next-line */
        return class_exists(Server::class) && static::getWorkerState()?->server instanceof Server;
    }

    /**
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     */
    public static function waitTaskComplete(): int
    {
        return WaitTaskComplete::wait();
    }
}
