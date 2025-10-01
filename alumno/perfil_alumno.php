<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['alumno']); // Solo alumnos pueden acceder

$usuario = obtenerUsuarioActual();
$id_alumno = obtenerIdAlumno();

if (!$id_alumno) {
    die('Error: No se encontró información del alumno para este usuario.');
}

// Obtener información del alumno
$stmt = $mysqli->prepare("
    SELECT a.*, c.nombre as carrera_nombre 
    FROM alumnos a 
    LEFT JOIN carreras c ON a.id_carrera = c.id_carrera 
    WHERE a.id_alumno = ?
");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();

// Obtener notas del alumno
$stmt = $mysqli->prepare("
    SELECT n.*, m.nombre as materia_nombre, CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre
    FROM notas n
    JOIN materias m ON n.id_materia = m.id_materia
    LEFT JOIN profesores p ON m.id_profesor = p.id_profesor
    WHERE n.id_alumno = ?
    ORDER BY m.nombre
");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$notas = $stmt->get_result();

// Obtener compañeros de la misma carrera
$stmt = $mysqli->prepare("
    SELECT a.nombre, a.dni, a.Edad
    FROM alumnos a
    WHERE a.id_carrera = ? AND a.id_alumno != ?
    ORDER BY a.nombre
    LIMIT 20
");
$stmt->bind_param('ii', $alumno['id_carrera'], $id_alumno);
$stmt->execute();
$companeros = $stmt->get_result();

// Calcular promedios por materia
$stmt = $mysqli->prepare("
    SELECT m.nombre as materia_nombre, 
           ROUND((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3, 2) as promedio,
           n.nota1, n.nota2, n.nota3,
           CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre
    FROM notas n
    JOIN materias m ON n.id_materia = m.id_materia
    LEFT JOIN profesores p ON m.id_profesor = p.id_profesor
    WHERE n.id_alumno = ?
    ORDER BY promedio DESC
");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$promedios = $stmt->get_result();

// Calcular promedio general
$stmt = $mysqli->prepare("
    SELECT ROUND(AVG((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3), 2) as promedio_general,
           COUNT(*) as total_materias
    FROM notas n
    WHERE n.id_alumno = ?
");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$estadisticas = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil - Sistema Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Smooth scrolling para navegación interna */
        html {
            scroll-behavior: smooth;
        }
        
        /* Offset para navegación con navbar fijo */
        .card {
            scroll-margin-top: 80px;
        }
        
        /* Estilos mejorados para badges de estado */
        .badge.bg-success {
            background-color: #28a745 !important;
            color: white !important;
            font-weight: bold;
            padding: 8px 12px;
            font-size: 0.85em;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .badge.bg-danger {
            background-color: #dc3545 !important;
            color: white !important;
            font-weight: bold;
            padding: 8px 12px;
            font-size: 0.85em;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .badge.bg-secondary {
            background-color: #6c757d !important;
            color: white !important;
            font-weight: bold;
            padding: 8px 12px;
            font-size: 0.85em;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Efecto hover para badges */
        .badge:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }
    </style>
</head>
<body>

<?php include '../include/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Mi Perfil de Estudiante</h2>
    
    <!-- Información Personal -->
    <div id="informacion" class="card profile-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información Personal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-signature me-2"></i>Nombre:</strong> <?= htmlspecialchars($alumno['nombre']) ?></p>
                    <p><strong><i class="fas fa-id-card me-2"></i>DNI:</strong> <?= htmlspecialchars($alumno['dni'] ?? 'No registrado') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-birthday-cake me-2"></i>Edad:</strong> <?= $alumno['Edad'] ?> años</p>
                    <p><strong><i class="fas fa-calendar me-2"></i>Fecha de Nacimiento:</strong> <?= $alumno['fecha_nacimiento'] ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p><strong><i class="fas fa-graduation-cap me-2"></i>Carrera:</strong> <?= htmlspecialchars($alumno['carrera_nombre'] ?? 'Sin carrera asignada') ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mis Notas -->
    <div class="card filter-card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Mis Calificaciones</h5>
        </div>
        <div class="card-body">
            <?php if ($notas->num_rows > 0): ?>
                <table class="table table-bordered table-striped table-hover shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Materia</th>
                            <th>Profesor</th>
                            <th>Nota 1</th>
                            <th>Nota 2</th>
                            <th>Nota 3</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($nota = $notas->fetch_assoc()): 
                            $promedio = 0;
                            $count = 0;
                            if ($nota['nota1']) { $promedio += $nota['nota1']; $count++; }
                            if ($nota['nota2']) { $promedio += $nota['nota2']; $count++; }
                            if ($nota['nota3']) { $promedio += $nota['nota3']; $count++; }
                            $promedio = $count > 0 ? round($promedio / $count, 2) : 0;
                            
                            $estado = 'Cursando';
                            $class = 'bg-secondary';
                            if ($promedio >= 6) {
                                $estado = 'Aprobado';
                                $class = 'bg-success';
                            } elseif ($promedio > 0 && $promedio < 6) {
                                $estado = 'Desaprobado';
                                $class = 'bg-danger';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($nota['materia_nombre']) ?></td>
                            <td><?= htmlspecialchars($nota['profesor_nombre'] ?? 'Sin asignar') ?></td>
                            <td><?= $nota['nota1'] ? $nota['nota1'] : '-' ?></td>
                            <td><?= $nota['nota2'] ? $nota['nota2'] : '-' ?></td>
                            <td><?= $nota['nota3'] ? $nota['nota3'] : '-' ?></td>
                            <td><strong><?= $promedio > 0 ? $promedio : '-' ?></strong></td>
                            <td><span class="badge <?= $class ?>"><?= $estado ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No tienes calificaciones registradas aún.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección de Promedios -->
    <div id="promedios" class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Mis Promedios</h5>
        </div>
        <div class="card-body">
            <?php if ($estadisticas['total_materias'] > 0): ?>
                <!-- Promedio General -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="card-title"><?= $estadisticas['promedio_general'] ?? '0.00' ?></h3>
                                <p class="card-text">Promedio General</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="card-title"><?= $estadisticas['total_materias'] ?></h3>
                                <p class="card-text">Materias Cursadas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promedios por Materia -->
                <h6><i class="fas fa-list me-2"></i>Promedios por Materia</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Materia</th>
                                <th>Profesor</th>
                                <th>Nota 1</th>
                                <th>Nota 2</th>
                                <th>Nota 3</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($promedio = $promedios->fetch_assoc()): 
                                $estado = 'Cursando';
                                $class = 'bg-secondary';
                                if ($promedio['promedio'] >= 6) {
                                    $estado = 'Aprobado';
                                    $class = 'bg-success';
                                } elseif ($promedio['promedio'] > 0 && $promedio['promedio'] < 6) {
                                    $estado = 'Desaprobado';
                                    $class = 'bg-danger';
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($promedio['materia_nombre']) ?></td>
                                <td><?= htmlspecialchars($promedio['profesor_nombre'] ?? 'Sin asignar') ?></td>
                                <td><?= $promedio['nota1'] ? $promedio['nota1'] : '-' ?></td>
                                <td><?= $promedio['nota2'] ? $promedio['nota2'] : '-' ?></td>
                                <td><?= $promedio['nota3'] ? $promedio['nota3'] : '-' ?></td>
                                <td><strong><?= $promedio['promedio'] > 0 ? $promedio['promedio'] : '-' ?></strong></td>
                                <td><span class="badge <?= $class ?>"><?= $estado ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No tienes calificaciones para calcular promedios.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección Ver Compañeros -->
    <div id="companeros" class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Compañeros de Carrera</h5>
        </div>
        <div class="card-body">
            <?php if ($companeros->num_rows > 0): ?>
                <div class="row">
                    <?php while ($companero = $companeros->fetch_assoc()): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-user me-2"></i><?= htmlspecialchars($companero['nombre']) ?></h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-id-card me-1"></i>DNI: <?= htmlspecialchars($companero['dni'] ?? 'No registrado') ?><br>
                                        <i class="fas fa-birthday-cake me-1"></i>Edad: <?= $companero['Edad'] ?> años
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay otros estudiantes registrados en tu carrera.
                </div>
            <?php endif; ?>
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