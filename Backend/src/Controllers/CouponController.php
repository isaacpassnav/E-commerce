<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CouponService;
use App\Support\Validator;

final class CouponController
{
    public function __construct(private readonly CouponService $service)
    {
    }

    /**
     * @param array<string,string> $params
     */
    public function index(Request $request, array $params = []): Response
    {
        return Response::json([
            'ok' => true,
            'data' => $this->service->list(),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function show(Request $request, array $params): Response
    {
        $id = Validator::int($params['id'] ?? null, 'id');

        return Response::json([
            'ok' => true,
            'data' => $this->service->findById($id),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function create(Request $request, array $params = []): Response
    {
        return Response::json([
            'ok' => true,
            'data' => $this->service->create($request->body()),
        ], 201);
    }

    /**
     * @param array<string,string> $params
     */
    public function update(Request $request, array $params): Response
    {
        $id = Validator::int($params['id'] ?? null, 'id');

        return Response::json([
            'ok' => true,
            'data' => $this->service->update($id, $request->body()),
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function delete(Request $request, array $params): Response
    {
        $id = Validator::int($params['id'] ?? null, 'id');
        $this->service->delete($id);

        return Response::json([
            'ok' => true,
            'message' => 'Cupón eliminado correctamente',
        ]);
    }

    /**
     * @param array<string,string> $params
     */
    public function validate(Request $request, array $params): Response
    {
        $id = Validator::int($params['id'] ?? null, 'id');
        $orderAmount = (float) ($request->input('monto_pedido', 0) ?? 0);

        return Response::json([
            'ok' => true,
            'data' => $this->service->validateAvailability($id, $orderAmount),
        ]);
    }
}
