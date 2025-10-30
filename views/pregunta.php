<div class="info">
  <p>Personajes posibles: <?= $num_personajes ?></p>
  <p>Preguntas respondidas: <?= count(obtenerPreguntasRespondidas()) ?></p>
</div>

<p class="pregunta"><?= htmlspecialchars($pregunta_actual['texto']) ?></p>

<form method="POST" action="juego.php">
  <input type="hidden" name="pregunta_id" value="<?= $pregunta_actual['id'] ?>">
  <input type="hidden" name="columna" value="<?= htmlspecialchars($pregunta_actual['columna_asociada']) ?>">
  
  <div class="botones">
    <button type="submit" name="respuesta" value="1" class="btn-primary">✅ Sí</button>
    <button type="submit" name="respuesta" value="0" class="btn-primary">❌ No</button>
  </div>
</form>