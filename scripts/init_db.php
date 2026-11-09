<?php
/*
 * Inicializa la base de datos configurada: crea las tablas e inserta los
 * datos iniciales. Es equivalente a ejecutar database.sql, pero sin crear
 * la base de datos (el hosting ya debe tenerla creada).
 *
 * Uso local:
 *   php scripts/init_db.php
 *
 * En Render se ejecuta automaticamente al arrancar si la variable de
 * entorno AUTO_INIT_DB vale "true", o manualmente desde la Shell del
 * servicio:
 *   php scripts/init_db.php
 */

// Este archivo solo debe ejecutarse por consola, nunca desde el navegador.
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Acceso no permitido\n");
}

require_once __DIR__ . '/../configuracion/conexion.php';

$consultas = [
    // Tabla de categorias.
    "CREATE TABLE IF NOT EXISTS categoria (
        codigo INT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Cuatro categorias iniciales para practicar GET.
    "INSERT IGNORE INTO categoria (codigo, nombre) VALUES
        (1, 'Computadoras'),
        (2, 'Telefonos'),
        (3, 'Impresoras'),
        (4, 'Accesorios')",

    // Tabla para comprobar los encabezados X-Cedula y X-Contrasena.
    "CREATE TABLE IF NOT EXISTS Programadores (
        Cedula VARCHAR(20) PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Contrasena VARCHAR(20) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Usuarios de prueba para Postman.
    "INSERT IGNORE INTO Programadores (Cedula, Nombre, Contrasena) VALUES
        ('1', 'Luis', '01'),
        ('2', 'Ana', '02'),
        ('3', 'Maria', '03')",
];

// La base de datos puede tardar en estar disponible tras un despliegue.
$intentos = max(1, (int) (getenv("DB_INIT_INTENTOS") ?: 20));
$conexion = null;

for ($intento = 1; $intento <= $intentos; $intento++) {
    try {
        $conexion = ConexionBD::obtenerConexion();
        break;
    } catch (PDOException $error) {
        if ($intento === $intentos) {
            fwrite(STDERR, "No se pudo conectar a MySQL (" . BD_SERVIDOR . ":" . BD_PUERTO . "/" . BD_NOMBRE . "): " . $error->getMessage() . "\n");
            exit(1);
        }

        fwrite(STDERR, "Esperando a MySQL... intento " . $intento . " de " . $intentos . "\n");
        sleep(3);
    }
}

try {
    foreach ($consultas as $sql) {
        $conexion->exec($sql);
    }
} catch (PDOException $error) {
    fwrite(STDERR, "Error al inicializar la base de datos: " . $error->getMessage() . "\n");
    exit(1);
}

echo "Base de datos inicializada correctamente: " . BD_NOMBRE . "\n";
