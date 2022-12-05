<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 14:56.
 */

namespace HughCube\Laravel\Octane;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Octane\Contracts\DispatchesTasks;
use Laravel\Octane\Swoole\WorkerState;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use Psr\SimpleCache\InvalidArgumentException;
use Swoole\Http\Server;

/**
 * @method static DispatchesTasks tasks()
 *
 * @see \Laravel\Octane\Octane
 */
class Octane extends \Laravel\Octane\Facades\Octane
{
    protected static function getApp()
    {
        return app();
    }

    /**
     * @return Repository
     */
    public static function cache(): Repository
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
     * @return null|WorkerState
     * @phpstan-ignore-next-line
     * @throws
     *
     */
    public static function workerState(): null|WorkerState
    {
        /** @phpstan-ignore-next-line */
        if (static::getApp()->bound(WorkerState::class)) {
            /** @phpstan-ignore-next-line */
            return static::getApp()->make(WorkerState::class);
        }

        return null;
    }

    public static function isOctane(): bool
    {
        return
            null !== static::workerState()
            && isset($_SERVER['LARAVEL_OCTANE'])
            && $_SERVER['LARAVEL_OCTANE'];
    }

    /**
     * @return int
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     *
     * @throws PhpVersionNotSupportedException
     */
    public static function waitSwooleTasks(): int
    {
        $workerCount = 0;
        if (class_exists(Server::class) && static::getApp()->bound(Server::class)) {
            $workerCount = static::getApp()->make(Server::class)->setting['task_worker_num'] ?? 0;
        }

        /** 生成标识 */
        $spies = [];
        for ($i = 1; $i <= $workerCount; $i++) {
            $random = serialize([microtime(), Str::random(32), $i]);
            $spies[] = sprintf('%s-%s-%s', getmypid(), md5($random), crc32($random));
        }

        /** 投递异步任务写入标识 */
        foreach ($spies as $index => $spy) {
            app(Server::class)->task(new SerializableClosure(function () use ($spy) {
                Cache::store('octane')->put($spy, time(), 600);
            }), $index);
        }

        /** 等待异步任务完成 */
        while (!empty($spies)) {
            foreach ($spies as $index => $spy) {
                $timestamp = static::cache()->get($spy);
                if (is_numeric($timestamp) && 0 < $timestamp) {
                    unset($spies[$index]);
                    static::cache()->forget($spy);
                }
            }
            usleep(200);
        }

        return $workerCount;
    }

    public static function getRuntimeType($default = null): null|string|int|float|bool
    {
        return getenv('OCTANE_RUNTIME_TYPE') ?: $default;
    }

    public static function isRuntimeType(string $type): bool
    {
        return $type === static::getRuntimeType();
    }
}
