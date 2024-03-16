<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/8/12
 * Time: 00:08.
 */

namespace HughCube\Laravel\Octane\Listeners;

use HughCube\Laravel\Knight\Traits\Container;
use Laravel\Octane\Events\RequestReceived;

class PrepareServerVariables
{
    use Container;

    /**
     * Handle the event.
     *
     * @param RequestReceived $event
     *
     * @return void
     */
    public function handle(mixed $event): void
    {
        $this->prepareHost($event);
    }

    /**
     * @param RequestReceived $event
     */
    protected function prepareHost(mixed $event): void
    {
        if (!$event instanceof RequestReceived) {
            return;
        }

        $host = $event->request->getHost();
        if (false === filter_var($host, FILTER_VALIDATE_IP)) {
            return;
        }

        $appUrl = $this->getContainerConfig('app.url');
        if (empty($host = parse_url($appUrl, PHP_URL_HOST))) {
            return;
        }

        $event->request->server->set('HTTP_HOST', $host);
        $event->request->headers->set('HOST', $host);
    }
}
