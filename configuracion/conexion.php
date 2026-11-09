<?php
// Cargar los datos necesarios para conectarse a MySQL.
require_once __DIR__ . '/datos_conexion.php';

/*
 * Clase encargada de abrir la conexion a la base de datos.
 * De esta manera, no repetimos la conexion en cada metodo del repositorio.
 */
class ConexionBD {

    /*
     * Devuelve un objeto PDO conectado a MySQL.
     * PDO permite utilizar consultas SQL preparadas con parametros.
     */
    public static function obtenerConexion() {
        // Crear la conexion indicando servidor, puerto, base y codificacion.
        return new PDO(
            "mysql:host=" . BD_SERVIDOR . ";port=" . BD_PUERTO
                . ";dbname=" . BD_NOMBRE . ";charset=utf8mb4",
            BD_USUARIO,
            BD_CLAVE,
            // Si ocurre un problema de MySQL, PDO lanzara una excepcion.
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
