<?php

namespace App\Services\PipePlanner;

use RuntimeException;

class OpenRouterException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        string $message,
        public readonly bool $retryable = false,
    ) {
        parent::__construct($message);
    }
}
