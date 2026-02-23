<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;

final class Router
{
    /**
     * @var array<string,array<int,array{regex:string,handler:callable}>>
     */
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $regex = $this->compilePath($path);
        $this->routes[$method][] = [
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();
        $candidates = $this->routes[$method] ?? [];

        foreach ($candidates as $route) {
            $matches = [];
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            $response = ($route['handler'])($request, $params);
            if (!$response instanceof Response) {
                throw new HttpException('Handler inválido: se esperaba Response', 500);
            }

            return $response;
        }

        throw new HttpException('Ruta no encontrada', 404);
    }

    private function compilePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized === '//') {
            $normalized = '/';
        }

        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $normalized);
        $pattern = $pattern === null ? $normalized : $pattern;

        return '#^' . $pattern . '$#';
    }
}
