<div class="resultado">
  <h2>¡Tu personaje era <?= htmlspecialchars($personaje_adivinado['nombre']) ?>!</h2>
  <img src="./<?= htmlspecialchars($personaje_adivinado['imagen_url']) ?>" 
     height="200px" border-radius= "10px">
  <p class="pregunta"><?= htmlspecialchars($personaje_adivinado['descripcion']) ?></p>
  
  <form method="POST" action="procesar.php">
    <input type="hidden" name="personaje_id" value="<?= $personaje_adivinado['id'] ?>">
    
    <div class="botones">
      <button type="submit" name="correcto" value="1" class="btn-primary-si">Vover a jugar</button>
    </div>
  </form>
</div>