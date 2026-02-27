<?php

declare(strict_types=1);

use App\Controllers\CartController;
use App\Controllers\CouponController;
use App\Controllers\FavoriteController;
use App\Controllers\NewsletterController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;

return static function (
    Router $router,
    CouponController $couponController,
    NewsletterController $newsletterController,
    FavoriteController $favoriteController,
    CartController $cartController
): void {
    $router->add('GET', '/api/health', static fn (Request $request): Response => Response::json([
        'ok' => true,
        'service' => 'okea-backend',
        'timestamp' => date(DATE_ATOM),
    ]));

    $router->add('GET', '/api/coupons', [$couponController, 'index']);
    $router->add('POST', '/api/coupons', [$couponController, 'create']);
    $router->add('GET', '/api/coupons/{id}', [$couponController, 'show']);
    $router->add('PUT', '/api/coupons/{id}', [$couponController, 'update']);
    $router->add('DELETE', '/api/coupons/{id}', [$couponController, 'delete']);
    $router->add('GET', '/api/coupons/{id}/validar', [$couponController, 'validate']);

    $router->add('GET', '/api/newsletter', [$newsletterController, 'index']);
    $router->add('POST', '/api/newsletter', [$newsletterController, 'create']);
    $router->add('PUT', '/api/newsletter/{id}', [$newsletterController, 'update']);
    $router->add('DELETE', '/api/newsletter/{id}', [$newsletterController, 'delete']);

    $router->add('GET', '/api/favorites', [$favoriteController, 'index']);
    $router->add('POST', '/api/favorites', [$favoriteController, 'create']);
    $router->add('DELETE', '/api/favorites/{id}', [$favoriteController, 'delete']);

    $router->add('POST', '/api/carts/{userId}', [$cartController, 'createCart']);
    $router->add('GET', '/api/carts/{cartId}', [$cartController, 'cartSummary']);
    $router->add('POST', '/api/carts/{cartId}/items', [$cartController, 'addItem']);
    $router->add('POST', '/api/addresses', [$cartController, 'createAddress']);
    $router->add('POST', '/api/carts/{cartId}/checkout', [$cartController, 'checkout']);
    $router->add('POST', '/api/orders/{orderId}/confirm', [$cartController, 'confirmOrder']);
};
