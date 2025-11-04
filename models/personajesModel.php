<?php
class PersonajesModel{
    private $db;
    private $personajes;

    public function __construct(){
        $this->db=Conectar::conexion();
        $this->personajes = $this->personajes.array();
    }

    // Para obtener todos los personajes (se usará para cuando se quiera mostrar toda la info o imagenes de los personajes)
    public function getAllPersonajes(){
        $consulta = $this->db->query("SELECT * FROM personajes");
    }

}
?>