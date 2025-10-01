<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden agregar materias

$mensaje = '';

if (isset($_POST['guardar'])) {
    $nombre = $mysqli->real_escape_string($_POST['nombre']);
    $id_carrera = (int)$_POST['id_carrera'];
    $id_profesor = (int)$_POST['id_profesor'];

    if ($nombre != '' && $id_carrera > 0) {
        $stmt = $mysqli->prepare("INSERT INTO materias (nombre, id_carrera, id_profesor) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $nombre, $id_carrera, $id_profesor);
        $stmt->execute();
        header('Location: materias.php');
        exit;
    } else {
        $mensaje = "Completa todos los campos obligatorios correctamente.";
    }
}

// Obtener carreras y profesores para los selects
$carreras = $mysqli->query("SELECT id_carrera, nombre FROM carreras ORDER BY nombre");
$profesores = $mysqli->query("SELECT id_profesor, CONCAT(nombre, ' ', apellido) as nombre_completo FROM profesores ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agregar Materia</title>
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
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
</style>
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                <li class="nav-item"><a class="nav-link" href="profesores.php">Profesores</a></li>
                <li class="nav-item"><a class="nav-link active" href="materias.php">Materias</a></li>
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMaterias" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

<div class="container py-4">
    <h2 class="mb-4">Agregar Materia</h2>
    
    <?php if ($mensaje): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    
    <div class="card filter-card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Datos de la Materia</h5>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-12">
                    <label for="nombre" class="form-label"><i class="fas fa-book me-1"></i>Nombre de la Materia *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="col-md-6">
                    <label for="id_carrera" class="form-label"><i class="fas fa-graduation-cap me-1"></i>Carrera *</label>
                    <select class="form-select" id="id_carrera" name="id_carrera" required>
                        <option value="">Seleccionar carrera</option>
                        <?php while ($carrera = $carreras->fetch_assoc()): ?>
                            <option value="<?= $carrera['id_carrera'] ?>">
                                <?= htmlspecialchars($carrera['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="id_profesor" class="form-label"><i class="fas fa-chalkboard-teacher me-1"></i>Profesor</label>
                    <select class="form-select" id="id_profesor" name="id_profesor">
                        <option value="">Seleccionar profesor</option>
                        <?php while ($profesor = $profesores->fetch_assoc()): ?>
                            <option value="<?= $profesor['id_profesor'] ?>">
                                <?= htmlspecialchars($profesor['nombre_completo']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" name="guardar" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Materia
                        </button>
                        <a href="materias.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>

</body>
</html>

