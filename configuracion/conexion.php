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
        $dsn = "mysql:host=" . BD_SERVIDOR . ";port=" . BD_PUERTO
            . ";dbname=" . BD_NOMBRE . ";charset=utf8mb4";

        $opciones = [
            // Si ocurre un problema de MySQL, PDO lanzara una excepcion.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Evitar que una conexion lenta bloquee la peticion indefinidamente.
            PDO::ATTR_TIMEOUT => 10,
        ];

        // Muchos proveedores en la nube exigen TLS.
        if (BD_SSL_CA !== "") {
            $rutaCa = self::prepararCertificado(BD_SSL_CA);
            if ($rutaCa !== null) {
                $opciones[PDO::MYSQL_ATTR_SSL_CA] = $rutaCa;
            }
        }

        if (!BD_SSL_VERIFICAR && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        return new PDO($dsn, BD_USUARIO, BD_CLAVE, $opciones);
    }

    /*
     * Si el valor recibido es el contenido PEM del certificado, lo guarda en
     * un archivo temporal y devuelve su ruta. Si ya es una ruta, la devuelve.
     */
    private static function prepararCertificado($valor) {
        if (strpos($valor, "BEGIN CERTIFICATE") === false) {
            return $valor;
        }

        $ruta = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . "mysql-ca.pem";

        if (file_put_contents($ruta, $valor) === false) {
            return null;
        }

        return $ruta;
    }
}
