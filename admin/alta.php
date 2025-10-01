<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden agregar estudiantes

$mensaje = '';

if (isset($_POST['guardar'])) {
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $edad = (int)$_POST['Edad'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $id_carrera = (int)$_POST['id_carrera'];

    if ($nombre != '' && $edad > 0 && $id_carrera > 0) {
        $stmt = $mysqli->prepare("INSERT INTO alumnos (nombre, Edad, fecha_nacimiento, id_carrera) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sisi', $nombre, $edad, $fecha_nacimiento, $id_carrera);
        $stmt->execute();
        header('Location: estudiantes.php');
        exit;
    } else {
        $mensaje = "Completa todos los campos correctamente.";
    }
}

// Obtener carreras para el select
$carreras = $mysqli->query("SELECT id_carrera, nombre FROM carreras ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agregar Alumno</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .filter-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        border: none;
    }
    .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
</head>
<body>

<?php include '../include/navbar.php'; ?>

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
            <input type="number" name="Edad" class="form-control" required min="1">
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Carrera</label>
            <select name="id_carrera" class="form-select" required>
                <option value="">Seleccionar Carrera</option>
                <?php while($carrera = $carreras->fetch_assoc()) { ?>
                <option value="<?= $carrera['id_carrera'] ?>"><?= htmlspecialchars($carrera['nombre']) ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar</button>
        <a href="estudiantes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

