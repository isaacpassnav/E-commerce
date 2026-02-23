<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\NewsletterService;
use App\Support\Validator;

final class NewsletterController
{
    public function __construct(private readonly NewsletterService $service)
    {
    }

    /**
     * @param array<string,string> $params
     */
    public function index(Request $request, array $params = []): Response
    {
        $estado = $request->input('estado');
        $estado = is_string($estado) ? $estado : null;

        return Response::json([
            'ok' => true,
            'data' => $this->service->list($estado),
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
        $motivo = $request->input('motivo');
        $this->service->delete($id, is_string($motivo) ? $motivo : null);

        return Response::json([
            'ok' => true,
            'message' => 'Suscripción dada de baja',
        ]);
    }
}
