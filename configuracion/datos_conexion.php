<?php
/*
 * Datos de conexion a la base de datos MySQL.
 *
 * En local (XAMPP) se usan los valores predeterminados de abajo.
 * En Render (o en cualquier otro hosting) se configuran con variables
 * de entorno, sin necesidad de modificar este archivo.
 *
 * Variables admitidas (se usa la primera que exista):
 *   Servidor: BD_SERVIDOR | DB_HOST | MYSQLHOST
 *   Puerto:   BD_PUERTO   | DB_PORT | MYSQLPORT
 *   Base:     BD_NOMBRE   | DB_NAME | MYSQLDATABASE
 *   Usuario:  BD_USUARIO  | DB_USER | MYSQLUSER
 *   Clave:    BD_CLAVE    | DB_PASSWORD | MYSQLPASSWORD
 *
 * Tambien se acepta una sola URL de conexion en DATABASE_URL o MYSQL_URL:
 *   mysql://usuario:clave@servidor:puerto/base_de_datos
 *
 * Para conexiones que exigen TLS se puede indicar el certificado CA en
 * BD_SSL_CA o DB_SSL_CA (ruta a un archivo o el contenido PEM completo).
 * Con BD_SSL_VERIFICAR=false se desactiva la verificacion del certificado.
 */

if (!function_exists('leer_variable_entorno')) {
    /*
     * Devuelve el valor de la primera variable de entorno que exista.
     * getenv() funciona en Render; $_ENV y $_SERVER son un respaldo.
     */
    function leer_variable_entorno(array $nombres, $valor_predeterminado = null) {
        foreach ($nombres as $nombre) {
            $valor = getenv($nombre);
            if ($valor !== false && $valor !== "") {
                return $valor;
            }
            if (isset($_ENV[$nombre]) && $_ENV[$nombre] !== "") {
                return $_ENV[$nombre];
            }
            if (isset($_SERVER[$nombre]) && $_SERVER[$nombre] !== "") {
                return $_SERVER[$nombre];
            }
        }
        return $valor_predeterminado;
    }
}

// Algunos proveedores entregan toda la conexion en una sola URL.
$urlBaseDatos = leer_variable_entorno(["DATABASE_URL", "MYSQL_URL"]);
$partesUrl = $urlBaseDatos ? parse_url($urlBaseDatos) : false;
$partesUrl = is_array($partesUrl) ? $partesUrl : [];

// Direccion del equipo donde se encuentra MySQL.
define("BD_SERVIDOR", (string) leer_variable_entorno(
    ["BD_SERVIDOR", "DB_HOST", "MYSQLHOST"],
    $partesUrl["host"] ?? "localhost"
));

// Puerto utilizado por MySQL.
define("BD_PUERTO", (string) leer_variable_entorno(
    ["BD_PUERTO", "DB_PORT", "MYSQLPORT"],
    $partesUrl["port"] ?? "3306"
));

// Nombre de la base de datos que utilizara la API.
define("BD_NOMBRE", (string) leer_variable_entorno(
    ["BD_NOMBRE", "DB_NAME", "MYSQLDATABASE"],
    isset($partesUrl["path"]) ? ltrim($partesUrl["path"], "/") : "ventas"
));

// Usuario y contrasena de MySQL.
define("BD_USUARIO", (string) leer_variable_entorno(
    ["BD_USUARIO", "DB_USER", "MYSQLUSER"],
    isset($partesUrl["user"]) ? urldecode($partesUrl["user"]) : "root"
));

define("BD_CLAVE", (string) leer_variable_entorno(
    ["BD_CLAVE", "DB_PASSWORD", "MYSQLPASSWORD"],
    isset($partesUrl["pass"]) ? urldecode($partesUrl["pass"]) : ""
));

// Certificado CA opcional para conexiones TLS (Aiven, TiDB, etc.).
define("BD_SSL_CA", (string) leer_variable_entorno(["BD_SSL_CA", "DB_SSL_CA"], ""));

// Permite desactivar la verificacion del certificado con BD_SSL_VERIFICAR=false.
define("BD_SSL_VERIFICAR", filter_var(
    leer_variable_entorno(["BD_SSL_VERIFICAR", "DB_SSL_VERIFY"], "true"),
    FILTER_VALIDATE_BOOLEAN
));
