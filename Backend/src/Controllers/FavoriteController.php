<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\FavoriteService;
use App\Support\Validator;

final class FavoriteController
{
    private FavoriteService $service;

    public function __construct(FavoriteService $service)
    {
        $this->service = $service;
    }

    /**
     * @param array<string,string> $params
     */
    public function index(Request $request, array $params = []): Response
    {
        $userIdInput = $request->input('id_usuario');
        $userId = null;
        if ($userIdInput !== null && $userIdInput !== '') {
            $userId = Validator::int($userIdInput, 'id_usuario');
        }

        return Response::json([
            'ok' => true,
            'data' => $this->service->list($userId),
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
    public function delete(Request $request, array $params): Response
    {
        $id = Validator::int($params['id'] ?? null, 'id');
        $this->service->delete($id);

        return Response::json([
            'ok' => true,
            'message' => 'Favorito eliminado correctamente',
        ]);
    }
}
