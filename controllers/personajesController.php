<?php
    // Importamos base de datos y modelo de personajes
    require_once '../config/database.php';
    require '../models/personajesModel.php';
    class PersonajeController{
        private $model;
        public function __construct(){
            $this->model = new Personaje();
        }
        public function obtenerPersonajes(){
            $lista = $this->model->obtenerPersonajes();
            // Al hacer el include, la lista estará disponible en la view de biblioteca.php
            include '../views/biblioteca.php';
        }
    }

?>