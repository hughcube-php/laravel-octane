<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/8/12
 * Time: 00:08.
 */

namespace HughCube\Laravel\Octane\Listeners;

use HughCube\Laravel\Octane\Octane;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use Psr\SimpleCache\InvalidArgumentException;

class WaitTaskComplete
{
    /**
     * @throws InvalidArgumentException
     * @throws PhpVersionNotSupportedException
     */
    public static function handle(mixed $event = null): void
    {
        static::wait();
    }

    /**
     * @throws InvalidArgumentException
     * @throws PhpVersionNotSupportedException
     */
    public static function wait(): int
    {
        if (Octane::isSwoole()) {
            return static::waitSwooleTaskComplete();
        }

        return 0;
    }

    /**
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     */
    protected static function waitSwooleTaskComplete(): int
    {
        $server = Octane::getSwooleServer();

        /** @var int $workerCount 当前task进程数量 */
        $workerCount = null === $server ? 0 : $server->setting['task_worker_num'];

        /** 生成标识 */
        $spies = [];
        for ($i = 1; $i <= $workerCount; $i++) {
            $random = serialize([microtime(), Str::random(32), $i]);
            $spies[] = [
                'key' => sprintf('%s-%s-%s', getmypid(), md5($random), crc32($random)),
                'value' => $random,
            ];
        }

        /** 投递异步任务写入标识 */
        foreach ($spies as $index => $spy) {
            $server->task(new SerializableClosure(function () use ($spy) {
                Octane::getCache()->put($spy['key'], $spy['value'], 3600);
            }), $index);
        }

        /** 等待异步任务完成 */
        while (!empty($spies)) {
            foreach ($spies as $index => $spy) {
                if ($spy['value'] === Octane::getCache()->get($spy['key'])) {
                    unset($spies[$index]);
                    Octane::getCache()->forget($spy['key']);
                }
            }
            usleep(200);
        }

        return $workerCount;
    }
}
