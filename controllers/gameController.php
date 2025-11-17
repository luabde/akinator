<?php
require_once '../models/preguntasModel.php';
require_once '../models/personajesModel.php';
session_start();

class gameController {

    private $preguntasModel;
    private $personajesModel;
    private $pregunta_aleatoria;

    public function __construct() {
        $this->preguntasModel = new preguntasModel();
        $this->personajesModel = new Personaje();
    }

    public function getPreguntaAleatoria() {
        return $this->pregunta_aleatoria;
    }

    public function iniciarJuego() {
        // 1. Obtener todas las IDs de preguntas
        $preguntas = $this->preguntasModel->obtenerTodasIdsPreguntas();
        $_SESSION['preguntas_disponibles'] = $preguntas;

        // 2. Reiniciar respuestas del usuario
        $_SESSION['respuestas_usuario'] = [];
        $_SESSION['preguntas_info'] = [];
        $_SESSION['preguntas_respondidas'] = 0;

        // 3. Inicializar número de personajes posibles
        $_SESSION['personajes_posibles'] = 30;

        // 4. Generar primera pregunta
        $pregunta = $this->preguntaAleatoria();

        if ($pregunta) {
            $_SESSION['pregunta_actual'] = $pregunta;
            $_SESSION['vista'] = 'pregunta';
        } else {
            $_SESSION['vista'] = 'error';
        }

        header('Location: ../public/index.php');
        exit;
    }

    public function preguntaAleatoria() {
        $preguntas = $_SESSION['preguntas_disponibles'];

        if (empty($preguntas)) {
            return false; // NO QUEDAN PREGUNTAS
        }

        $indice_aleatorio = array_rand($preguntas);
        $id_pregunta = $preguntas[$indice_aleatorio];

        $this->pregunta_aleatoria = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);

        // Guardar pregunta en el log interno
        $_SESSION['preguntas_info'][$id_pregunta] = [
            'columna' => $this->pregunta_aleatoria['columna_asociada'],
            'respuestaUser' => null
        ];

        // Eliminarla del listado y reindexar
        unset($_SESSION['preguntas_disponibles'][$indice_aleatorio]);
        $_SESSION['preguntas_disponibles'] = array_values($_SESSION['preguntas_disponibles']);

        return $this->pregunta_aleatoria;
    }


    public function procesarRespuesta() {
        // 1. Datos de la pregunta actual
        $pregunta_id = $_SESSION['pregunta_actual']['id'];
        $respuestaUser = $_POST['respuesta'];

        // 2. Convertir si/no en 1/0
        if ($respuestaUser === 'si') {
            $valor = 1;
        } elseif ($respuestaUser === 'no') {
            $valor = 0;
        } else {
            $valor = null; // o maneja error
        }


        // 3. Guardar respuesta
        $_SESSION['preguntas_info'][$pregunta_id]['respuestaUser'] = $valor;

        // 4. Aumentar contador
        $_SESSION['preguntas_respondidas']++;

        // 5. Filtrar personajes
        $personajes_restantes = $this->personajesModel->filtrarPersonajes($_SESSION['preguntas_info']);
        $num = count($personajes_restantes);

        $_SESSION['personajes_posibles'] = $num;

        if ($num == 1) {
            // ADIVINADO
            $_SESSION['personaje_adivinado'] = $personajes_restantes[0];
            $_SESSION['vista'] = 'adivinar';

        } elseif ($num == 0) {
            // SIN RESULTADOS POSIBLES
            $_SESSION['vista'] = 'sin_resultados';

        } else {

            // SI NO QUEDAN MÁS PREGUNTAS → MOSTRAR LISTA FINAL
            if (empty($_SESSION['preguntas_disponibles'])) {
                $_SESSION['personajes_posibles_lista'] = $personajes_restantes;
                $_SESSION['vista'] = 'lista';

            } else {
                // QUEDAN PREGUNTAS → SEGUIR PREGUNTANDO
                $siguiente = $this->preguntaAleatoria();
                if ($siguiente) {
                    $_SESSION['pregunta_actual'] = $siguiente;
                    $_SESSION['vista'] = 'pregunta';
                } else {
                    $_SESSION['vista'] = 'error';
                }
            }
        }

        header('Location: ../public/index.php');
        exit;
    }
}

$controlador = new gameController();

// Iniciar juego
if (isset($_POST['inicio'])) {
    $controlador->iniciarJuego();
}

// Responder pregunta
if (isset($_POST['respuesta'])) {
    $controlador->procesarRespuesta();
}
?>
