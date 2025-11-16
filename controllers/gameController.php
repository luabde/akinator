<?php
    require_once '../models/preguntasModel.php';
    require_once '../models/personajesModel.php';
    session_start();
    class gameController{
        private $preguntasModel;
        private $personajesModel;

        // Pregunta aleatoria, hace referencia al id de la pregunta que se ha seleccionado como aleatoria
        private $pregunta_aleatoria;
        
        public function __construct(){
            $this->preguntasModel = new preguntasModel();
            $this->personajesModel = new Personaje();
        }

        // Getters y setters
        public function getPreguntaAleatoria() {
            return $this->pregunta_aleatoria;
        }

        public function iniciarJuego() {
            // 1. Obtener todas las IDs de preguntas de la BD
            $preguntas = $this->preguntasModel->obtenerTodasIdsPreguntas();
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
            // Comprovamos que en el array de preguntas, aun quedan preguntas y sino hace un return false Si hay se sigue con el flujo
            if (empty($preguntas)) {
                // No hay más preguntas disponibles
                return false; 
            }

            // Seleccionamos un indice aleatorio del array de preguntas almacenadas en session que se pasa por parametro
            $indice_aleatorio = array_rand($preguntas);

            // Obtenemos el id de la pregunta aleatoria a traves del indice generado aleatoriamente
            $id_pregunta = $preguntas[$indice_aleatorio];

            // Obtenemos por id toda la información de la pregunta y la guardamos en el atributo pregunta_aleatoria
            $this->pregunta_aleatoria = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);

            // Comrpovamos que el array asociativo exista, sino lo creamos vacio.
            if (!isset($_SESSION['preguntas_info'])) {
                $_SESSION['preguntas_info'] = [];
            }
            var_dump($this->pregunta_aleatoria);
            // Ahora actualizamos preguntas respondidas con el id de la pregunta, y la columna. La respuesta del user de primeras se queda a null, porque es cuando ya se responde que se actualiza.
            $_SESSION['preguntas_info'][$id_pregunta] = [
                'columna' => $this->pregunta_aleatoria['columna_asociada'],
                'respuestaUser' => null
            ];

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

         public function procesarRespuesta() {
            // 1. Obtener la información de la pregunta actual y la respuesta
            $pregunta_id = $_SESSION['pregunta_actual']['id'];
            $respuestaUser = $_POST['respuesta'];
            
            // 2. Convertir 'si'/'no' a 1/0
            $respuesta_valor = ($respuestaUser === 'si') ? 1 : 0;
            
            // 3. Guardar la respuesta en preguntas_info
            $_SESSION['preguntas_info'][$pregunta_id]['respuestaUser'] = $respuesta_valor;
            
            // 4. Incrementar contador de preguntas respondidas
            $_SESSION['preguntas_respondidas']++;
            
            // 5. FILTRAR PERSONAJES según las respuestas
            $personajes_restantes = $this->personajesModel->filtrarPersonajes($_SESSION['preguntas_info']);
            $num_personajes = count($personajes_restantes);
            
            // Actualizar número de personajes posibles
            $_SESSION['personajes_posibles'] = $num_personajes;
            
            // 6. DECIDIR QUÉ HACER según cuántos personajes quedan
            if ($num_personajes == 1) {
                // SOLO QUEDA 1 - ADIVINAR
                $_SESSION['personaje_adivinado'] = $personajes_restantes[0];
                $_SESSION['vista'] = 'adivinar';
                
            } elseif ($num_personajes == 0) {
                // NO QUEDAN PERSONAJES
                $_SESSION['vista'] = 'sin_resultados';
                
            } elseif ($num_personajes >= 2 && $num_personajes <= 5) {
                // 📋 ENTRE 2 Y 5 - MOSTRAR LISTA
                $_SESSION['personajes_posibles_lista'] = $personajes_restantes;
                $_SESSION['vista'] = 'lista';
                
            } else {
                // ❓ MÁS DE 5 - SIGUIENTE PREGUNTA
                if (empty($_SESSION['preguntas_disponibles'])) {
                    // No quedan preguntas - mostrar lista
                    $_SESSION['personajes_posibles_lista'] = $personajes_restantes;
                    $_SESSION['vista'] = 'lista';
                } else {
                    // Generar siguiente pregunta
                    $siguiente_pregunta = $this->preguntaAleatoria();
                    if ($siguiente_pregunta) {
                        $_SESSION['pregunta_actual'] = $siguiente_pregunta;
                        $_SESSION['vista'] = 'pregunta';
                    } else {
                        $_SESSION['vista'] = 'error';
                    }
                }
            }
            
            // 7. Redirigir al index
            header('Location: ../public/index.php');
            exit;
        }
    }

    $controlador = new gameController();
    // Cuando el post sea inicio, se iniciará el juego
    if (isset($_POST['inicio'])) {
        $controlador->iniciarJuego();
    }

    // Cuando respuesta este set, significa que se ha respondido si o no a una pregunta
    if(isset($_POST['respuesta'])){
        $controlador->procesarRespuesta();
    }
?>
