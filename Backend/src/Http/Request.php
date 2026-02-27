<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    private string $method;
    private string $path;
    /** @var array<string,mixed> */
    private array $query;
    /** @var array<string,mixed> */
    private array $body;
    /** @var array<string,string> */
    private array $headers;

    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $body
     * @param array<string,string> $headers
     */
    public function __construct(
        string $method,
        string $path,
        array $query,
        array $body,
        array $headers
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
        $this->headers = $headers;
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = $_GET;

        $rawInput = file_get_contents('php://input') ?: '';
        $jsonBody = json_decode($rawInput, true);
        $parsedBody = is_array($jsonBody) ? $jsonBody : [];

        if ($method === 'POST' && !empty($_POST)) {
            $parsedBody = array_merge($parsedBody, $_POST);
        }

        return new self($method, $path, $query, $parsedBody, self::extractHeaders());
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string,mixed>
     */
    public function query(): array
    {
        return $this->query;
    }

    /**
     * @return array<string,mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        return $this->query[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? null;
    }

    /**
     * @return array<string,string>
     */
    private static function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0) {
                continue;
            }

            $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$headerName] = (string) $value;
        }

        return $headers;
    }
}
