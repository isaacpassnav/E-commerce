<?php

declare(strict_types=1);

namespace App;

use App\Config\Env;
use App\Controllers\CartController;
use App\Controllers\CouponController;
use App\Controllers\FavoriteController;
use App\Controllers\NewsletterController;
use App\Database\Connection;
use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\FavoriteService;
use App\Services\NewsletterService;
use Throwable;

final class App
{
    private Router $router;
    private ?Throwable $bootError = null;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerBaseRoutes();

        try {
            $pdo = Connection::make();

            $couponController = new CouponController(new CouponService($pdo));
            $newsletterController = new NewsletterController(new NewsletterService($pdo));
            $favoriteController = new FavoriteController(new FavoriteService($pdo));
            $cartController = new CartController(new CartService($pdo));

            /** @var callable $registerRoutes */
            $registerRoutes = require __DIR__ . '/routes.php';
            $registerRoutes(
                $this->router,
                $couponController,
                $newsletterController,
                $favoriteController,
                $cartController
            );
        } catch (Throwable $e) {
            $this->bootError = $e;
        }
    }

    public function run(): void
    {
        try {
            $request = Request::fromGlobals();
            $this->guardApiKey($request);
            $this->guardBoot($request);
            $response = $this->router->dispatch($request);
        } catch (HttpException $e) {
            $response = Response::json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], $e->statusCode());
        } catch (Throwable $e) {
            $response = Response::json([
                'ok' => false,
                'error' => 'Error interno del servidor',
            ], 500);
        }

        $response->send();
    }

    private function registerBaseRoutes(): void
    {
        $this->router->add('GET', '/api/health', function (): Response {
            $dbReady = $this->bootError === null;

            $payload = [
                'ok' => $dbReady,
                'service' => 'okea-backend',
                'timestamp' => date(DATE_ATOM),
                'database' => [
                    'status' => $dbReady ? 'up' : 'down',
                ],
            ];

            $isDebug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
            if (!$dbReady && $isDebug && $this->bootError !== null) {
                $payload['database']['error'] = $this->bootError->getMessage();
            }

            return Response::json($payload, $dbReady ? 200 : 503);
        });
    }

    private function guardBoot(Request $request): void
    {
        if ($this->bootError === null) {
            return;
        }

        if ($request->path() === '/api/health') {
            return;
        }

        throw new HttpException('Servicio no disponible temporalmente', 503);
    }

    private function guardApiKey(Request $request): void
    {
        $expected = Env::get('API_KEY');
        if ($expected === null || $expected === '') {
            return;
        }

        $provided = $request->header('x-api-key');
        if ($provided !== $expected) {
            throw new HttpException('No autorizado', 401);
        }
    }
}
