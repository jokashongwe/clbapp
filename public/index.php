<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Sous Apache/CGI, Authorization est souvent perdu : le restaurer si possible.
if (
    (!isset($_SERVER['HTTP_AUTHORIZATION']) || $_SERVER['HTTP_AUTHORIZATION'] === '')
    && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
    && $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] !== ''
) {
    $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
