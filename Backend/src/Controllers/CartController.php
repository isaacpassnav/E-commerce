<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CartService;
use App\Support\Validator;

final class CartController
{
    private CartService $service;

    public function __construct(CartService $service)
    {
        $this->service = $service;
    }

    /**
     * @param array<string,string> $params
     */
    public function createCart(Request $request, array $params): Response
    {
        $userId = Validator::int($params['userId'] ?? null, 'userId');

        return Response::json([
            'ok' => true,
            'data' => $this->service->createOrGetActiveCart($userId),
        ], 201);
    }

    /**
     * @param array<string,string> $params
     */
    public function addItem(Request $request, array $params): Response
    {
        $cartId = Validator::int($params['cartId'] ?? null, 'cartId');
        $productId = Validator::int($request->input('id_producto'), 'id_producto');
        $quantity = Validator::int($request->input('cantidad', 1), 'cantidad');

        return Response::json([
            'ok' => true,
            'data' => $this->service->addItem($cartId, $productId, $quantity),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function cartSummary(Request $request, array $params): Response
    {
        $cartId = Validator::int($params['cartId'] ?? null, 'cartId');

        return Response::json([
            'ok' => true,
            'data' => $this->service->getCartSummary($cartId),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function createAddress(Request $request, array $params = []): Response
    {
        return Response::json([
            'ok' => true,
            'data' => $this->service->createAddress($request->body()),
        ], 201);
    }

    /**
     * @param array<string,string> $params
     */
    public function checkout(Request $request, array $params): Response
    {
        $cartId = Validator::int($params['cartId'] ?? null, 'cartId');
        $addressId = Validator::int($request->input('id_direccion'), 'id_direccion');

        return Response::json([
            'ok' => true,
            'data' => $this->service->checkout($cartId, $addressId),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function confirmOrder(Request $request, array $params): Response
    {
        $orderId = Validator::int($params['orderId'] ?? null, 'orderId');

        return Response::json([
            'ok' => true,
            'data' => $this->service->confirmOrder($orderId),
        ]);
    }
}
