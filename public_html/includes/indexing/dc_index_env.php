<?php

function dc_index_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $path = dirname(__DIR__, 3) . '/config/indexing.php';
        $cfg = is_readable($path) ? require $path : [];
        if (!is_array($cfg)) {
            $cfg = [];
        }
    }
    return $cfg;
}

function dc_index_cfg(string $key, $default = '')
{
    $cfg = dc_index_config();
    return array_key_exists($key, $cfg) ? $cfg[$key] : $default;
}

function dc_index_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $root = dirname(__DIR__, 3);
    $envFile = $root . '/.cursor/deploy.local.env';
    $onServer = is_readable(dirname(__DIR__, 2) . '/connection.php') && !is_readable($envFile);
    if (is_readable($envFile) && !$onServer) {
        $vars = [];
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v, " \t'\"");
        }
        $host = $vars['DB_HOST'] ?? '127.0.0.1';
        $port = $vars['DB_PORT'] ?? '3307';
        $name = $vars['DB_NAME'] ?? 'dreamcalendars_dream';
        $user = $vars['DB_USER'] ?? 'dreamcalendars_dream';
        $pass = $vars['DB_PASSWORD'] ?? '';
    } else {
        $name = 'dreamcalendars_dream';
        $user = 'dreamcalendars_dream';
        $pass = '';
        $host = '127.0.0.1';
        $port = '3306';
        $connPhp = dirname(__DIR__, 2) . '/connection.php';
        if (is_readable($connPhp)) {
            require_once $connPhp;
            $name = $dbname ?? $name;
            $user = $username ?? $user;
            $pass = $password ?? $pass;
        }
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function dc_index_log(string $msg): void
{
    $line = '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . $msg . PHP_EOL;
    if (PHP_SAPI === 'cli') {
        echo $line;
    }
    $logDir = dirname(__DIR__, 2) . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . '/index-ping.log', $line, FILE_APPEND | LOCK_EX);
}
