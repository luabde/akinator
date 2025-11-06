<?php
function conectarDB(): mysqli{
    $db = mysqli_connect('localhost', 'root', '', 'akinator');

    if(!$db){
        echo"Error no se pudo conectar";
        exit;
    }

    return $db;
}
    // class Conectar{

    //     public static function conexion(){
    //         $db = new mysqli("localhost", "root", "", "akinator");
    //         $db->query("SET NAMES 'utf8'");
    //         return $db;
    //     }
    // }
?>