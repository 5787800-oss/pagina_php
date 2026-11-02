<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
    exit;
}


require_once "enviar_correo.php";


function obtenerConexionBD() {
    try {
      
        $pdo = new PDO("sqlite:" . __DIR__ . "/restaurante.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la BD: " . $e->getMessage());
        return null;
    }
}

function crearBaseDatos() {
    $pdo = obtenerConexionBD();
    if ($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre_cliente TEXT NOT NULL,
            correo_cliente TEXT NOT NULL,
            item_menu TEXT NOT NULL,
            cantidad INTEGER NOT NULL,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
    }
}

function guardarPedido(string $nombreCliente, string $correoCliente, string $itemMenu, int $cantidad) {
    $pdo = obtenerConexionBD();
    if (!$pdo) return null;

    try {
        $sql = "INSERT INTO pedidos (nombre_cliente, correo_cliente, item_menu, cantidad) VALUES (:nombre, :correo, :item, :cantidad)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => $nombreCliente,
            ':correo'   => $correoCliente,
            ':item'     => $itemMenu,
            ':cantidad' => $cantidad
        ]);
        return (int)$pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al guardar el pedido: " . $e->getMessage());
        return null;
    }
}


crearBaseDatos();




$jsonInput = file_get_contents("php://input");
$data = json_decode($jsonInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "El cuerpo de la petición debe ser un JSON válido."]);
    exit;
}


$nombre = trim($data["nombre"] ?? "");
$correo = trim($data["correo"] ?? "");
$item   = trim($data["platillo"] ?? $data["item"] ?? "");
$cantidadRaw = $data["cantidad"] ?? null;


$camposFaltantes = [];
if (empty($nombre)) $camposFaltantes[] = "nombre";
if (empty($correo)) $camposFaltantes[] = "correo";
if (empty($item))   $camposFaltantes[] = "platillo/item";
if ($cantidadRaw === null) $camposFaltantes[] = "cantidad";

if (!empty($camposFaltantes)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Faltan campos requeridos o están vacíos.",
        "campos_faltantes" => $camposFaltantes
    ]);
    exit;
}


if (!filter_var($cantidadRaw, FILTER_VALIDATE_INT) || (int)$cantidadRaw < 1) {
    http_response_code(400);
    echo json_encode(["error" => "La cantidad debe ser un número entero válido mayor a 0."]);
    exit;
}
$cantidad = (int)$cantidadRaw;


$pedidoId = guardarPedido($nombre, $correo, $item, $cantidad);

if ($pedidoId === null) {
    http_response_code(500);
    echo json_encode(["error" => "Ocurrió un error al guardar el pedido en la base de datos."]);
    exit;
}

$itemConCantidad = "{$cantidad}x {$item}";
$correoEnviado = false;

try {
    $correoEnviado = enviarCorreoConfirmacion($correo, $nombre, $itemConCantidad);
} catch (Exception $e) {
    error_log("Advertencia: Falló el envío de correo, pero el pedido se guardó. Detalle: " . $e->getMessage());
    $correoEnviado = false;
}

// Responder con éxito (HTTP 201 Created)
http_response_code(201);
echo json_encode([
    "mensaje" => "Pedido creado exitosamente.",
    "id" => $pedidoId,
    "correo_confirmacion_enviado" => $correoEnviado
]);
?>