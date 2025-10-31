<?php
    function getConnection(){
        $user = 'root';
        $pass = '';
        try {
            $dbh = new PDO('mysql:host=localhost;dbname=akinator', $user, $pass);
            echo "conexion con la BD OK";
            return $dbh;
        } catch(PDOException $e){
            // intentar reintentar la conexión después de un cierto tiempo, por ejemplo
            echo "error haciendo conexion con la BD $e";
        }
    }
?>