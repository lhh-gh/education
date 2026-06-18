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
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\ClassLoader;
use Mine\AppStore\Plugin;

/*
 * This file is part of MineAdmin.
 *
 * @see     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(\E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', \SWOOLE_HOOK_ALL);
! defined('START_TIME') && define('START_TIME', time());    // 启动时间
! defined('HF_VERSION') && define('HF_VERSION', '3.1');     // 定义hyperf版本号

function envValueForTests(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    $envFile = BASE_PATH . '/.env';
    if (! is_file($envFile)) {
        return null;
    }

    foreach (file($envFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$name, $rawValue] = explode('=', $line, 2);
        if (trim($name) !== $key) {
            continue;
        }

        return trim(trim($rawValue), '\'"');
    }

    return null;
}

function setEnvForTests(string $key, string $value): void
{
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$testDatabase = envValueForTests('DB_TEST_DATABASE');
if ($testDatabase === null || trim($testDatabase) === '') {
    throw new RuntimeException('DB_TEST_DATABASE must be configured before running tests.');
}

if (! str_contains(mb_strtolower($testDatabase), 'test')) {
    throw new RuntimeException('Refusing to run tests because DB_TEST_DATABASE does not look like a test database.');
}

setEnvForTests('APP_ENV', 'testing');
setEnvForTests('DB_DATABASE', $testDatabase);

require BASE_PATH . '/vendor/autoload.php';

Plugin::init();
ClassLoader::init();

$container = require BASE_PATH . '/config/container.php';

$container->get(ApplicationInterface::class);
