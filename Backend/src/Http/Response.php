<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(
        private readonly array $payload,
        private readonly int $statusCode = 200
    ) {
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function json(array $payload, int $statusCode = 200): self
    {
        return new self($payload, $statusCode);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->payload, JSON_UNESCAPED_UNICODE);
    }
}
