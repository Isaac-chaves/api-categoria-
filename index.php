<?php
/*
 * Punto de entrada informativo. Render puede usar esta ruta como
 * health check porque no depende de la base de datos.
 */
header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "api" => "Categorias",
    "estado" => "en linea",
    "php" => PHP_VERSION,
    "endpoints" => [
        "GET /controlador/categoria.php" => "Lista todas las categorias",
        "GET /controlador/categoria.php?codigo=1" => "Consulta una categoria",
        "POST /controlador/categoria.php" => "Inserta una categoria",
        "PUT /controlador/categoria.php" => "Actualiza una categoria",
        "DELETE /controlador/categoria.php" => "Elimina una categoria",
    ],
    "encabezados_requeridos" => [
        "X-Cedula: 1",
        "X-Contrasena: 01",
    ],
    "diagnostico" => "/salud.php",
    "documentacion" => "Consulte DEPLOY_RENDER.md en el repositorio",
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

echo "\n";
