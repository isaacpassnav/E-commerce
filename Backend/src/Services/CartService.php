<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Support\Validator;
use PDO;
use Throwable;

final class CartService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<string,mixed>
     */
    public function createOrGetActiveCart(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT id_carrito, id_usuario, estado, fecha_creacion FROM carrito WHERE id_usuario = :id_usuario AND estado = :estado LIMIT 1');
        $stmt->execute([
            'id_usuario' => $userId,
            'estado' => 'activo',
        ]);
        $cart = $stmt->fetch();

        if ($cart) {
            return $cart;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO carrito (id_usuario, fecha_creacion, estado)
             VALUES (:id_usuario, :fecha_creacion, :estado)'
        );
        $insert->execute([
            'id_usuario' => $userId,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'estado' => 'activo',
        ]);

        return $this->findCartById((int) $this->pdo->lastInsertId());
    }

    /**
     * @return array<string,mixed>
     */
    public function addItem(int $cartId, int $productId, int $quantity): array
    {
        $cart = $this->findCartById($cartId);
        if (($cart['estado'] ?? null) !== 'activo') {
            throw new ValidationException('Solo se puede agregar productos a un carrito activo');
        }

        $product = $this->findProductById($productId);
        if ((int) $product['stock'] < $quantity) {
            throw new ValidationException('Stock insuficiente');
        }

        $existing = $this->pdo->prepare(
            'SELECT id_detalle_carrito, cantidad FROM detalle_carrito
             WHERE id_carrito = :id_carrito AND id_producto = :id_producto LIMIT 1'
        );
        $existing->execute([
            'id_carrito' => $cartId,
            'id_producto' => $productId,
        ]);
        $row = $existing->fetch();

        if ($row) {
            $newQty = (int) $row['cantidad'] + $quantity;
            if ((int) $product['stock'] < $newQty) {
                throw new ValidationException('Stock insuficiente para la cantidad total del carrito');
            }

            $update = $this->pdo->prepare(
                'UPDATE detalle_carrito SET cantidad = :cantidad
                 WHERE id_detalle_carrito = :id_detalle'
            );
            $update->execute([
                'cantidad' => $newQty,
                'id_detalle' => (int) $row['id_detalle_carrito'],
            ]);
        } else {
            $insert = $this->pdo->prepare(
                'INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad)
                 VALUES (:id_carrito, :id_producto, :cantidad)'
            );
            $insert->execute([
                'id_carrito' => $cartId,
                'id_producto' => $productId,
                'cantidad' => $quantity,
            ]);
        }

        return $this->getCartSummary($cartId);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function createAddress(array $data): array
    {
        Validator::require($data, ['id_usuario', 'calle', 'ciudad', 'tipo']);
        $userId = Validator::int($data['id_usuario'], 'id_usuario', 1);
        $tipo = Validator::oneOf($data['tipo'], 'tipo', ['envio', 'facturacion']);

        $stmt = $this->pdo->prepare(
            'INSERT INTO direcciones (id_usuario, calle, telefono, ciudad, provincia, codigo_postal, pais, tipo)
             VALUES (:id_usuario, :calle, :telefono, :ciudad, :provincia, :codigo_postal, :pais, :tipo)'
        );
        $stmt->execute([
            'id_usuario' => $userId,
            'calle' => trim((string) $data['calle']),
            'telefono' => isset($data['telefono']) ? trim((string) $data['telefono']) : null,
            'ciudad' => trim((string) $data['ciudad']),
            'provincia' => isset($data['provincia']) ? trim((string) $data['provincia']) : null,
            'codigo_postal' => isset($data['codigo_postal']) ? trim((string) $data['codigo_postal']) : null,
            'pais' => isset($data['pais']) ? trim((string) $data['pais']) : null,
            'tipo' => $tipo,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $get = $this->pdo->prepare('SELECT * FROM direcciones WHERE id_direccion = :id LIMIT 1');
        $get->execute(['id' => $id]);
        return $get->fetch() ?: [];
    }

    /**
     * @return array<string,mixed>
     */
    public function checkout(int $cartId, int $addressId): array
    {
        $cart = $this->findCartById($cartId);
        if (($cart['estado'] ?? null) !== 'activo') {
            throw new ValidationException('El carrito no está activo');
        }

        $itemsStmt = $this->pdo->prepare(
            'SELECT dc.id_producto, dc.cantidad, p.precio, p.stock
             FROM detalle_carrito dc
             JOIN productos p ON p.id_producto = dc.id_producto
             WHERE dc.id_carrito = :id_carrito'
        );
        $itemsStmt->execute(['id_carrito' => $cartId]);
        $items = $itemsStmt->fetchAll();
        if ($items === []) {
            throw new ValidationException('El carrito está vacío');
        }

        $total = 0.0;
        foreach ($items as $item) {
            if ((int) $item['stock'] < (int) $item['cantidad']) {
                throw new ValidationException('Stock insuficiente al generar pedido');
            }
            $total += ((float) $item['precio']) * ((int) $item['cantidad']);
        }

        try {
            $this->pdo->beginTransaction();

            $orderStmt = $this->pdo->prepare(
                'INSERT INTO pedidos (id_usuario, fecha_pedido, estado, total, id_direccion)
                 VALUES (:id_usuario, :fecha_pedido, :estado, :total, :id_direccion)'
            );
            $orderStmt->execute([
                'id_usuario' => (int) $cart['id_usuario'],
                'fecha_pedido' => date('Y-m-d H:i:s'),
                'estado' => 'pendiente',
                'total' => $total,
                'id_direccion' => $addressId,
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            $detailStmt = $this->pdo->prepare(
                'INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
                 VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)'
            );
            $stockStmt = $this->pdo->prepare(
                'UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto'
            );

            foreach ($items as $item) {
                $detailStmt->execute([
                    'id_pedido' => $orderId,
                    'id_producto' => (int) $item['id_producto'],
                    'cantidad' => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio'],
                ]);
                $stockStmt->execute([
                    'cantidad' => (int) $item['cantidad'],
                    'id_producto' => (int) $item['id_producto'],
                ]);
            }

            $updateCart = $this->pdo->prepare('UPDATE carrito SET estado = :estado WHERE id_carrito = :id_carrito');
            $updateCart->execute([
                'estado' => 'finalizado',
                'id_carrito' => $cartId,
            ]);

            $clear = $this->pdo->prepare('DELETE FROM detalle_carrito WHERE id_carrito = :id_carrito');
            $clear->execute(['id_carrito' => $cartId]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return [
            'id_pedido' => $orderId,
            'total' => round($total, 2),
            'estado' => 'pendiente',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function confirmOrder(int $orderId): array
    {
        $stmt = $this->pdo->prepare('UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido');
        $stmt->execute([
            'estado' => 'pagado',
            'id_pedido' => $orderId,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new HttpException('Pedido no encontrado', 404);
        }

        $get = $this->pdo->prepare('SELECT id_pedido, estado, total, id_usuario, id_direccion FROM pedidos WHERE id_pedido = :id LIMIT 1');
        $get->execute(['id' => $orderId]);
        return $get->fetch() ?: [];
    }

    /**
     * @return array<string,mixed>
     */
    public function getCartSummary(int $cartId): array
    {
        $cart = $this->findCartById($cartId);
        $stmt = $this->pdo->prepare(
            'SELECT dc.id_producto, dc.cantidad, p.nombre, p.precio
             FROM detalle_carrito dc
             JOIN productos p ON p.id_producto = dc.id_producto
             WHERE dc.id_carrito = :id_carrito'
        );
        $stmt->execute(['id_carrito' => $cartId]);
        $items = $stmt->fetchAll();

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['precio']) * ((int) $item['cantidad']);
        }

        return [
            'carrito' => $cart,
            'items' => $items,
            'subtotal' => round($subtotal, 2),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function findCartById(int $cartId): array
    {
        $stmt = $this->pdo->prepare('SELECT id_carrito, id_usuario, estado, fecha_creacion FROM carrito WHERE id_carrito = :id LIMIT 1');
        $stmt->execute(['id' => $cartId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Carrito no encontrado', 404);
        }

        return $row;
    }

    /**
     * @return array<string,mixed>
     */
    private function findProductById(int $productId): array
    {
        $stmt = $this->pdo->prepare('SELECT id_producto, nombre, precio, stock FROM productos WHERE id_producto = :id LIMIT 1');
        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException('Producto no encontrado', 404);
        }

        return $row;
    }
}
