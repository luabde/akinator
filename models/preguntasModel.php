<?php
    require_once '../config/database.php';
    class preguntasModel{
        private $db;

        public function __construct(){
            $this->db = conectarDB();
        }

        public function ObtenerTodasIdsPreguntas(){
        $query = 'SELECT id FROM preguntas;';
        $resultado = mysqli_query($this->db, $query);
        $preguntas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        // Extraer solo los IDs
        $ids = [];
        foreach ($preguntas as $pregunta) {
            $ids[] = $pregunta['id'];
        }

        return $ids;
    }

    public function obtenerPreguntaPorId($id) {
        $query = "SELECT id, texto, columna_asociada FROM preguntas WHERE id = $id";
        $resultado = mysqli_query($this->db, $query);
        
        // Devolver el primer resultado
        return mysqli_fetch_assoc($resultado);
    }

    }

?>