<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Support\Validator;
use PDO;
use PDOException;

final class FavoriteService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(?int $userId = null): array
    {
        if ($userId !== null) {
            $stmt = $this->pdo->prepare(
                'SELECT id_favorito, id_usuario, id_producto, creado_el
                 FROM favoritos WHERE id_usuario = :user_id ORDER BY id_favorito DESC'
            );
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        }

        return $this->pdo->query(
            'SELECT id_favorito, id_usuario, id_producto, creado_el
             FROM favoritos ORDER BY id_favorito DESC'
        )->fetchAll();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function create(array $data): array
    {
        Validator::require($data, ['id_usuario', 'id_producto']);
        $userId = Validator::int($data['id_usuario'], 'id_usuario');
        $productId = Validator::int($data['id_producto'], 'id_producto');
        $ip = trim((string) ($data['ip_usuario'] ?? ''));
        $email = trim((string) ($data['email_usuario'] ?? ''));

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO favoritos (id_usuario, id_producto, creado_el, ip_usuario, email_usuario)
                 VALUES (:id_usuario, :id_producto, :creado_el, :ip_usuario, :email_usuario)'
            );
            $stmt->execute([
                'id_usuario' => $userId,
                'id_producto' => $productId,
                'creado_el' => date('Y-m-d H:i:s'),
                'ip_usuario' => $ip !== '' ? $ip : null,
                'email_usuario' => $email !== '' ? $email : null,
            ]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new ValidationException('El producto ya está en favoritos para este usuario');
            }
            throw $e;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM favoritos WHERE id_favorito = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw new HttpException('Favorito no encontrado', 404);
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_favorito, id_usuario, id_producto, creado_el, ip_usuario, email_usuario
             FROM favoritos WHERE id_favorito = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Favorito no encontrado', 404);
        }

        return $row;
    }
}
