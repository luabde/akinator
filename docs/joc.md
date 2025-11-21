# Documentació Sistema de Joc - Akinator DC

## Índex
1. [Introducció](#introducció)
2. [Arquitectura del sistema](#arquitectura-del-sistema)
3. [Base de dades](#base-de-dades)
4. [Models](#models)
5. [Controller (gameController.php)](#controller-gamecontrollerphp)
6. [Vistes del joc](#vistes-del-joc)
7. [Flux complet del joc](#flux-complet-del-joc)
8. [Gestió de sessions](#gestió-de-sessions)
9. [Algoritme de filtratge](#algoritme-de-filtratge)
10. [Estats del joc](#estats-del-joc)

---

## Introducció

Aquest document descriu el sistema de joc implementat per al projecte **Akinator DC**. El sistema permet:

- Endevinar personatges mitjançant preguntes de sí/no
- Filtrar progressivament els personatges possibles
- Gestionar múltiples estats del joc
- Mostrar resultats segons el nombre de coincidències
- Reiniciar partides i mantenir l'estat entre pàgines

---

## Arquitectura del sistema

### Patró MVC

El sistema de joc segueix el patró **Model-Vista-Controlador (MVC)**:

- **Models**:
  - `preguntasModel.php`: Gestiona les preguntes del joc
  - `personajesModel.php`: Gestiona els personatges i el filtratge
  - `userModel.php`: Gestiona l'historial d'usuaris

- **Controller**: 
  - `gameController.php`: Orquestra tota la lògica del joc

- **Vistes**:
  - `pregunta.php`: Mostra la pregunta actual
  - `adivinar.php`: Mostra el personatge encertat
  - `lista.php`: Mostra els personatges possibles restants
  - `sin_resultados.php`: Mostra missatge quan no hi ha coincidències

---

## Base de dades

### Taula: `preguntas`

```sql
CREATE TABLE preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    columna_asociada VARCHAR(50) NOT NULL,
    orden INT DEFAULT 0
);
```

**Camps:**
- `id`: Identificador únic de la pregunta
- `texto`: Text de la pregunta que veu l'usuari
- `columna_asociada`: Nom de la columna de `personajes` que correspon a aquesta pregunta
- `orden`: Ordre de prioritat (opcional)

**Exemple de registres:**
```sql
INSERT INTO preguntas (texto, columna_asociada) VALUES
('És un heroi?', 'es_heroe'),
('Té superpoders?', 'tiene_poderes'),
('És humà?', 'es_humano');
```

---

### Taula: `personajes`

```sql
CREATE TABLE personajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255),
    -- Columnes de característiques (relacionades amb preguntes)
    es_heroe TINYINT(1) DEFAULT 0,
    tiene_poderes TINYINT(1) DEFAULT 0,
    es_humano TINYINT(1) DEFAULT 0,
    -- ... més columnes segons les preguntes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Camps de característiques:**
- Cada columna representa una característica binària (0 = No, 1 = Sí)
- El nom de la columna coincideix amb `columna_asociada` de la taula `preguntas`

---

## Models

### preguntasModel.php

#### Descripció
Gestiona les operacions relacionades amb les preguntes del joc.

---

#### Mètodes

##### `ObtenerTodasIdsPreguntas()`
Obté totes les IDs de les preguntes disponibles.

**Retorna:**
- Array d'IDs numèrics

**Exemple:**
```php
$preguntasModel = new preguntasModel();
$ids = $preguntasModel->ObtenerTodasIdsPreguntas();
// Retorna: [1, 2, 3, 4, 5, ...]
```

**Implementació:**
```php
public function ObtenerTodasIdsPreguntas() {
    $query = 'SELECT id FROM preguntas;';
    $resultado = mysqli_query($this->db, $query);
    $preguntas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    // Extraure només els IDs
    $ids = [];
    foreach ($preguntas as $pregunta) {
        $ids[] = $pregunta['id'];
    }
    
    return $ids;
}
```

---

##### `obtenerPreguntaPorId($id)`
Obté els detalls d'una pregunta específica.

**Paràmetres:**
- `$id` (int): ID de la pregunta

**Retorna:**
- Array associatiu amb `id`, `texto` i `columna_asociada`

**Exemple:**
```php
$pregunta = $preguntasModel->obtenerPreguntaPorId(5);
// Retorna:
// [
//     'id' => 5,
//     'texto' => 'És un heroi?',
//     'columna_asociada' => 'es_heroe'
// ]
```

**Implementació:**
```php
public function obtenerPreguntaPorId($id) {
    $query = "SELECT id, texto, columna_asociada FROM preguntas WHERE id = $id";
    $resultado = mysqli_query($this->db, $query);
    
    return mysqli_fetch_assoc($resultado);
}
```

---

### personajesModel.php

Ja documentat al document de biblioteca, però aquí destaquem el mètode clau per al joc:

#### `filtrarPersonajes($preguntas_info)`

Aquest mètode construeix dinàmicament una consulta SQL que filtra personatges segons les respostes de l'usuari.

**Lògica de filtratge:**
```sql
SELECT * FROM personajes 
WHERE 1=1 
  AND `es_heroe` = 1 
  AND `tiene_poderes` = 1 
ORDER BY nombre
```

---

## Controller (gameController.php)

### Descripció
El `gameController` és el nucli del sistema de joc. Gestiona tot el flux, des de l'inici fins a la finalització.

---

### Propietats

```php
private $preguntasModel;      // Model de preguntes
private $personajesModel;     // Model de personatges
private $userController;      // Controller d'usuaris
private $pregunta_aleatoria;  // Pregunta actual generada
```

---

### Mètodes principals

#### `__construct()`
Inicialitza tots els models i controladors necessaris.

```php
public function __construct() {
    $this->preguntasModel = new preguntasModel();
    $this->personajesModel = new Personaje();
    $this->userController = new userController();
}
```

---

#### `iniciarJuego()`
Inicia una nova partida, reiniciant totes les variables de sessió.

**Flux:**
1. Obté totes les IDs de preguntes disponibles
2. Inicialitza l'array de preguntes respondides
3. Estableix el comptador de preguntes a 0
4. Estableix el nombre inicial de personatges possibles (30)
5. Genera la primera pregunta aleatòria
6. Estableix la vista a 'pregunta'
7. Redirigeix a index.php

**Implementació:**
```php
public function iniciarJuego() {
    // 1. Obtenir totes les IDs de preguntes
    $preguntas = $this->preguntasModel->obtenerTodasIdsPreguntas();
    $_SESSION['preguntas_disponibles'] = $preguntas;
    
    // 2. Reiniciar respostes de l'usuari
    $_SESSION['preguntas_info'] = [];
    $_SESSION['preguntas_respondidas'] = 0;
    
    // 3. Inicialitzar nombre de personatges possibles
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
```

---

#### `preguntaAleatoria()`
Selecciona una pregunta aleatòria de les disponibles.

**Flux:**
1. Obté l'array de preguntes disponibles
2. Comprova que quedin preguntes
3. Selecciona un índex aleatori
4. Obté els detalls de la pregunta
5. Guarda la pregunta al log intern (`preguntas_info`)
6. Elimina la pregunta de les disponibles
7. Reajusta els índexs de l'array

**Retorna:**
- Array amb les dades de la pregunta
- `false` si no queden preguntes

**Implementació:**
```php
public function preguntaAleatoria() {
    $preguntas = $_SESSION['preguntas_disponibles'];
    
    if (empty($preguntas)) {
        return false; // NO QUEDEN PREGUNTES
    }
    
    $indice_aleatorio = array_rand($preguntas);
    $id_pregunta = $preguntas[$indice_aleatorio];
    
    $this->pregunta_aleatoria = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);
    
    // Guardar pregunta al log intern
    $_SESSION['preguntas_info'][$id_pregunta] = [
        'columna' => $this->pregunta_aleatoria['columna_asociada'],
        'respuestaUser' => null
    ];
    
    // Eliminar de les disponibles i reajustar índexs
    unset($_SESSION['preguntas_disponibles'][$indice_aleatorio]);
    $_SESSION['preguntas_disponibles'] = array_values($_SESSION['preguntas_disponibles']);
    
    return $this->pregunta_aleatoria;
}
```

**Per què `array_values()`?**

Sense `array_values()`, després d'eliminar un element queden "forats" als índexs:
```php
// Abans: [0 => 1, 1 => 2, 2 => 3, 3 => 4]
// Després d'eliminar índex 2: [0 => 1, 1 => 2, 3 => 4]  // Falta el 2!
```

Amb `array_values()`, es reinicien els índexs:
```php
// Després: [0 => 1, 1 => 2, 2 => 4]  // Índexs consecutius
```

---

#### `procesarRespuesta()`
Processa la resposta de l'usuari i determina el següent pas del joc.

**Flux:**
1. Obté la resposta de l'usuari (sí/no/ns)
2. Converteix la resposta a format numèric (1/0/null)
3. Guarda la resposta al log de preguntes
4. Incrementa el comptador de preguntes respondides
5. Filtra els personatges segons totes les respostes
6. Determina l'estat següent segons el nombre de coincidències:
   - **1 personatge** → Vista 'adivinar'
   - **0 personatges** → Vista 'sin_resultados'
   - **Més d'1 i no queden preguntes** → Vista 'lista'
   - **Més d'1 i queden preguntes** → Següent pregunta

**Implementació:**
```php
public function procesarRespuesta() {
    // 1. Dades de la pregunta actual
    $pregunta_id = $_SESSION['pregunta_actual']['id'];
    $respuestaUser = $_POST['respuesta'];
    
    // 2. Convertir si/no en 1/0
    if ($respuestaUser === 'si') {
        $valor = 1;
    } elseif ($respuestaUser === 'no') {
        $valor = 0;
    } else {
        $valor = null; // "No lo sé"
    }
    
    // 3. Guardar resposta
    $_SESSION['preguntas_info'][$pregunta_id]['respuestaUser'] = $valor;
    
    // 4. Augmentar comptador
    $_SESSION['preguntas_respondidas']++;
    
    // 5. Filtrar personatges
    $personajes_restantes = $this->personajesModel->filtrarPersonajes($_SESSION['preguntas_info']);
    $num = count($personajes_restantes);
    
    $_SESSION['personajes_posibles'] = $num;
    
    if ($num == 1) {
        // ENDEVINAT
        $_SESSION['personaje_adivinado'] = $personajes_restantes[0];
        $_SESSION['vista'] = 'adivinar';
        
        $this->userController->guardarHistorial();
        
    } elseif ($num == 0) {
        // SENSE RESULTATS POSSIBLES
        $_SESSION['vista'] = 'sin_resultados';
        
    } else {
        // SI NO QUEDEN MÉS PREGUNTES → MOSTRAR LLISTA FINAL
        if (empty($_SESSION['preguntas_disponibles'])) {
            $_SESSION['personajes_posibles_lista'] = $personajes_restantes;
            $_SESSION['vista'] = 'lista';
            
        } else {
            // QUEDEN PREGUNTES → SEGUIR PREGUNTANT
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
```

---

### Enrutament del controller

Al final del fitxer `gameController.php`, hi ha la lògica d'enrutament:

```php
$controlador = new gameController();

// Iniciar joc
if (isset($_POST['inicio'])) {
    $controlador->iniciarJuego();
}

// Respondre pregunta
if (isset($_POST['respuesta'])) {
    $controlador->procesarRespuesta();
}
```

---

## Vistes del joc

### pregunta.php

Mostra la pregunta actual amb tres botons de resposta.

**Variables disponibles:**
- `$pregunta`: Array amb les dades de la pregunta
- `$preguntas_respondidas`: Nombre de preguntes ja respostes
- `$num_personajes`: Nombre de personatges possibles restants

**Codi:**
```php
<div class="info">
  <p>Estoy pensando en <?= $num_personajes ?? 0 ?> Personajes</p>
  <p>Preguntas respondidas: <?= $preguntas_respondidas ?></p>
</div>

<?php if ($pregunta): ?>
    <p class="pregunta"><?= htmlspecialchars($pregunta['texto'] ?? '') ?></p>

    <form method="POST" action='../controllers/gameController.php'>
        <div class="botones">
            <button type="submit" name="respuesta" value="si" class="btn-primary-si">Sí</button>
            <button type="submit" name="respuesta" value="ns" class="btn-primary-ns">No lo sé</button>
            <button type="submit" name="respuesta" value="no" class="btn-primary-no">No</button>
        </div>
    </form>
<?php else: ?>
    <p>No hay más preguntas disponibles.</p>
<?php endif; ?>
```

---

### adivinar.php

Mostra el personatge endevinat amb la seva imatge i descripció.

**Variables disponibles:**
- `$personaje_adivinado`: Array amb totes les dades del personatge

**Codi:**
```php
<div class="resultado">
  <p>¡Tu personaje era <?= htmlspecialchars($personaje_adivinado['nombre']) ?>!</p>
  <img src="./<?= htmlspecialchars($personaje_adivinado['imagen_url']) ?>" 
       class="foto-adivinar">
  <p><?= htmlspecialchars($personaje_adivinado['descripcion']) ?></p>
  
  <form method="POST" action="procesar.php">
    <input type="hidden" name="personaje_id" value="<?= $personaje_adivinado['id'] ?>">
    
    <a href="../public/index.php?seccio=nueva" class="btn-primary-si">Volver a jugar</a>
  </form>
</div>
```

---

### lista.php

Mostra una llista dels personatges possibles quan no s'ha pogut endevinar amb certesa.

**Variables disponibles:**
- `$personajes_posibles`: Array amb els personatges restants
- `$num_personajes`: Nombre de personatges a la llista

**Codi:**
```php
<div class="info">
  <p>He reducido las opciones a <?= $num_personajes ?> personajes posibles</p>
</div>

<div class="resultado"> 
    <div class="personajes-lista">
  <?php foreach ($personajes_posibles as $personaje): ?>
    <label class="personaje-card">
      <span>
        <?= htmlspecialchars($personaje['nombre']) ?>
        <?php if ($personaje !== end($personajes_posibles)): ?>
          -
        <?php endif; ?>
      </span>
    </label>
  <?php endforeach; ?>
</div>

    <a href="../public/index.php?seccio=nueva" class="btn-primary-si">Intentar de nuevo</a>
</div>
```

---

### sin_resultados.php

Mostra un missatge quan no hi ha cap personatge que compleixi els criteris.

**Codi:**
```php
<div class="resultado">
  <p>¡Ups! No he logrado identificar a tu personaje.</p>

  <img src="img/interrogante.png" alt="Interrogante" class="foto-adivinar">

  <p>Tu elección no coincide con ninguno de los personajes que conozco.</p>

    <a href="../public/index.php?seccio=nueva" class="btn-primary-si">Intentar de nuevo</a>
</div>
```

---

## Flux complet del joc

### 1. Inici de partida

```
1. Usuari accedeix a index.php
   - $_SESSION['vista'] = 'inicio' (per defecte)

2. Es mostra el botó "Començar partida"

3. Usuari clica el botó
   - <form method="POST" action="../controllers/gameController.php">
   - <button name="inicio">Començar partida</button>

4. gameController detecta isset($_POST['inicio'])

5. gameController->iniciarJuego()
   - Obté totes les IDs de preguntes
   - Inicialitza sessions:
     * preguntas_disponibles = [1, 2, 3, ...]
     * preguntas_info = []
     * preguntas_respondidas = 0
     * personajes_posibles = 30
   - Genera primera pregunta amb preguntaAleatoria()
   - $_SESSION['pregunta_actual'] = pregunta
   - $_SESSION['vista'] = 'pregunta'

6. Redirecció a index.php

7. index.php renderitza pregunta.php
```

---

### 2. Cicle de preguntes

```
1. Es mostra pregunta.php amb la pregunta actual

2. Usuari selecciona resposta (Sí/No/No lo sé)
   - <button name="respuesta" value="si">

3. POST a gameController.php

4. gameController detecta isset($_POST['respuesta'])

5. gameController->procesarRespuesta()
   - Obté resposta: 'si', 'no' o 'ns'
   - Converteix a valor numèric: 1, 0 o null
   - Guarda a $_SESSION['preguntas_info'][id]['respuestaUser']
   - Incrementa $_SESSION['preguntas_respondidas']
   
6. Filtra personatges
   - personajesModel->filtrarPersonajes($_SESSION['preguntas_info'])
   - Construeix query SQL dinàmica
   - Retorna array de personatges coincidents

7. Avalua nombre de coincidències:
   - count($personajes_restantes)

8. Decisió segons resultat:
   
   A. Si $num == 1:
      - $_SESSION['personaje_adivinado'] = personatge
      - $_SESSION['vista'] = 'adivinar'
      - Guarda a historial (si està autenticat)
      - FI DEL JOC
   
   B. Si $num == 0:
      - $_SESSION['vista'] = 'sin_resultados'
      - FI DEL JOC
   
   C. Si $num > 1 i NO queden preguntes:
      - $_SESSION['personajes_posibles_lista'] = personatges
      - $_SESSION['vista'] = 'lista'
      - FI DEL JOC
   
   D. Si $num > 1 i queden preguntes:
      - Genera següent pregunta amb preguntaAleatoria()
      - $_SESSION['pregunta_actual'] = nova_pregunta
      - $_SESSION['vista'] = 'pregunta'
      - CONTINUA EL CICLE

9. Redirecció a index.php

10. Torna al pas 1 del cicle (si no ha acabat)
```

---

### 3. Finalització

**Cas A: Personatge endevinat**
```
1. Es mostra adivinar.php
2. Imatge i dades del personatge
3. Botó "Volver a jugar"
4. Registre guardat a historial (si està autenticat)
```

**Cas B: Sense resultats**
```
1. Es mostra sin_resultados.php
2. Missatge d'error
3. Imatge d'interrogant
4. Botó "Intentar de nuevo"
```

**Cas C: Múltiples opcions**
```
1. Es mostra lista.php
2. Llista de personatges possibles
3. Botó "Intentar de nuevo"
```

---

## Gestió de sessions

### Variables de sessió utilitzades

```php
// Control de vista
$_SESSION['vista']                    // 'inicio', 'pregunta', 'adivinar', 'lista', 'sin_resultados', 'error'

// Preguntes
$_SESSION['preguntas_disponibles']    // Array d'IDs de preguntes no utilitzades
$_SESSION['pregunta_actual']          // Array amb dades de la pregunta actual
$_SESSION['preguntas_info']           // Log de preguntes amb respostes de l'usuari
$_SESSION['preguntas_respondidas']    // Comptador de preguntes respostes

// Personatges
$_SESSION['personajes_posibles']      // Nombre de personatges restants
$_SESSION['personaje_adivinado']      // Dades del personatge endevinat
$_SESSION['personajes_posibles_lista']// Array de personatges (quan no es pot endevinar)

// Usuari (gestionades per userController)
$_SESSION['login']                    // bool: Usuari autenticat
$_SESSION['user_id']                  // ID de l'usuari
$_SESSION['usuario']                  // Nom de l'usuari
```

---

### Reinici de partida

Quan l'usuari accedeix a `index.php?seccio=nueva`, es reinicien totes les variables del joc:

```php
if (isset($_GET['seccio']) && $_GET['seccio'] === 'nueva') {
    // RESETEAR TOTES LES VARIABLES DEL JOC
    unset($_SESSION['vista']);
    unset($_SESSION['preguntas_disponibles']);
    unset($_SESSION['pregunta_actual']);
    unset($_SESSION['preguntas_info']);
    unset($_SESSION['preguntas_respondidas']);
    unset($_SESSION['personajes_posibles']);
    unset($_SESSION['personaje_adivinado']);
    unset($_SESSION['personajes_posibles_lista']);

    $_SESSION['vista'] = 'inicio';
    
    header("Location: index.php");
    exit;
}
```

---

## Algoritme de filtratge

### Construcció dinàmica de la query

L'algoritme de filtratge és el cor del sistema Akinator. Construeix una query SQL dinàmica basada en les respostes de l'usuari.

#### Exemple pas a pas

**Estat inicial:**
```php
$preguntas_info = [];
$query = "SELECT * FROM personajes WHERE 1=1";
```

**Després de respondre "És un heroi?" → Sí:**
```php
$preguntas_info = [
    1 => ['columna' => 'es_heroe', 'respuestaUser' => 1]
];
$query = "SELECT * FROM personajes WHERE 1=1 AND `es_heroe` = 1";
```

**Després de respondre "Té superpoders?" → Sí:**
```php
$preguntas_info = [
    1 => ['columna' => 'es_heroe', 'respuestaUser' => 1],
    2 => ['columna' => 'tiene_poderes', 'respuestaUser' => 1]
];
$query = "SELECT * FROM personajes WHERE 1=1 AND `es_heroe` = 1 AND `tiene_poderes` = 1";
```

**Després de respondre "És humà?" → No:**
```php
$preguntas_info = [
    1 => ['columna' => 'es_heroe', 'respuestaUser' => 1],
    2 => ['columna' => 'tiene_poderes', 'respuestaUser' => 1],
    3 => ['columna' => 'es_humano', 'respuestaUser' => 0]
];
$query = "SELECT * FROM personajes WHERE 1=1 AND `es_heroe` = 1 AND `tiene_poderes` = 1 AND `es_humano` = 0";
```

---

### Gestió del "No lo sé"

Quan l'usuari respon "No lo sé", `respuestaUser` és `null`, i aquesta pregunta **no s'afegeix al filtre**:

```php
foreach ($preguntas_info as $id_pregunta => $info) {
    if ($info['respuestaUser'] !== null) {  // Només si ha respost
        $columna = mysqli_real_escape_string($this->db, $info['columna']);
        $respuesta = (int)$info['respuestaUser'];
        $query .= " AND `$columna` = $respuesta";
    }
}
```

Això permet mantenir més opcions obertes quan l'usuari no està segur.

---

## Estats del joc

### Diagrama d'estats

```
    [INICIO]
       |
       | Clic "Començar partida"
       v
   [PREGUNTA] <----+
       |           |
       | Resposta  |
       v           |
  [PROCESSAMENT]   |
       |           |
       +-- num > 1 i queden preguntes
       |
       +-- num == 1 --> [ADIVINAR]
       |
       +-- num == 0 --> [SIN_RESULTADOS]
       |
       +-- num > 1 i NO queden preguntes --> [LISTA]
```

---

### Transicions d'estat

| Estat actual | Condició | Estat següent |
|--------------|----------|---------------|
| inicio | Clic botó "Començar" | pregunta |
| pregunta | Resposta + $num > 1 + queden preguntes | pregunta (nova) |
| pregunta | Resposta + $num == 1 | adivinar |
| pregunta | Resposta + $num == 0 | sin_resultados |
| pregunta | Resposta + $num > 1 + NO queden preguntes | lista |
| adivinar | Clic "Volver a jugar" | inicio |
| lista | Clic "Intentar de nuevo" | inicio |
| sin_resultados | Clic "Intentar de nuevo" | inicio |

---

## Consideracions tècniques

### Rendiment

- **Preguntes aleatòries**: Evita patrons predictibles
- **Filtratge progressiu**: Redueix el conjunt de dades a cada pregunta
- **Sessions**: Mantenen l'estat sense necessitat de BD

---

### Escalabilitat

El sistema pot créixer afegint:
- Més personatges a la BD
- Més preguntes amb les seves columnes corresponents
- Lògica de probabilitats (preguntes més efectives primer)

---

### Seguretat

- **htmlspecialchars()**: A totes les vistes per prevenir XSS
- **mysqli_real_escape_string()**: Al model per prevenir SQL injection
- **Validació de respostes**: Només acepta 'si', 'no' o 'ns'

---

## Possibles millores

1. **Optimització d'algoritme**
   - Prioritzar preguntes que divideixen millor el conjunt
   - Implementar arbre de decisió binari

2. **Estadístiques**
   - Mostrar taxa d'encert
   - Preguntes més efectives
   - Personatges més difícils

3. **Millores UX**
   - Barra de progrés
   - Animacions entre preguntes
   - Historial de respostes durant la partida

4. **Funcionalitats**
   - Mode dificultat (més/menys preguntes)
   - Pistes
   - Opció de desfer última resposta