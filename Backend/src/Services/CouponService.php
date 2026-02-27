<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Support\Validator;
use PDO;
use PDOException;

final class CouponService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(): array
    {
        $sql = 'SELECT id_cupon, codigo, descripcion, tipo_descuento, valor_descuento, monto_minimo, usos_maximos, usos_actuales, fecha_inicio, fecha_fin, activo
                FROM cupones
                ORDER BY id_cupon DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function create(array $data): array
    {
        Validator::require($data, ['codigo', 'tipo_descuento', 'valor_descuento', 'fecha_inicio', 'fecha_fin']);
        $codigo = strtoupper(trim((string) $data['codigo']));
        $tipo = Validator::oneOf($data['tipo_descuento'], 'tipo_descuento', ['porcentaje', 'monto_fijo']);
        $valor = Validator::float($data['valor_descuento'], 'valor_descuento', 0.01);
        $inicio = Validator::datetime($data['fecha_inicio'], 'fecha_inicio');
        $fin = Validator::datetime($data['fecha_fin'], 'fecha_fin');
        $montoMinimo = Validator::float($data['monto_minimo'] ?? 0, 'monto_minimo', 0);
        $usosMaximos = isset($data['usos_maximos']) && $data['usos_maximos'] !== null
            ? Validator::int($data['usos_maximos'], 'usos_maximos', 1)
            : null;
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $userId = isset($data['id_usuario_creacion']) ? Validator::int($data['id_usuario_creacion'], 'id_usuario_creacion', 1) : null;

        if ($tipo === 'porcentaje' && $valor > 100) {
            throw new ValidationException('El valor_descuento no puede ser mayor a 100 cuando el tipo es porcentaje');
        }
        if ($inicio > $fin) {
            throw new ValidationException('fecha_inicio no puede ser mayor que fecha_fin');
        }

        $sql = 'INSERT INTO cupones
                (codigo, descripcion, tipo_descuento, valor_descuento, monto_minimo, usos_maximos, usos_actuales, fecha_inicio, fecha_fin, activo, id_usuario_creacion)
                VALUES
                (:codigo, :descripcion, :tipo, :valor, :monto_minimo, :usos_maximos, 0, :inicio, :fin, 1, :user_id)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'codigo' => $codigo,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'tipo' => $tipo,
                'valor' => $valor,
                'monto_minimo' => $montoMinimo,
                'usos_maximos' => $usosMaximos,
                'inicio' => $inicio,
                'fin' => $fin,
                'user_id' => $userId,
            ]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new ValidationException('El código de cupón ya existe');
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
        $coupon = $this->findById($id);
        if (!$coupon) {
            throw new HttpException('Cupón no encontrado', 404);
        }

        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('codigo', $data)) {
            $fields[] = 'codigo = :codigo';
            $params['codigo'] = strtoupper(trim((string) $data['codigo']));
        }
        if (array_key_exists('descripcion', $data)) {
            $fields[] = 'descripcion = :descripcion';
            $params['descripcion'] = trim((string) $data['descripcion']);
        }
        if (array_key_exists('tipo_descuento', $data)) {
            $fields[] = 'tipo_descuento = :tipo';
            $params['tipo'] = Validator::oneOf($data['tipo_descuento'], 'tipo_descuento', ['porcentaje', 'monto_fijo']);
        }
        if (array_key_exists('valor_descuento', $data)) {
            $fields[] = 'valor_descuento = :valor';
            $params['valor'] = Validator::float($data['valor_descuento'], 'valor_descuento', 0.01);
        }
        if (array_key_exists('monto_minimo', $data)) {
            $fields[] = 'monto_minimo = :monto_minimo';
            $params['monto_minimo'] = Validator::float($data['monto_minimo'], 'monto_minimo', 0);
        }
        if (array_key_exists('usos_maximos', $data)) {
            $fields[] = 'usos_maximos = :usos_maximos';
            $params['usos_maximos'] = $data['usos_maximos'] === null ? null : Validator::int($data['usos_maximos'], 'usos_maximos', 1);
        }
        if (array_key_exists('usos_actuales', $data)) {
            $fields[] = 'usos_actuales = :usos_actuales';
            $params['usos_actuales'] = Validator::int($data['usos_actuales'], 'usos_actuales', 0);
        }
        if (array_key_exists('fecha_inicio', $data)) {
            $fields[] = 'fecha_inicio = :inicio';
            $params['inicio'] = Validator::datetime($data['fecha_inicio'], 'fecha_inicio');
        }
        if (array_key_exists('fecha_fin', $data)) {
            $fields[] = 'fecha_fin = :fin';
            $params['fin'] = Validator::datetime($data['fecha_fin'], 'fecha_fin');
        }
        if (array_key_exists('activo', $data)) {
            $fields[] = 'activo = :activo';
            $params['activo'] = (int) ((bool) $data['activo']);
        }
        if (array_key_exists('id_usuario_modificacion', $data)) {
            $fields[] = 'id_usuario_modificacion = :id_usuario_modificacion';
            $params['id_usuario_modificacion'] = Validator::int($data['id_usuario_modificacion'], 'id_usuario_modificacion', 1);
        }

        if ($fields === []) {
            throw new ValidationException('No hay campos para actualizar');
        }

        $stmt = $this->pdo->prepare('UPDATE cupones SET ' . implode(', ', $fields) . ' WHERE id_cupon = :id');
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE cupones SET activo = 0 WHERE id_cupon = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw new HttpException('Cupón no encontrado', 404);
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_cupon, codigo, descripcion, tipo_descuento, valor_descuento, monto_minimo, usos_maximos, usos_actuales, fecha_inicio, fecha_fin, activo
             FROM cupones WHERE id_cupon = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new HttpException('Cupón no encontrado', 404);
        }

        return $row;
    }

    /**
     * @return array<string,mixed>
     */
    public function validateAvailability(int $id, float $orderAmount = 0): array
    {
        $coupon = $this->findById($id);
        $now = date('Y-m-d H:i:s');

        $active = (int) $coupon['activo'] === 1;
        $inWindow = $coupon['fecha_inicio'] <= $now && $coupon['fecha_fin'] >= $now;
        $withinUsage = $coupon['usos_maximos'] === null || (int) $coupon['usos_actuales'] < (int) $coupon['usos_maximos'];
        $meetsAmount = $orderAmount >= (float) $coupon['monto_minimo'];

        return [
            'valido' => $active && $inWindow && $withinUsage && $meetsAmount,
            'detalle' => [
                'activo' => $active,
                'vigente' => $inWindow,
                'limite_uso_ok' => $withinUsage,
                'monto_minimo_ok' => $meetsAmount,
            ],
        ];
    }
}
