<?php
include '../config.php';
include '../auth.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reportes - Sistema Estudiantes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background-color: #f8f9fa; }
    .navbar { border-bottom: 1px solid #dee2e6; }
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
    .card { border-radius: 12px; transition: transform 0.2s; }
    .card:hover { transform: translateY(-5px); }
    .card-title { font-weight: 600; }
    .card-subtitle { font-weight: 500; }
    .notas { font-size: 0.95rem; color: #495057; }
    .promedio { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
</style>
</head>
<body>

<div class="container-fluid">
<?php include '../include/navbar.php'; ?>

<div style="display:none;"><!-- Navbar reemplazado -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" >
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
                <li class="nav-item"><a class="nav-link active" href="reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="perfil.php">Ver Perfil</a></li>
                <?php elseif (esProfesor()): ?>
                <li class="nav-item"><a class="nav-link" href="notas.php">Gestión de Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="perfil_profesor.php">Mi Perfil</a></li>
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownReportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
    <h2 class="mb-4">Reportes por Carrera</h2>
    <div class="row g-4">
    <?php
    $rs_carreras = $mysqli->query("SELECT id_carrera,nombre FROM carreras");
    while($carrera=$rs_carreras->fetch_assoc()){
        $id_carrera=$carrera['id_carrera'];
        $nombre_carrera=$carrera['nombre'];
        $rs_mejor=$mysqli->query("
            SELECT a.id_alumno AS id_alumno,a.nombre,AVG((n.nota1 + n.nota2 + n.nota3) / 3) AS promedio
            FROM alumnos a
            JOIN notas n ON a.id_alumno=n.id_alumno
            WHERE a.id_carrera=$id_carrera
            GROUP BY a.id_alumno, a.nombre
            ORDER BY promedio DESC
            LIMIT 1
        ");
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 p-3">
                <div class="card-body d-flex flex-column">
                    <h3 class="card-title text-center"><?=htmlspecialchars($nombre_carrera)?></h3>
                    <?php
                    if($rs_mejor && $rs_mejor->num_rows>0){
                        $mejor=$rs_mejor->fetch_assoc();
                        $id_mejor=$mejor['id_alumno'];
                        $nombre_alumno=$mejor['nombre'];
                        $rs_notas=$mysqli->query("SELECT nota1, nota2, nota3, ROUND((nota1 + nota2 + nota3) / 3, 2) AS promedio FROM notas WHERE id_alumno=$id_mejor");
                        $notas_data = $rs_notas->fetch_assoc();
                        $promedio = $notas_data['promedio'];
                        ?>
                        <h5 class="card-subtitle mb-2 text-center">Mejor Alumno: <?=htmlspecialchars($nombre_alumno)?></h5>
                        <p class="notas text-center"><strong>Notas:</strong> <?=$notas_data['nota1']?>, <?=$notas_data['nota2']?>, <?=$notas_data['nota3']?></p>
                        <p class="promedio text-center">Promedio: <?=$promedio?></p>
                        <?php
                    } else {
                        ?>
                        <p class="text-center text-muted mt-3">No hay alumnos con notas para esta carrera.</p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
</div>

<footer class="bg-white text-center py-4 mt-5 border-top shadow-sm">
    Sistema de Estudiantes © <?=date('Y')?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

