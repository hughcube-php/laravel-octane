<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 21:36.
 */

namespace HughCube\Laravel\Octane\Actions;

use HughCube\Laravel\Octane\Octane;
use Illuminate\Config\Repository;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;

class WaitTaskCompleteAction
{
    /**
     * @throws BindingResolutionException
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     *
     * @return JsonResponse
     */
    protected function action(): JsonResponse
    {
        $start = microtime(true);
        $workerCount = Octane::waitSwooleTasks();
        $end = microtime(true);

        $duration = round((($end - $start) * 1000), 2);
        $type = Octane::getRuntimeType() ?: 'unknown';
        $uri = $this->getRequest()->getRequestUri();

        /** 记录log */
        $message = sprintf('type:%s, uri:%s, workerCount:%s, duration%sms', $type, $uri, $workerCount, $duration);
        $this->getLogChannel()->log($this->getLogLevel(), $message);

        return new JsonResponse(['code' => 200, 'message' => 'ok', 'data' => ['duration' => $duration]]);
    }

    /**
     * @throws BindingResolutionException
     *
     * @return LoggerInterface
     */
    protected function getLogChannel(): LoggerInterface
    {
        $channel = $this->getContainerConfig()->get('octane.wait_task_complete_log_channel');

        return Log::channel($channel);
    }

    /**
     * @throws BindingResolutionException
     *
     * @return mixed
     */
    protected function getLogLevel(): mixed
    {
        return $this->getContainerConfig()->get('octane.wait_task_complete_log_level', 'debug');
    }

    /**
     * @throws BindingResolutionException
     *
     * @return Repository
     */
    protected function getContainerConfig(): Repository
    {
        return $this->getContainer()->make('config');
    }

    /**
     * @throws
     *
     * @return Request
     * @phpstan-ignore-next-line
     */
    protected function getRequest(): Request
    {
        return $this->getContainer()->make('request');
    }

    /**
     * @return IlluminateContainer
     */
    protected function getContainer(): IlluminateContainer
    {
        return IlluminateContainer::getInstance();
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @throws PhpVersionNotSupportedException
     */
    public function __invoke(): JsonResponse
    {
        return $this->action();
    }
}
