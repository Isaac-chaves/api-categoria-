<?php
// Importar la clase que proporciona la conexion a MySQL.
require_once __DIR__ . '/../configuracion/conexion.php';

/*
 * REPOSITORIO: CategoriaRepositorio.
 * Aqui se escriben todas las instrucciones SQL de la tabla categoria.
 * El controlador llama a estos metodos y no necesita escribir SQL.
 */
class CategoriaRepositorio {

    /*
     * INSERT: guarda una categoria nueva en la base de datos.
     * Recibe un objeto Categoria con codigo y nombre.
     */
    public function guardar($categoria) {
        $conexion = ConexionBD::obtenerConexion();
        $sql = "INSERT INTO categoria (codigo, nombre) VALUES (?, ?)";

        // Preparar la consulta. Los signos ? representan los datos a insertar.
        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $categoria->getCodigo(), PDO::PARAM_INT);
        $consulta->bindValue(2, $categoria->getNombre(), PDO::PARAM_STR);
        $consulta->execute(); // Ejecutar el INSERT en MySQL.
    }

    /*
     * SELECT: obtiene todas las categorias de la tabla.
     * Devuelve una lista de filas, cada una con codigo y nombre.
     */
    public function listar() {
        $conexion = ConexionBD::obtenerConexion();
        $sql = "SELECT codigo, nombre FROM categoria ORDER BY codigo";

        $consulta = $conexion->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC); // Devolver todas las filas.
    }

    /*
     * SELECT: busca una sola categoria por su codigo.
     * Devuelve la fila encontrada o false si ese codigo no existe.
     */
    public function buscar($codigo) {
        $conexion = ConexionBD::obtenerConexion();
        $sql = "SELECT codigo, nombre FROM categoria WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $codigo, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC); // Obtener una sola fila.
    }

    /*
     * UPDATE: cambia el nombre de una categoria existente.
     * Se busca por el codigo del objeto y se coloca su nuevo nombre.
     * Devuelve el numero de filas que realmente se modificaron.
     */
    public function actualizar($categoria) {
        $conexion = ConexionBD::obtenerConexion();
        $sql = "UPDATE categoria SET nombre = ? WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $categoria->getNombre(), PDO::PARAM_STR);
        $consulta->bindValue(2, $categoria->getCodigo(), PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount(); // Cantidad de filas modificadas.
    }

    /*
     * DELETE: elimina una categoria utilizando su codigo.
     * Devuelve 1 si se elimino la fila o 0 si no existia.
     */
    public function eliminar($codigo) {
        $conexion = ConexionBD::obtenerConexion();
        $sql = "DELETE FROM categoria WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $codigo, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount(); // Cantidad de filas eliminadas.
    }
}
