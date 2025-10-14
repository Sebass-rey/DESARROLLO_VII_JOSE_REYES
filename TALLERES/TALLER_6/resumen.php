<?php
$archivo_json = 'registros.json';
$registros = [];

if (file_exists($archivo_json)) {
    $registros = json_decode(file_get_contents($archivo_json), true) ?? [];
}
?>

<h2>Resumen de Registros</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Edad</th>
        <th>Género</th>
        <th>Intereses</th>
        <th>Comentarios</th>
        <th>Foto</th>
    </tr>

    <?php foreach ($registros as $registro): ?>
        <tr>
            <td><?= htmlspecialchars($registro['nombre']) ?></td>
            <td><?= htmlspecialchars($registro['email']) ?></td>
            <td><?= htmlspecialchars($registro['edad']) ?></td>
            <td><?= htmlspecialchars($registro['genero']) ?></td>
            <td><?= implode(', ', $registro['intereses']) ?></td>
            <td><?= htmlspecialchars($registro['comentarios']) ?></td>
            <td>
                <?php if (!empty($registro['foto_perfil'])): ?>
                    <img src="uploads/<?= htmlspecialchars($registro['foto_perfil']) ?>" width="80">
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
