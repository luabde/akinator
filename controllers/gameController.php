<?php
    require_once '../models/preguntasModel.php';
    session_start();
    class gameController{
        private $model;

        // Pregunta aleatoria, hace referencia al id de la pregunta que se ha seleccionado como aleatoria
        private $pregunta_aleatoria;
        
        public function __construct(){
            $this->model = new preguntasModel();
        }

        // Getters y setters
        public function getPreguntaAleatoria() {
            return $this->pregunta_aleatoria;
        }

        public function iniciarJuego() {
            // 1. Obtener todas las IDs de preguntas de la BD
            $preguntas = $this->model->obtenerTodasIdsPreguntas();
            $_SESSION['preguntas_disponibles'] = $preguntas;
            
            // 2. Reiniciar respuestas del usuario
            $_SESSION['respuestas_usuario'] = [];
            
            // 3. Generar primera pregunta aleatoria
            $pregunta = $this->preguntaAleatoria();
            
            if ($pregunta) {
                $_SESSION['pregunta_actual'] = $pregunta;
                $_SESSION['vista'] = 'pregunta';
                $_SESSION['personajes_posibles'] = 30;
                $_SESSION['preguntas_respondidas'] = 0;
            } else {
                $_SESSION['vista'] = 'error';
            }
            
            // 4. Redirigir al index
            header('Location: ../public/index.php');
            exit;
        }

        public function preguntaAleatoria(){
            $preguntas = $_SESSION['preguntas_disponibles'];
            // Comprovamos que en el arrayd e preguntas, aun quedan preguntas y sino hace un return false Si hay se sigue con el flujo
            if (empty($preguntas)) {
                // No hay más preguntas disponibles
                return false; 
            }

            // Seleccionamos un indice aleatorio del array de preguntas almacenadas en session que se pasa por parametro
            $indice_aleatorio = array_rand($preguntas);

            // Obtenemos el id de la pregunta aleatoria a traves del indice generado aleatoriamente
            $id_pregunta = $preguntas[$indice_aleatorio];

            // Obtenemos por id toda la información de la pregunta y la guardamos en el atributo pregunta_aleatoria
            $this->pregunta_aleatoria = $this->model->obtenerPreguntaPorId($id_pregunta);
            
            // Ahora eliminamos de las rpeguntas disponibles el indice aleatorio. Pero los indices seguramente saltaran al que se 
            // ha eliminado, por eso hacemos array_values para que los indices se reseteen y sean seguidos

            /*
                Ejemplo:
                sin values eliminamod el indice 2 --> 0 => 1, 1 => 2, 3 => 4, 4 => 5 Índices: 0, 1, 3, 4 ← ¡Hay un "hueco"!
                Con values eliminamos el indice 2 --> [0 => 1, 1 => 2, 3 => 4, 4 => 5]  // índices: 0, 1, 3, 4
            */
            unset($_SESSION['preguntas_disponibles'][$indice_aleatorio]);
            $_SESSION['preguntas_disponibles'] = array_values($_SESSION['preguntas_disponibles']);

            // Se devuelve la pregunta completa, es decir junto a su id como con el texto de esta
            return $this->pregunta_aleatoria;
            
        }
    }

    $controlador = new gameController();

    if (isset($_POST['inicio'])) {
        $controlador->iniciarJuego();
    } else {
        // Si no hay acción válida, volver al index
        header('Location: ../public/index.php');
        exit;
    }
?>
