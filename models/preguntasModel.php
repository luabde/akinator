<?php
class PreguntasModel{
    private $db;
    private $preguntas;
    public function __construct(){
        $this->db=Conectar::conexion();
        $this->preguntas.array();
    }

    // Función para obtener una pregunta por ID
    public function getQuestionById($qId){
        $consulta = $this->db->query("SELECT * FROM preguntas WHERE pregunta = $qId;");
        return $consulta;
    }
}
?>