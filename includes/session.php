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

    
?>