<?php

/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/2/22
 * Time: 11:18.
 */

namespace HughCube\Laravel\Octane\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class ListClassPropertiesCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected $signature = 'octane:list-class-properties
                            {--path=*Http : Number of repetitions, one by default }
                            {--name=**Controller.php : The name of the class that needs to be executed}
                            {--class=*App\* : The name of the class that needs to be executed}
    ';

    /**
     * @inheritdoc
     */
    protected $description = 'Query classes properties, properties are not recommended in octane.';

    public function handle(Schedule $schedule): void
    {
        $basePath = base_path();
        $files = (new Finder())->files()
            ->in(
                Collection::make(($this->option('path') ?: []))->map(function ($path) {
                    return is_dir($path) ? $path : app_path($path);
                })->values()->toArray()
            )
            ->name(($this->option('name') ?: []));

        foreach ($files as $file) {
            $class = Str::replaceFirst($basePath, '', $file->getRealPath());
            $class = trim($class, DIRECTORY_SEPARATOR);
            $class = rtrim($class, '.php');
            $class = strtr($class, ['/' => '\\']);
            $class = Str::ucfirst($class);

            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }

            if (!$reflection->isInstantiable()) {
                continue;
            }

            foreach ($reflection->getProperties() as $property) {
                $inClass = $property->getDeclaringClass()->getName();

                if (!Str::is(($this->option('class') ?: []), $inClass)) {
                    continue;
                }

                $this->info(sprintf('%s::$%s', $inClass, $property->getName()));
            }
        }
    }
}
