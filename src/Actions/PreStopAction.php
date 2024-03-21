<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 21:36.
 */

namespace HughCube\Laravel\Octane\Actions;

use HughCube\Laravel\Knight\Routing\Controller;
use HughCube\Laravel\Knight\Traits\Container;
use HughCube\Laravel\Octane\Listeners\WaitTaskComplete;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Psr\SimpleCache\InvalidArgumentException;

class PreStopAction extends Controller
{
    use Container;

    /**
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     */
    protected function action(): JsonResponse
    {
        $this->getEventsDispatcher()->dispatch($this);

        $start = microtime(true);
        $taskWorkerCount = WaitTaskComplete::wait();
        $end = microtime(true);

        $duration = ($end - $start) * 1000;
        $uri = $this->getRequest()->getRequestUri();

        /** 记录log */
        Log::channel()->info(sprintf(
            'Wait tasks complete uri: %s, count: %s, duration: %.5fms',
            $uri,
            $taskWorkerCount,
            $duration
        ));

        return new JsonResponse([
            'code' => 200,
            'message' => 'ok',
            'data' => [
                'duration' => round($duration, 5),
                'task_worker_count' => $taskWorkerCount,
            ],
        ]);
    }
}
