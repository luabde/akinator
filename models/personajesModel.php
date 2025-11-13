<?php
    require_once '../config/database.php';
    
    class Personaje{
        private $arrayPersonajes;
        private $db;

        // Definimos el constructor
        public function __construct(){
            // Se tiene que crear una nueva instancia de la base de datos.
            $this->db = conectarDB();
        }

        // Definimos los metodos para los personajes
        public function obtenerPersonajes(){
            $query = 'SELECT * FROM personajes';
            $resultado = mysqli_query($this->db, $query);

            // Convertimos el resultado en un array
            $this->arrayPersonajes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

            return $this->arrayPersonajes;
        }
        
    }
?>