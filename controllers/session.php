<?php
    /**
     * Gestión de sessiones del juego
     * 
     * Las sessiones permiten guardar info que se usara entre difentes paginas del PHP. Si no se usa esto, cada vez que se cambie
     * de pagina la información se perderá.
     */

    // En caso de que la sessión no esté iniciada, la crearemos
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

    // Función en la que se inicializan las variables que se usaran en todo el proyetco
    function inicializar_juego(){
        if(!isset($_SESSION['preguntas_respondidas'])){
            $_SESSION['preguntas_respondidas'] = [];
        }

        // Se guardan los personajes adivinados
        if (isset($_SESSION['user_id']) && !isset($_SESSION['historial'])) {
            $_SESSION['historial'] = [];
        }

    }

    // Función para guardar la respuesta y la columna en el array preguntas_respondidas
    function guardarRespuesta($idPregunta, $columna, $respuestaUser){
        // Se guarda en preguntas respondidas (array associativas) la columna y la respuesta del usuario
        $_SESSION['preguntas_respondidas'][$idPregunta] = [
            'columna' => $columna,
            'respuesta' => $respuestaUser
        ];
    }


?>