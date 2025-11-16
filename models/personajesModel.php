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

        public function filtrarPersonajes($preguntas_info) {
            // Construir la query base
            $query = "SELECT * FROM personajes WHERE 1=1";
            
            // Por cada pregunta respondida, añadir un AND
            foreach ($preguntas_info as $id_pregunta => $info) {
                // Solo filtrar si ya hay respuesta del usuario
                if ($info['respuestaUser'] !== null) {
                    $columna = mysqli_real_escape_string($this->db, $info['columna']);
                    $respuesta = (int)$info['respuestaUser'];
                    $query .= " AND `$columna` = $respuesta";
                }
            }
            
            $query .= " ORDER BY nombre";
            
            $resultado = mysqli_query($this->db, $query);
            
            if (!$resultado) {
                // Debug en caso de error
                echo "Error en query: " . mysqli_error($this->db);
                return [];
            }
            
            return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
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