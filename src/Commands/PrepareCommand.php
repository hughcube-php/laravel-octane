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
use Symfony\Component\Console\Input\InputOption;

class PrepareCommand extends StartSwooleCommand
{
    /**
     * @inheritdoc
     */
    public $description = 'Prepare the Octane Swoole server';

    public function __construct()
    {
        $this->signature = preg_replace('/^octane:swoole/', 'octane:prepare', $this->signature);

        parent::__construct();

        $this->getDefinition()->addOption(new InputOption(
            'state-file',
            null,
            InputOption::VALUE_OPTIONAL,
            'Server state file'
        ));
    }

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

    protected function setServerStateFilePath(ServerStateFile $serverStateFile, $file): void
    {
        $reflection = new ReflectionClass($serverStateFile);

        $property = $reflection->getProperty('path');
        $property->setAccessible(true);

        $property->setValue($serverStateFile, $file);
    }
}
