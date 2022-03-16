<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/8/24
 * Time: 14:44.
 */

namespace HughCube\Laravel\Octane\Commands;

use Laravel\Octane\Commands\StartSwooleCommand;
use Laravel\Octane\Swoole\ServerProcessInspector;
use Laravel\Octane\Swoole\ServerStateFile;
use Laravel\Octane\Swoole\SwooleExtension;
use ReflectionClass;

class PrepareCommand extends StartSwooleCommand
{
    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'octane:prepare
                    {--host=127.0.0.1 : The IP address the server should bind to}
                    {--port=8000 : The port the server should be available on}
                    {--workers=auto : The number of workers that should be available to handle requests}
                    {--task-workers=auto : The number of task workers that should be available to handle tasks}
                    {--max-requests=500 : The number of requests to process before reloading the server}
                    {--watch : Automatically reload the server when the application is modified}
                    {--state-file : Server state file}';

    /**
     * The command's description.
     *
     * @var string
     */
    public $description = 'Prepare the Octane Swoole server';

    /**
     * @inheritdoc
     */
    public function handle(
        ServerProcessInspector $inspector,
        ServerStateFile $serverStateFile,
        SwooleExtension $extension
    ): int {
        if (!empty($stateFile = $this->option('state-file'))) {
            $this->setServerStateFilePath($serverStateFile, $stateFile);
        }

        $this->writeServerStateFile($serverStateFile, $extension);
        $this->info(sprintf('file writing succeeded: %s', $serverStateFile->path()));

        return 0;
    }

    protected function setServerStateFilePath(ServerStateFile $serverStateFile, $file)
    {
        $reflection = new ReflectionClass($serverStateFile);

        $property = $reflection->getProperty('path');
        $property->setAccessible(true);

        $property->setValue($serverStateFile, $file);
    }
}
