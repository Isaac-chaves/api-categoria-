<?php
require_once __DIR__ . '/../configuracion/conexion.php';

/*
 * REPOSITORIO DE PROGRAMADORES.
 * Comprueba si existe un programador con la cedula y contrasena recibidas.
 * Esta es una practica local: las contrasenas se guardan sin cifrar para
 * facilitar el aprendizaje. No utilizar este metodo en sistemas reales.
 */
class ProgramadorRepositorio {

    public function validar($cedula, $contrasena) {
        $conexion = ConexionBD::obtenerConexion();

        // Consultar si existe un programador con esa cedula y contrasena.
        // Los signos ? representan valores que se asignan por separado.
        $sql = "SELECT COUNT(*) FROM Programadores WHERE Cedula = ? AND Contrasena = ?";

        // Preparar la consulta SQL.
        $consulta = $conexion->prepare($sql);

        // Asignar la cedula al primer ? y la contrasena al segundo ?.
        // Se utilizan como texto para conservar valores como "01", "02" y "03".
        $consulta->bindValue(1, $cedula, PDO::PARAM_STR);
        $consulta->bindValue(2, $contrasena, PDO::PARAM_STR);

        // Ejecutar la consulta con los parametros asignados.
        $consulta->execute();

        // Si hay una coincidencia, devolvemos true. Si no, false.
        return $consulta->fetchColumn() > 0;
    }
}
