<?php
// env.php - Simple .env loader and environment bootstrap

// Load .env file if present
if (!function_exists('load_env_file')) {
    /**
     * Loads key=value pairs from a .env file into environment variables.
     */
    function load_env_file(string $filepath): void
    {
        if (!is_readable($filepath)) {
            return;
        }

        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || substr($trimmed, 0, 1) === '#') {
                continue;
            }
            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove surrounding quotes if any
            $len = strlen($value);
            if ($len >= 2) {
                $firstChar = $value[0];
                $lastChar = $value[$len - 1];
                if ((($firstChar === '"') && ($lastChar === '"')) || (($firstChar === "'") && ($lastChar === "'"))) {
                    $value = substr($value, 1, -1);
                }
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$envPath = __DIR__ . '/.env';
load_env_file($envPath);

// Export OPENAI_API_KEY as a constant if provided via environment
if (!defined('OPENAI_API_KEY')) {
    $openAiKey = getenv('OPENAI_API_KEY');
    if ($openAiKey !== false && $openAiKey !== '') {
        define('OPENAI_API_KEY', $openAiKey);
    }
}

?>
