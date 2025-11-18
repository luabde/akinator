<?php
session_start();
    require_once '../models/userModel.php';

    class userController{
        private $model;
        private $errores = [];

        public function __construct(){
            $this->model = new Usuario();
        }

        public function login(){
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            if(!$email){
                $this->errores[] = "L'email es obligatori o no es vàlid";
            }

            if(!$password){
                $this->errores[] = "La contrassenya és obligatoria";
            }

            // En el caso de que no hayan errores seguiremos haciendo verificaciones para validar el usuario
            if(empty($this->errores)){
                // Llamamos a la clase usuario que se encarga de hacer una query a la BD para obtener el usuario que tenga el email introducido por el user
                $usuario = $this->model->findByEmail($email);
                if($usuario){
                    // Ahora se verifica si la contraseña es correcta. Como la contraseña esta hasheada se debe usar password_verify.
                    // Esto nos lo ofrece php, y esto nos permite comparar una contraseña con la que esta hasheada
                    $auth = password_verify($password, $usuario['contrasena']);
    
                    if($auth){
                        // Si auth es true, significa que la contraseña también es correcta
                        // Se gurada en session el nombre del usuario y login true que se obtiene de lo que ha devuelto la consulta a la db
                        $_SESSION['usuario'] = $usuario['nombre_usuario'];
                        $_SESSION['login'] = true;
                        // Se guarda tambien el id de usuario, debido a que se necesitara en el historial.
                        $_SESSION['user_id'] = $usuario['id'];
                        // Una vez se ha iniciado la sessión se redirge a la pagina de inicio
                        header('Location: ../public/index.php');
                        exit;
                    }else{
                        $this->errores[] = "La contrassenya no es correcta";
                    }
                }else{
                    $this->errores[] = "L'usuari no existeix";
                }
            }

            // Si hay errores no habra entrado al if entonces hay que guardarlos en sesión y volver al formulario
            $_SESSION['errores_login'] = $this->errores;
            header('Location: ../public/login.php?login=login');
            exit;
        }

        public function signIn(){
            // Obtenemos la informació enviada a través del form
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $nombre_usuario = $_POST['nomUsuari'];
            $password = $_POST['password'];
            
            if(!$nombre_usuario){
                $this->errores[] = "El nom d'usuari es obligatori";
            }

            if(!$email){
                $this->errores[] = "L'email es obligatori o no es vàlid";
            }elseif($this->model->findByEmail($email)){
                // Si retorna true, significa que el email ya existe
                $this->errores = "El correu ja existeix";
            }
            if(!$password){
                $this->errores[] = 'La contrassenya és obligatoria';
            }elseif(strlen($password) < 6){
                $this->errores[] = "La contrassenya ha de tenir al menys 6 caràcters";
            }

            if(empty($this->errores)){
                // Si no hay errores se crea el usuario
                $resultado = $this->model->createUser($nombre_usuario, $email, $password);

                 if ($resultado) {
                    // Registro exitoso, iniciar sesión automáticamente
                    $_SESSION['usuario'] = $nombre_usuario;
                    $_SESSION['login'] = true;
                    header('Location: ../public/index.php');
                    exit;
                } else {
                    $this->errores[] = "Error al crear el usuario";
                }
            }

            $_SESSION['errores_login'] = $this->errores;
            header('Location: ../public/login.php?login=signIn');
            exit;
        }

        public function logOut(){
            // Dejaremos el login a false, porque se ha cerrado la session
            $_SESSION['login'] = false;

            // Eliminamos de la sesión el usuario, porque ya se ha cerrado session y no nos interesa tener el nombre de este guardado
            unset($_SESSION['usuario']);

            // Redirigimos al index
            header("Location: ../public/index.php");
        }

        public function guardarHistorial(){
            if($_SESSION['login']){
                // Obtenemos el id del usuario
                $id = $_SESSION['user_id'];
                $id_personaje = $_SESSION['personaje_adivinado']['id'];

                $this->model->guardarHistorial($id, $id_personaje);

            }else{
                return false;
            }
        }

        public function mostrarHistorial(){
            // Comprovar si hay el usuario logueado

            // Si esta logueado
                // Obtener el id del usuario y hacer query para obtener todos los regsitros de historial de ese usuario 
                // Después devolver un array con todos los registros
            
            // Si no esta logueado se 
        }
    }
 
    /*
        Obtenemos des de el GET el valor de action. Esto nos ayudará a saber a que metodo debemos llamar dependiendo de la accion que se haya realizado desde el
        frontend.
        Lo que esto nos puede devolver es:
            - login --> esto se envia desde el formulario de inicio de sesión (situado el login.php)
            - signup --> esto se envia desde el formulario de registro (situado en el login.php)
            - logout --> esto se envía desde el <a> situado en el header cuando la sessión esta iniciada, el a permite desloguearte (situado en views/header.php)
    */
    $action = $_GET['action'] ?? null;

    // Creamos una instancia del controlador para más adelante llamar a sus metodos
    $userController = new userController();
    
    switch($action){
        case 'login':
            $userController->login();
            break;
        case 'signin':
            $userController->signIn();
            break;
        case 'logout':
            $userController->logOut();
            break;
    }
?>