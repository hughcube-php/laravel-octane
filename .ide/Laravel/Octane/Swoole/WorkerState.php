<?php

namespace Laravel\Octane\Swoole;

use Laravel\Octane\Tables\OpenSwooleTable;
use Laravel\Octane\Tables\SwooleTable;
use Laravel\Octane\Worker;
use Swoole\Http\Server;

class WorkerState
{
    /**
     * @var Server
     */
    public $server;

    /**
     * @var integer
     */
    public $workerId;

    /**
     * @var integer
     */
    public $workerPid;

    /**
     * @var Worker
     */
    public $worker;

    /**
     * @var SwooleClient
     */
    public $client;

    /**
     * @var SwooleTable|OpenSwooleTable
     */
    public $timerTable;

    /**
     * @var SwooleTable|OpenSwooleTable
     */
    public $cacheTable;

    /**
     * @var SwooleTable[]|OpenSwooleTable[]
     */
    public $tables = [];

    /**
     * @var integer
     */
    public $tickTimerId;

    /**
     * @var float
     */
    public $lastRequestTime;
}
