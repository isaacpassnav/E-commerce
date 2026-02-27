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

    public function __construct()
    {
        $this->router = new Router();
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
    }

    public function run(): void
    {
        try {
            $request = Request::fromGlobals();
            $this->guardApiKey($request);
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
