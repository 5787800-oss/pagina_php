<?php

// Configura los datos de tu conexión Ngrok / MySQL Local aquí:
define("MYSQL_HOST", "0.tcp.ngrok.io"); // <-- Cambia esto por tu host de Ngrok (o "localhost")
define("MYSQL_PORT", 12345);            // <-- Cambia esto por tu puerto de Ngrok (o 3306)
define("MYSQL_USER", "root");           // <-- Tu usuario de MySQL en XAMPP
define("MYSQL_PASSWORD", "");           // <-- Tu contraseña (déjala vacía "" si no usas)
define("MYSQL_DB", "tu_base_de_datos"); // <-- Pon el nombre EXACTO de tu BD en phpMyAdmin

/**
 * Establece la conexión a MySQL usando PDO.
 */
function obtenerConexion(): ?PDO {
    try {
        $dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DB . ";charset=utf8mb4";
        $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a MySQL: " . $e->getMessage());
        return null;
    }
}

/**
 * Crea la tabla 'pedidos' si no existe.
 */
function crearBaseDatos(): void {
    $conexion = obtenerConexion();
    if (!$conexion) return;

    try {
        $sql = "
            CREATE TABLE IF NOT EXISTS pedidos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_cliente VARCHAR(100) NOT NULL,
                correo_cliente VARCHAR(100) NOT NULL,
                item_menu VARCHAR(100) NOT NULL,
                cantidad INT NOT NULL DEFAULT 1,
                fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conexion->exec($sql);
    } catch (PDOException $e) {
        error_log("Error al crear la tabla en MySQL: " . $e->getMessage());
    } finally {
        $conexion = null; // Cerrar conexión
    }
}

/**
 * Inserta un nuevo pedido en la base de datos y retorna su ID.
 */
function guardarPedido(
    string $nombreCliente,
    string $correoCliente,
    string $itemMenu,
    int $cantidad
): ?int {
    $conexion = obtenerConexion();
    if (!$conexion) return null;

    try {
        $sql = "
            INSERT INTO pedidos (nombre_cliente, correo_cliente, item_menu, cantidad)
            VALUES (:nombre_cliente, :correo_cliente, :item_menu, :cantidad)
        ";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre_cliente' => $nombreCliente,
            ':correo_cliente' => $correoCliente,
            ':item_menu'     => $itemMenu,
            ':cantidad'        => $cantidad
        ]);

        return (int)$conexion->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al guardar el pedido en MySQL: " . $e->getMessage());
        return null;
    } finally {
        $conexion = null; // Cerrar conexión
    }
}

// Ejemplo de prueba (equivale a __name__ == '__main__' en Python)
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'] ?? '')) {
    crearBaseDatos();

    $nuevoId = guardarPedido(
        "Juan Pérez",
        "juan.perez@example.com",
        "Iced Caramel Macchiato",
        2
    );

    if ($nuevoId !== null) {
        echo "Pedido guardado exitosamente con id: " . $nuevoId . "\n";
    } else {
        echo "No se pudo guardar el pedido.\n";
    }
}
?>