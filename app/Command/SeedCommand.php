<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Command;

use Hyperf\Database\Seeders\Seed;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputOption;

class SeedCommand extends \Hyperf\Database\Commands\Seeders\SeedCommand
{
    public function __construct(Seed $seed)
    {
        parent::__construct($seed);
    }

    public function handle()
    {
        $class = $this->input->getOption('class');
        if ($class === null || $class === '') {
            return parent::handle();
        }

        if (! $this->confirmToProceed()) {
            return null;
        }

        $this->seed->setOutput($this->output);

        if ($this->input->hasOption('database') && $this->input->getOption('database')) {
            $this->seed->setConnection($this->input->getOption('database'));
        }

        $this->runClassSeeder((string) $class);

        return null;
    }

    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the seeder'],
        ]);
    }

    private function runClassSeeder(string $class): void
    {
        $class = ltrim($class, '\\');

        if (! class_exists($class)) {
            $file = BASE_PATH . '/databases/seeders/' . Str::snake($class) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }

        if (! class_exists($class)) {
            $file = BASE_PATH . '/databases/seeders/' . $class . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }

        if (! class_exists($class)) {
            throw new \RuntimeException(sprintf('Class "%s" not found', $class));
        }

        $seeder = new $class();
        if (! $seeder instanceof Seeder) {
            throw new \RuntimeException(sprintf('Class "%s" is not a seeder', $class));
        }

        $this->output->writeln("<comment>Seed:</comment> {$class}");
        $seeder->run();
        $this->output->writeln("<info>Seeded:</info> {$class}");
    }
}
