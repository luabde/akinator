<div class="info">
  <p>Personajes posibles: <?= $num_personajes ?? 0 ?></p>
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