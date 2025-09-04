<?php
include 'config.php';

$mensaje = '';

if (isset($_POST['guardar'])) {
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $edad = (int)$_POST['edad'];
    $id_carrera = (int)$_POST['id_carrera'];

    if ($nombre != '' && $edad > 0 && $id_carrera > 0) {
        $stmt = $mysqli->prepare("INSERT INTO alumno (nombre, edad, id_carrera) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $nombre, $edad, $id_carrera);
        $stmt->execute();
        header('Location: estudiantes.php');
        exit;
    } else {
        $mensaje = "Completa todos los campos correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agregar Alumno</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar igual a estudiantes.php -->
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                <li class="nav-item"><a class="nav-link active" href="alta.php">Agregar alumno</a></li>
                <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
            </ul>
        </div>
    </nav>
</div>

<!-- Formulario -->
<div class="container py-4">
    <h2 class="mb-4">Agregar Alumno</h2>

    <?php if ($mensaje != '') { ?>
        <div class="alert alert-danger"><?= $mensaje ?></div>
    <?php } ?>

    <form method="POST" class="shadow p-4 rounded bg-white">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Edad</label>
            <input type="number" name="edad" class="form-control" required min="1">
        </div>
        <div class="mb-3">
            <label class="form-label">Carrera (ID)</label>
            <input type="number" name="id_carrera" class="form-control" required min="1">
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar</button>
        <a href="estudiantes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
