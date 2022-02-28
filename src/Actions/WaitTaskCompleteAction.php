<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 21:36.
 */

namespace HughCube\Laravel\Octane\Actions;

use HughCube\Laravel\Knight\Routing\Controller;
use HughCube\Laravel\Octane\Octane;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;

class WaitTaskCompleteAction extends Controller
{
    /**
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     * @throws  PhpVersionNotSupportedException
     */
    protected function action(): JsonResponse
    {
        $start = microtime(true);
        $workerCount = Octane::waitSwooleTasks();
        $end = microtime(true);

        $duration = round((($end - $start) * 1000), 2);
        $type = getenv('OCTANE_RUNTIME_TYPE') ?: 'unknown';
        $uri = $this->getRequest()->getRequestUri();

        /** 记录log */
        $message = sprintf('type:%s, uri:%s, workerCount:%s, duration%sms', $type, $uri, $workerCount, $duration);
        $this->getLogChannel()->log($this->getLogLevel(), $message);

        return $this->asJson(['duration' => $duration]);
    }

    /**
     * @return LoggerInterface
     * @throws BindingResolutionException
     */
    protected function getLogChannel(): LoggerInterface
    {
        $channel = $this->getContainerConfig()->get('octane.wait_task_complete_log_channel');

        return Log::channel($channel);
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     *
     */
    protected function getLogLevel(): mixed
    {
        return $this->getContainerConfig()->get('octane.wait_task_complete_log_level', 'debug');
    }
}
