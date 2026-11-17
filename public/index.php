<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    $environment = $context['APP_ENV'] ?? null;
    assert(is_string($environment));

    return new Kernel($environment, (bool) $context['APP_DEBUG']);
};
