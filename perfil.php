<?php
include 'config.php';
include 'auth.php';
verificarSesion();

$usuario = obtenerUsuarioActual();

// Redireccionar según el rol del usuario
if (esAlumno()) {
    header('Location: alumno/perfil_alumno.php');
    exit;
} elseif (esProfesor()) {
    header('Location: profesor/perfil_profesor.php');
    exit;
}

// Si es admin, continúa con el perfil general
// Obtener datos completos del usuario
$stmt = $mysqli->prepare("SELECT id, username FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $usuario['id']);
$stmt->execute();
$result = $stmt->get_result();
$datosUsuario = $result->fetch_assoc();

if (!$datosUsuario) {
    header('Location: logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Perfil</title>
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
        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 500;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (esAdmin()): ?>
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                <li class="nav-item"><a class="nav-link" href="profesores.php">Profesores</a></li>
                <li class="nav-item"><a class="nav-link" href="materias.php">Materias</a></li>
                <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link active" href="perfil.php">Ver Perfil</a></li>
                <?php elseif (esProfesor()): ?>
                <li class="nav-item"><a class="nav-link" href="notas.php">Gestión de Notas</a></li>
                <li class="nav-item"><a class="nav-link active" href="perfil.php">Ver Perfil</a></li>
                <?php elseif (esAlumno()): ?>
                <li class="nav-item"><a class="nav-link" href="perfil_alumno.php">Mi Perfil</a></li>
                <?php endif; ?>
            </ul>
            <?php
            $usuario = obtenerUsuarioActual();
            if ($usuario['username']) {
                echo '
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownPerfil" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
    <h2 class="mb-4">Mi Perfil</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card filter-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información de Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>ID de Usuario:</strong></div>
                        <div class="col-sm-8"><?= $datosUsuario['id'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Nombre de Usuario:</strong></div>
                        <div class="col-sm-8"><?= htmlspecialchars($datosUsuario['username']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Estado:</strong></div>
                        <div class="col-sm-8"><span class="badge bg-success">Activo</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card filter-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="cambiar_password.php" class="btn btn-filter">
                            <i class="fas fa-key me-2"></i>Cambiar Contraseña
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>