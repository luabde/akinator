
<div class="historial">
    <?php foreach ($historial as $indice => $fila): ?>
        <li>
            <strong>Registro #<?= $indice + 1 ?>:</strong>
            <ul>
                <?php foreach ($fila as $campo => $valor): ?>
                    <li>
                        <strong><?= $campo ?>:</strong> 
                        <?= (string)$valor ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
    <?php endforeach; ?>
</div>