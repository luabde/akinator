<div class="resultado">
  <h2>¡Creo que es...!</h2>
  <p class="pregunta"><?= htmlspecialchars($personaje_adivinado['nombre']) ?></p>
  <p>¿Es correcto?</p>
  
  <form method="POST" action="procesar.php">
    <input type="hidden" name="personaje_id" value="<?= $personaje_adivinado['id'] ?>">
    
    <div class="botones">
      <button type="submit" name="correcto" value="1" class="btn-success">Sí</button>
      <button type="submit" name="correcto" value="0" class="btn-danger">No</button>
    </div>
  </form>
</div>