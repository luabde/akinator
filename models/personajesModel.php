<?php
    require '../config/database.php';
    
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

    $personajes = new Personaje();
    $lista = $personajes->obtenerPersonajes();
    // var_dump($lista);
    foreach($lista as $personaje){
         echo "
            <div class='card'>
                <div class='card-inner'>
                    <img src='./{$personaje['imagen_url']}' alt='{$personaje['nombre']}' class='foto'>  
                    <h3>{$personaje['nombre']}</h3>
                    <p>{$personaje['descripcion']}</p>
                </div>
            </div>";
    }

    
// class PersonajesModel{
//     private $db;
//     private $personajes;

//     public function __construct(){
//         $this->db=Conectar::conexion();
//         $this->personajes = $this->personajes.array();
//     }

//     // Para obtener todos los personajes (se usará para cuando se quiera mostrar toda la info o imagenes de los personajes)
//     public function getAllPersonajes(){
//         $consulta = $this->db->query("SELECT * FROM personajes");
//     }

//     // Función para obtener un personaje por id
//     public function getPersonajeById($pId){
//         $consulta = $this->db->query("SELECT * FROM personajes WHERE id = $pId");
//     }

// }
?>