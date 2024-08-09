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

class ClearTimeOutTimerGuard
{
    /**
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);
        } finally {
            $this->clearTimeOutTimer();
        }

        return $response;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function clearTimeOutTimer(): void
    {
        $container = IlluminateContainer::getInstance();

        $workerState = null;
        if (class_exists(WorkerState::class, false) && $container->bound(WorkerState::class)) {
            $workerState = $container->make(WorkerState::class);
        }

        if (null !== $workerState && $workerState->timerTable) {
            $workerState->timerTable->del($workerState->workerId);
        }
    }
}
