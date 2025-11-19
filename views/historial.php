
<div class="historial">
    <?php foreach ($historial as $indice => $fila): ?>
            <ul>
                <?php foreach ($fila as $campo => $valor): ?>
                    <h3>
                        <?= (string)$valor ?>
                </h3>
                <?php endforeach; ?>
            </ul>
    <?php endforeach; ?>
</div>