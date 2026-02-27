<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Support\Validator;
use PDO;
use PDOException;

final class NewsletterService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(?string $estado = null): array
    {
        if ($estado !== null) {
            $estado = Validator::oneOf($estado, 'estado', ['activo', 'inactivo', 'bloqueado']);
            $stmt = $this->pdo->prepare(
                'SELECT id_suscripcion, email, nombre, estado, fecha_suscripcion, fecha_confirmacion, fecha_baja
                 FROM newsletter WHERE estado = :estado ORDER BY id_suscripcion DESC'
            );
            $stmt->execute(['estado' => $estado]);
            return $stmt->fetchAll();
        }

        return $this->pdo->query(
            'SELECT id_suscripcion, email, nombre, estado, fecha_suscripcion, fecha_confirmacion, fecha_baja
             FROM newsletter ORDER BY id_suscripcion DESC'
        )->fetchAll();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function create(array $data): array
    {
        Validator::require($data, ['email']);
        $email = Validator::email($data['email'], 'email');
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $ip = trim((string) ($data['ip_suscripcion'] ?? ''));

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO newsletter (email, nombre, estado, fecha_suscripcion, ip_suscripcion)
                 VALUES (:email, :nombre, :estado, :fecha_suscripcion, :ip)'
            );
            $stmt->execute([
                'email' => $email,
                'nombre' => $nombre !== '' ? $nombre : null,
                'estado' => 'activo',
                'fecha_suscripcion' => date('Y-m-d H:i:s'),
                'ip' => $ip !== '' ? $ip : null,
            ]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new ValidationException('El email ya está suscrito');
            }
            throw $e;
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function update(int $id, array $data): array
    {
        $this->findById($id);

        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('email', $data)) {
            $fields[] = 'email = :email';
            $params['email'] = Validator::email($data['email'], 'email');
        }
        if (array_key_exists('nombre', $data)) {
            $fields[] = 'nombre = :nombre';
            $params['nombre'] = trim((string) $data['nombre']);
        }
        if (array_key_exists('estado', $data)) {
            $fields[] = 'estado = :estado';
            $params['estado'] = Validator::oneOf($data['estado'], 'estado', ['activo', 'inactivo', 'bloqueado']);
        }

        if ($fields === []) {
            throw new ValidationException('No hay campos para actualizar');
        }

        $stmt = $this->pdo->prepare('UPDATE newsletter SET ' . implode(', ', $fields) . ' WHERE id_suscripcion = :id');
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id, ?string $motivo = null): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE newsletter
             SET estado = :estado, fecha_baja = :fecha_baja, motivo_baja = :motivo
             WHERE id_suscripcion = :id'
        );
        $stmt->execute([
            'estado' => 'inactivo',
            'fecha_baja' => date('Y-m-d H:i:s'),
            'motivo' => $motivo,
            'id' => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new HttpException('Suscripción no encontrada', 404);
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_suscripcion, email, nombre, estado, fecha_suscripcion, fecha_confirmacion, fecha_baja, motivo_baja
             FROM newsletter WHERE id_suscripcion = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new HttpException('Suscripción no encontrada', 404);
        }

        return $row;
    }
}
