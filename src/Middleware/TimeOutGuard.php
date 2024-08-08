<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2024/8/8
 * Time: 下午7:43
 */

namespace HughCube\Laravel\Octane\Middleware;

use Closure;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Laravel\Octane\Swoole\WorkerState;
use Swoole\Timer as SwooleTimer;

class TimeOutGuard
{
    /**
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $this->afterTimer($request);
        try {
            $response = $next($request);
        } finally {
            $this->clearTimer($id);
        }

        return $response;
    }

    protected function getContainer(): IlluminateContainer
    {
        return IlluminateContainer::getInstance();
    }

    /**
     * @throws BindingResolutionException
     * @phpstan-ignore-next-line
     */
    protected function getWorkerState(): null|WorkerState
    {
        if (class_exists(WorkerState::class) && $this->getContainer()->bound(WorkerState::class)) {
            return $this->getContainer()->make(WorkerState::class);
        }
        return null;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function afterTimer(Request $request): int|bool|null
    {
        $workerState = $this->getWorkerState();
        if (null !== $workerState && class_exists(SwooleTimer::class, false)) {
            return SwooleTimer::after($this->getTimeOut($request) * 1000, function () use ($workerState) {
                /** @phpstan-ignore-next-line */
                $workerState->server->stop($workerState->workerId, true);
            });
        }
        return null;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function clearTimer($timerId): void
    {
        $workerState = $this->getWorkerState();
        if (null !== $workerState && class_exists(SwooleTimer::class, false) && is_int($timerId)) {
            SwooleTimer::clear($timerId);
        }
    }

    protected function getTimeOut(Request $request): float
    {
        return 30;
    }
}
