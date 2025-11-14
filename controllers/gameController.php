<?php

    class gameController{
        private $model;

        // Pregunta aleatoria, se usará para cuando se haya definido a pregunta que queremos preguntar, pues tenerla registrada dentro de la classe
        private $pregunta_aleatoria;
        
        public function __construct(){

        }

        public function reiniciarPartida(){
            // El array de preguntas lo volvemos a poner con todas las preguntas

            
        }

        public function preguntaAleatoria($array_preguntas){
            // Comprovamos que en el arrayd e preguntas, aun quedan preguntas y sino hace un return false Si hay se sigue con el flujo
            if (empty($array_preguntas)) {
                // No hay más preguntas disponibles
                return false; 
            }

            // Seleccionamos un indice aleatorio del array de preguntas almacenadas en session que se pasa por parametro
            $indice_aleatorio = array_rand($array_preguntas);

            
        }
    }

?>
