<?php
include 'config.php';
include 'auth.php';
verificarSesion();

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

<!-- Navbar igual a estudiantes.php -->
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="perfil.php">Ver Perfil</a></li>
            </ul>
            <?php
            $usuario = obtenerUsuarioActual();
            if ($usuario['username']) {
                echo '
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownAlta" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <span>' . htmlspecialchars($usuario['username']) . '</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="cambiar_password.php"><i class="fas fa-key me-2"></i>Cambiar Contraseña</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>';
            }
            ?>
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
