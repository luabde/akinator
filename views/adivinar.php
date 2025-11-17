<div class="resultado">
  <p>¡Tu personaje era <?= htmlspecialchars($personaje_adivinado['nombre']) ?>!</p>
  <img src="./<?= htmlspecialchars($personaje_adivinado['imagen_url']) ?>" 
     class="foto-adivinar">
  <p><?= htmlspecialchars($personaje_adivinado['descripcion']) ?></p>
  
  <form method="POST" action="procesar.php">
    <input type="hidden" name="personaje_id" value="<?= $personaje_adivinado['id'] ?>">
    
    <div class="botones">
              <a href="../public/index.php?seccio=nueva" class="btn-primary-si">Vover a jugar</a>
    </div>
  </form>
</div>