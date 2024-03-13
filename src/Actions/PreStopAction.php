<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 21:36.
 */

namespace HughCube\Laravel\Octane\Actions;

use HughCube\Laravel\Knight\Routing\Controller;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use Psr\SimpleCache\InvalidArgumentException;
use Swoole\Http\Server;

class PreStopAction extends Controller
{
    /**
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    protected function action(): JsonResponse
    {
        $this->getDispatcher()->dispatch($this);

        $start = microtime(true);
        $taskWorkerCount = $this->waitTaskComplete();
        $end = microtime(true);

        $duration = ($end - $start) * 1000;
        $uri = $this->getRequest()->getRequestUri();

        /** 记录log */
        Log::channel()->info(sprintf(
            'Wait swoole task complete uri: %s, count: %s, duration: %.2fms',
            $uri,
            $taskWorkerCount,
            $duration
        ));

        return new JsonResponse([
            'code' => 200,
            'message' => 'ok',
            'data' => [
                'duration' => $duration,
                'task_worker_count' => $taskWorkerCount,
            ]
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getCache(): Repository
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->getContainer()->make('cache');

        return $cacheManager->store('octane');
    }

    /**
     * @throws BindingResolutionException
     * @throws PhpVersionNotSupportedException
     * @throws InvalidArgumentException
     */
    public function waitTaskComplete(): int
    {
        /** @var null|Server $server */
        $server = null;
        if (class_exists(Server::class) && $this->getContainer()->bound(Server::class)) {
            $server = $this->getContainer()->make(Server::class);
        }

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
                $this->getCache()->put($spy['key'], $spy['value'], 3600);
            }), $index);
        }

        /** 等待异步任务完成 */
        while (!empty($spies)) {
            foreach ($spies as $index => $spy) {
                if ($spy['value'] === $this->getCache()->get($spy['key'])) {
                    unset($spies[$index]);
                    $this->getCache()->forget($spy['key']);
                }
            }
            usleep(200);
        }

        return $workerCount;
    }
}
