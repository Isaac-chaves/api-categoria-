<?php
/*
 * Diagnostico del servicio: comprueba la conexion a MySQL y que existan
 * las tablas necesarias. Se puede abrir desde el navegador:
 *   https://TU-SERVICIO.onrender.com/salud.php
 */
header("Content-Type: application/json; charset=utf-8");

try {
    require_once __DIR__ . '/configuracion/conexion.php';

    $conexion = ConexionBD::obtenerConexion();
    $conexion->query("SELECT 1");

    $tablas = $conexion->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $faltantes = array_values(array_diff(["categoria", "Programadores"], $tablas));

    if (count($faltantes) > 0) {
        // HTTP 503: la base responde, pero la API aun no puede funcionar.
        http_response_code(503);
        echo json_encode([
            "estado" => "degradado",
            "base_de_datos" => "conectada",
            "tablas_faltantes" => $faltantes,
            "sugerencia" => "Ejecute 'php scripts/init_db.php' o defina AUTO_INIT_DB=true",
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n";
        exit;
    }

    echo json_encode([
        "estado" => "ok",
        "base_de_datos" => "conectada",
        "base" => BD_NOMBRE,
        "php" => PHP_VERSION,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n";
} catch (Throwable $error) {
    // El detalle se envia al log del servicio, no al cliente.
    error_log("Fallo en /salud.php: " . $error->getMessage());

    // HTTP 503: el servicio esta arriba pero la base no responde.
    http_response_code(503);
    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudo conectar con MySQL",
        "sugerencia" => "Revise las variables DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASSWORD en Render",
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n";
}
