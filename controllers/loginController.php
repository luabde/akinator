<?php
    require '../config/database.php';
    $db = conectarDB();
    // var_dump(($db));
    // $nombre_usuario = "prueba";
    // $email = "correo@correo.com";
    // $password = "1234";

    // // Hasheamos la password para mayor seguridad
    // $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    // echo "$passwordHash";
    // $query = "INSERT INTO usuarios (nombre_usuario, email, contrasena) VALUES ('$nombre_usuario', '$email', '$passwordHash')";

    // mysqli_query($db, $query);

    session_start();
    $action = $_GET['action'];

    if(isset($action)){
        switch($action){
            case 'logout':
                logOut();
                break;
            case 'signin':
                break;
        }
    }

    function signin(){
        
    }
    function logOut(){
        // Dejaremos el login a false, porque se ha cerrado la session
        $_SESSION['login'] = false;

        // Eliminamos de la sesión el usuario, porque ya se ha cerrado session y no nos interesa tener el nombre de este guardado
        unset($_SESSION['usuario']);
        header("Location: ../public/index.php");
    }   
?>