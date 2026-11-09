<?php
/*
 * MODELO: Categoria.
 * Representa una categoria mediante su codigo y su nombre.
 * Esta clase no contiene instrucciones SQL ni se conecta a MySQL.
 */
class Categoria {

    // Atributos privados: se consultan y modifican mediante metodos publicos.
    private $codigo;
    private $nombre;

    /*
     * Constructor con parametros opcionales.
     * En PHP se utiliza un unico constructor para ambos casos:
     * new Categoria()                     // Sin datos iniciales.
     * new Categoria(1, "Computadoras")   // Con codigo y nombre.
     */
    public function __construct($codigo = null, $nombre = null) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    /*
     * Devuelve el codigo de la categoria.
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /*
     * Modifica el codigo de la categoria.
     * $codigo: nuevo codigo que se almacenara en el objeto.
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    /*
     * Devuelve el nombre de la categoria.
     */
    public function getNombre() {
        return $this->nombre;
    }

    /*
     * Modifica el nombre de la categoria.
     * $nombre: nuevo nombre que se almacenara en el objeto.
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}
