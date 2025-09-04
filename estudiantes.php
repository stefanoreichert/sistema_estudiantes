<?php
include 'config.php';

if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)($_GET['eliminar']);
    if ($id_eliminar > 0) {
        $check = $mysqli->query("SELECT id FROM alumno WHERE id=$id_eliminar");
        if ($check && $check->num_rows > 0) {
            $stmt = $mysqli->prepare("DELETE FROM alumno WHERE id=?");
            $stmt->bind_param('i', $id_eliminar);
            $stmt->execute();
        }
    }
    header('Location: estudiantes.php');
    exit;
}

$rs = $mysqli->query("SELECT id, nombre, edad, id_carrera FROM alumno ORDER BY id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link active" href="estudiantes.php">Estudiantes</a></li>
                
                <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
            </ul>
        </div>
    </nav>
</div>

<div class="container py-4">
    <h2 class="mb-4">Listado de Estudiantes</h2>
    <a href="alta.php" class="btn btn-primary mb-3">Agregar Alumno</a>
    
    <table class="table table-bordered table-striped table-hover shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Edad</th>
                <th>Carrera (ID)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $rs->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= $row['edad'] ?></td>
                <td><?= $row['id_carrera'] ?></td>
                <td>
                    <a href="modificar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Modificar</a>
                    <a href="estudiantes.php?eliminar=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar este alumno?')">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
