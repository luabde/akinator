# Documentació Sistema d'Historial - Akinator DC

## Índex
1. [Introducció](#introducció)
2. [Arquitectura MVC](#arquitectura-mvc)
3. [Base de dades](#base-de-dades)
4. [Model (userModel.php)](#model-usermodelphp)
5. [Controller (userController.php)](#controller-usercontrollerphp)
6. [Vista (historial.php)](#vista-historialphp)
7. [Flux de funcionament](#flux-de-funcionament)
8. [Integració amb el joc](#integració-amb-el-joc)
9. [Gestió de sessions](#gestió-de-sessions)

---

## Introducció

Aquest document descriu el sistema d'historial implementat per al projecte **Akinator DC**. El sistema permet:

- Guardar els personatges encertats per cada usuari
- Visualitzar l'historial personal de personatges descoberts
- Vincular partides amb usuaris autenticats
- Protegir l'accés a historials privats

---

## Arquitectura MVC

El sistema segueix el patró **Model-Vista-Controlador (MVC)**:

- **Model** (userModel.php): Gestiona les operacions de base de dades per a l'historial
- **Controller** (userController.php): Coordina la lògica de guardat i visualització
- **Vista** (historial.php): Mostra la llista de personatges encertats

---

## Base de dades

### Taula: `historial`

```sql
CREATE TABLE historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    personaje_id INT NOT NULL,
    fecha_adivinado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (personaje_id) REFERENCES personajes(id) ON DELETE CASCADE
);
```

**Camps:**
- `id`: Identificador únic del registre
- `usuario_id`: FK que referència l'usuari que va encertar
- `personaje_id`: FK que referència el personatge encertat
- `fecha_adivinado`: Data i hora en què es va encertar el personatge

**Relacions:**
- **usuarios**: Relació N:1 (un usuari pot tenir molts registres)
- **personajes**: Relació N:1 (un personatge pot ser encertat per molts usuaris)

---

## Model (userModel.php)

### Descripció
La classe `Usuario` conté els mètodes relacionats amb la gestió de l'historial d'usuaris.

---

### Mètodes relacionats amb l'historial

#### `obtenerHistorial($userId)`
Obté tots els personatges encertats per un usuari específic.

**Paràmetres:**
- `$userId` (int): ID de l'usuari del qual volem obtenir l'historial

**Retorna:**
- Array associatiu amb els noms dels personatges encertats
- `null` si no hi ha registres o hi ha un error

**Exemple:**
```php
$usuario = new Usuario();
$historial = $usuario->obtenerHistorial(5);

if ($historial) {
    foreach ($historial as $registro) {
        echo $registro['nombre'];
    }
} else {
    echo "No hi ha personatges encertats";
}
```

**Implementació:**
```php
public function obtenerHistorial($userId) {
    $query = "
        SELECT p.nombre
        FROM historial h
        INNER JOIN personajes p ON h.personaje_id = p.id
        WHERE h.usuario_id = $userId
    ";
    
    $resultado = mysqli_query($this->db, $query);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }
    return null;
}
```

**Detalls tècnics:**
- Utilitza un `INNER JOIN` per obtenir el nom del personatge
- Filtra per `usuario_id` per garantir privacitat
- Retorna només el camp `nombre` per optimitzar

---

#### `guardarHistorial($userId, $personajeId)`
Guarda un nou registre quan l'usuari encerta un personatge.

**Paràmetres:**
- `$userId` (int): ID de l'usuari que ha encertat
- `$personajeId` (int): ID del personatge encertat

**Retorna:**
- `true` si el registre es guarda correctament
- `false` si hi ha un error

**Exemple:**
```php
$usuario = new Usuario();
$resultado = $usuario->guardarHistorial(5, 12);

if ($resultado) {
    echo "Personatge guardat a l'historial";
}
```

**Implementació:**
```php
public function guardarHistorial($userId, $personajeId) {
    $query = "INSERT INTO historial (usuario_id, personaje_id) 
              VALUES ($userId, $personajeId)";
    
    return mysqli_query($this->db, $query);
}
```

**Consideracions:**
- El camp `fecha_adivinado` s'omple automàticament amb `CURRENT_TIMESTAMP`
- No es valida si el personatge ja existeix (permet múltiples encerts del mateix)
- Els IDs es passen directament ja que són valors numèrics de sessió

---

## Controller (userController.php)

### Mètodes relacionats amb l'historial

#### `guardarHistorial()`
Guarda el personatge encertat a l'historial de l'usuari actual.

**Flux:**
1. Verifica que l'usuari estigui autenticat (`$_SESSION['login']`)
2. Obté l'ID de l'usuari de la sessió
3. Obté l'ID del personatge encertat de la sessió
4. Crida al model per guardar el registre

**Retorna:**
- `true` si es guarda correctament
- `false` si l'usuari no està autenticat

**Implementació:**
```php
public function guardarHistorial() {
    if ($_SESSION['login']) {
        // Obtenim l'ID de l'usuari
        $id = $_SESSION['user_id'];
        $id_personaje = $_SESSION['personaje_adivinado']['id'];
        
        $this->model->guardarHistorial($id, $id_personaje);
        
    } else {
        return false;
    }
}
```

**Moment de crida:**
Aquest mètode s'invoca des de `gameController.php` quan es compleix la condició `$num == 1` (personatge encertat):

```php
if ($num == 1) {
    $_SESSION['personaje_adivinado'] = $personajes_restantes[0];
    $_SESSION['vista'] = 'adivinar';
    
    $this->userController->guardarHistorial();
}
```

---

#### `mostrarHistorial()`
Prepara i mostra l'historial de l'usuari actual.

**Flux:**
1. Inicialitza les variables `$historial` i `$logueado`
2. Comprova si l'usuari està autenticat
3. Si està autenticat, obté l'historial de la BD
4. Carrega la vista amb les dades preparades

**Variables disponibles a la vista:**
- `$logueado` (bool): Indica si l'usuari està autenticat
- `$historial` (array|null): Llista de personatges encertats

**Implementació:**
```php
public function mostrarHistorial() {
    $historial = [];
    $logueado = false;
    
    if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
        $logueado = true;
        $idUser = $_SESSION['user_id'];
        $historial = $this->model->obtenerHistorial($idUser);
    }
    
    include '../views/historial.php';
}
```

**Moment de crida:**
Aquest mètode s'invoca des d'`index.php` quan l'usuari accedeix a la secció d'historial:

```php
<?php if ($seccio === 'historial'): ?>
    <div class="sidebar-content">
        <h2>Historial</h2>
        <?php
            require '../controllers/userController.php';
            $controller = new userController();
            $controller->mostrarHistorial();
        ?>
    </div>
<?php endif; ?>
```

---

## Vista (historial.php)

### Descripció
Vista condicional que mostra diferents estats segons l'autenticació i el contingut de l'historial.

---

### Estats de la vista

#### 1. Usuari no autenticat

```php
<?php
if (!$logueado) {
    echo "<h4>Inicia la sessió en un compte.</h4>";
    return;
}
```

**Condició:** `$logueado === false`  
**Missatge:** "Inicia la sessió en un compte."

---

#### 2. Historial buit

```php
if (empty($historial)) {
    echo "<h4>No has encertat cap personatge.</h4>";
    return;
}
```

**Condició:** `$logueado === true` i `$historial` és buit  
**Missatge:** "No has encertat cap personatge."

---

#### 3. Historial amb personatges

```php
foreach ($historial as $fila) {
    echo "<h4>" . htmlspecialchars($fila['nombre']) . "</h4>";
}
```

**Condició:** `$logueado === true` i `$historial` conté registres  
**Sortida:** Llista de noms de personatges encertats

---

### Codi complet

```php
<?php
if (!$logueado) {
    echo "<h4>Inicia la sessió en un compte.</h4>";
    return;
}

if (empty($historial)) {
    echo "<h4>No has encertat cap personatge.</h4>";
    return;
}

foreach ($historial as $fila) {
    echo "<h4>" . htmlspecialchars($fila['nombre']) . "</h4>";
}
?>
```

---

## Flux de funcionament

### Flux de guardat d'historial

```
1. Usuari completa una partida i encerta un personatge

2. gameController->procesarRespuesta()
   - Detecta que només queda 1 personatge
   - Guarda $_SESSION['personaje_adivinado']
   - Estableix $_SESSION['vista'] = 'adivinar'

3. gameController crida userController->guardarHistorial()

4. userController->guardarHistorial()
   - Verifica $_SESSION['login'] === true
   - Obté $id = $_SESSION['user_id']
   - Obté $id_personaje = $_SESSION['personaje_adivinado']['id']

5. userModel->guardarHistorial($id, $id_personaje)
   - INSERT INTO historial (usuario_id, personaje_id)
   - Retorna true/false

6. Redirecció a index.php
   - Es mostra la vista 'adivinar' amb el personatge
```

---

### Flux de visualització d'historial

```
1. Usuari clica "Historial" al menú lateral

2. URL: index.php?seccio=historial

3. index.php detecta el paràmetre GET
   - if ($seccio === 'historial')

4. Es crida al controlador:
   - $controller = new userController()
   - $controller->mostrarHistorial()

5. userController->mostrarHistorial()
   - Comprova $_SESSION['login']
   - Si true: obté $idUser i crida al model
   - Si false: $logueado = false

6. userModel->obtenerHistorial($idUser)
   - Query amb INNER JOIN
   - Retorna array amb noms de personatges

7. Controller carrega la vista
   - include '../views/historial.php'
   - Variables $logueado i $historial disponibles

8. Vista renderitza el contingut apropiat
   - Missatge de no autenticat
   - Missatge d'historial buit
   - Llista de personatges encertats
```

---

## Integració amb el joc

### Vinculació amb gameController

El `gameController` té una instància de `userController` per gestionar l'historial:

```php
class gameController {
    private $preguntasModel;
    private $personajesModel;
    private $userController;
    
    public function __construct() {
        $this->preguntasModel = new preguntasModel();
        $this->personajesModel = new Personaje();
        $this->userController = new userController();
    }
}
```

---

### Moment del guardat

El guardat es produeix immediatament després d'encertar:

```php
if ($num == 1) {
    // ADIVINADO
    $_SESSION['personaje_adivinado'] = $personajes_restantes[0];
    $_SESSION['vista'] = 'adivinar';
    
    $this->userController->guardarHistorial();
}
```

**Important:** El guardat NO depèn que l'usuari confirmi. Es guarda automàticament.

---

## Gestió de sessions

### Variables de sessió utilitzades

```php
$_SESSION['login']                // bool: Indica si l'usuari està autenticat
$_SESSION['user_id']              // int: ID de l'usuari autenticat
$_SESSION['personaje_adivinado']  // array: Dades del personatge encertat
```

---

### Verificació d'autenticació

Per a operacions d'historial, sempre es comprova:

```php
if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
    // Usuari autenticat
} else {
    // Usuari no autenticat
}
```

---

## Seguretat i privacitat

### Protecció de dades

- **Accés restringit**: Només usuaris autenticats poden guardar i veure historial
- **Privacitat**: Cada usuari només pot veure el seu propi historial
- **Filtratge per ID**: Les consultes sempre filtren per `usuario_id`

---

### Validacions

```php
// Al guardar
if ($_SESSION['login']) {
    // Procedir amb el guardat
} else {
    return false;
}

// Al mostrar
if (!$logueado) {
    echo "Inicia la sessió en un compte.";
    return;
}
```

---

## Possibles millores

### Funcionalitats adicionals

1. **Estadístiques**
   - Nombre total de personatges encertats
   - Percentatge de la biblioteca completada
   - Personatge més encertat

2. **Historial detallat**
   - Data d'encert
   - Nombre de preguntes necessàries
   - Temps invertit

3. **Eliminació de registres**
   - Permetre esborrar l'historial complet
   - Eliminar registres individuals

4. **Ordenació i filtratge**
   - Ordenar per data (més recents primer)
   - Filtrar per personatge
   - Cerca per nom

---

## Exemples d'ús

### Exemple 1: Guardar personatge encertat

```php
// Al finalitzar una partida exitosa
$userController = new userController();

if ($_SESSION['login'] && isset($_SESSION['personaje_adivinado'])) {
    $userController->guardarHistorial();
}
```

---

### Exemple 2: Mostrar historial a la vista

```php
// A index.php
<?php if ($seccio === 'historial'): ?>
    <?php
        $controller = new userController();
        $controller->mostrarHistorial();
    ?>
<?php endif; ?>
```

---

### Exemple 3: Consultar historial directament

```php
$usuario = new Usuario();
$historial = $usuario->obtenerHistorial($_SESSION['user_id']);

if ($historial) {
    echo "Has encertat " . count($historial) . " personatges";
}
```