<?php
    // function getConnection(){
    //     $user = 'root';
    //     $pass = '';
    //     try {
    //         $dbh = new PDO('mysql:host=localhost;dbname=akinator', $user, $pass);
    //         echo "conexion con la BD OK";
    //         return $dbh;
    //     } catch(PDOException $e){
    //         // intentar reintentar la conexión después de un cierto tiempo, por ejemplo
    //         echo "error haciendo conexion con la BD $e";
    //     }
    // }
    class Conectar{
        public static function conexion(){
            $conexion = new mysqli("localhost", "akinator", "root", "");
            $conexion->query("SET NAMES 'utf8'");
            return $conexion;
        }
    }
?>