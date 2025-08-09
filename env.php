<?php
// Lightweight .env loader for PHP projects without Composer
// Usage: include_once __DIR__ . '/env.php'; load_env(__DIR__);

if (!function_exists('load_env')) {
    function load_env(string $dir): void
    {
        $candidates = [
            $dir . DIRECTORY_SEPARATOR . '.env',
            $dir . DIRECTORY_SEPARATOR . 'env.local',
            $dir . DIRECTORY_SEPARATOR . 'env.example'
        ];
        $file = null;
        foreach ($candidates as $path) {
            if (is_readable($path)) { $file = $path; break; }
        }
        if ($file === null) return;

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '#') || str_starts_with($trim, ';')) continue;
            // Split on first '=' only
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            // Remove surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if ($name === '') continue;
            // Don't override already defined env
            if (getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Auto-load when included
load_env(__DIR__);
?>


