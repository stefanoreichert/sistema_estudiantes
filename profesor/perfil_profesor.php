<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['profesor']); // Solo profesores pueden acceder

$usuario = obtenerUsuarioActual();
$id_profesor = obtenerIdProfesor();

if (!$id_profesor) {
    die('Error: No se encontró información del profesor para este usuario.');
}

// Obtener información del profesor
$stmt = $mysqli->prepare("
    SELECT p.* 
    FROM profesores p 
    WHERE p.id_profesor = ?
");
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$profesor = $stmt->get_result()->fetch_assoc();

// Obtener materias que enseña el profesor
$stmt = $mysqli->prepare("
    SELECT m.*, c.nombre as carrera_nombre
    FROM materias m
    LEFT JOIN carreras c ON m.id_carrera = c.id_carrera
    WHERE m.id_profesor = ?
    ORDER BY m.nombre
");
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$materias = $stmt->get_result();

// Obtener alumnos de las materias del profesor
$stmt = $mysqli->prepare("
    SELECT DISTINCT a.nombre as alumno_nombre, a.dni, a.Edad, c.nombre as carrera_nombre,
           m.nombre as materia_nombre,
           ROUND((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3, 2) as promedio
    FROM alumnos a
    JOIN notas n ON a.id_alumno = n.id_alumno
    JOIN materias m ON n.id_materia = m.id_materia
    LEFT JOIN carreras c ON a.id_carrera = c.id_carrera
    WHERE m.id_profesor = ?
    ORDER BY a.nombre, m.nombre
");
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$mis_alumnos = $stmt->get_result();

// Estadísticas del profesor
$stmt = $mysqli->prepare("
    SELECT 
        COUNT(DISTINCT m.id_materia) as total_materias,
        COUNT(DISTINCT n.id_alumno) as total_alumnos,
        ROUND(AVG((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3), 2) as promedio_general,
        COUNT(CASE WHEN ((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3) >= 6 THEN 1 END) as alumnos_aprobados
    FROM materias m
    LEFT JOIN notas n ON m.id_materia = n.id_materia
    WHERE m.id_profesor = ?
");
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$estadisticas = $stmt->get_result()->fetch_assoc();

// Promedios por materia que enseña
$stmt = $mysqli->prepare("
    SELECT m.nombre as materia_nombre,
           COUNT(n.id_nota) as total_alumnos,
           ROUND(AVG((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3), 2) as promedio_materia,
           COUNT(CASE WHEN ((COALESCE(n.nota1,0) + COALESCE(n.nota2,0) + COALESCE(n.nota3,0)) / 3) >= 6 THEN 1 END) as aprobados
    FROM materias m
    LEFT JOIN notas n ON m.id_materia = n.id_materia
    WHERE m.id_profesor = ?
    GROUP BY m.id_materia, m.nombre
    ORDER BY promedio_materia DESC
");
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$promedios_materias = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil - Profesor - Sistema Estudiantes</title>
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
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-card .card-header {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
    <!-- Mensaje de Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none; border-radius: 15px; box-shadow: 0 6px 12px rgba(79, 172, 254, 0.3);">
                <div class="card-body text-center text-white py-4">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-chalkboard-teacher me-3"></i>
                        ¡Bienvenido, Profesor!
                    </h1>
                    <h3 class="mb-0">
                        <?= htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido']) ?>
                    </h3>
                    <p class="lead mt-2 mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Sesión iniciada el <?= date('d/m/Y H:i', $_SESSION['tiempo_login']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mb-4">Mi Perfil de Profesor</h2>
    
    <!-- Información Personal -->
    <div id="informacion" class="card profile-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Información Personal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-signature me-2"></i>Nombre:</strong> <?= htmlspecialchars($profesor['nombre']) ?></p>
                    <p><strong><i class="fas fa-user me-2"></i>Apellido:</strong> <?= htmlspecialchars($profesor['apellido']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong> <?= htmlspecialchars($profesor['email'] ?? 'No registrado') ?></p>
                    <p><strong><i class="fas fa-id-badge me-2"></i>ID Profesor:</strong> <?= $profesor['id_profesor'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div id="estadisticas" class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Mis Estadísticas</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white stats-card">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?= $estadisticas['total_materias'] ?? 0 ?></h3>
                            <p class="card-text">Materias que Enseño</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white stats-card">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?= $estadisticas['total_alumnos'] ?? 0 ?></h3>
                            <p class="card-text">Total de Alumnos</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white stats-card">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?= $estadisticas['promedio_general'] ?? '0.00' ?></h3>
                            <p class="card-text">Promedio General</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-white stats-card">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?= $estadisticas['alumnos_aprobados'] ?? 0 ?></h3>
                            <p class="card-text">Alumnos Aprobados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mis Materias -->
    <div id="materias" class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-books me-2"></i>Materias que Enseño</h5>
        </div>
        <div class="card-body">
            <?php if ($materias->num_rows > 0): ?>
                <div class="row">
                    <?php while ($materia = $materias->fetch_assoc()): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-book me-2"></i><?= htmlspecialchars($materia['nombre']) ?></h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-graduation-cap me-1"></i>Carrera: <?= htmlspecialchars($materia['carrera_nombre'] ?? 'Sin carrera') ?>
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
                    No tienes materias asignadas aún.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Promedios por Materia -->
    <div class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Rendimiento por Materia</h5>
        </div>
        <div class="card-body">
            <?php if ($promedios_materias->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Materia</th>
                                <th>Total Alumnos</th>
                                <th>Promedio</th>
                                <th>Aprobados</th>
                                <th>% Aprobación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($promedio = $promedios_materias->fetch_assoc()): 
                                $porcentaje_aprobacion = $promedio['total_alumnos'] > 0 ? 
                                    round(($promedio['aprobados'] / $promedio['total_alumnos']) * 100, 1) : 0;
                                
                                $class_aprobacion = '';
                                if ($porcentaje_aprobacion >= 80) $class_aprobacion = 'text-success';
                                elseif ($porcentaje_aprobacion >= 60) $class_aprobacion = 'text-warning';
                                else $class_aprobacion = 'text-danger';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($promedio['materia_nombre']) ?></strong></td>
                                <td><?= $promedio['total_alumnos'] ?></td>
                                <td><?= $promedio['promedio_materia'] ?? '0.00' ?></td>
                                <td><?= $promedio['aprobados'] ?></td>
                                <td><span class="<?= $class_aprobacion ?>"><?= $porcentaje_aprobacion ?>%</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay datos de rendimiento disponibles.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mis Alumnos -->
    <div id="alumnos" class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Mis Alumnos</h5>
        </div>
        <div class="card-body">
            <?php if ($mis_alumnos->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Alumno</th>
                                <th>DNI</th>
                                <th>Edad</th>
                                <th>Carrera</th>
                                <th>Materia</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($alumno = $mis_alumnos->fetch_assoc()): 
                                $estado = 'Cursando';
                                $class = 'bg-secondary';
                                if ($alumno['promedio'] >= 6) {
                                    $estado = 'Aprobado';
                                    $class = 'bg-success';
                                } elseif ($alumno['promedio'] > 0 && $alumno['promedio'] < 6) {
                                    $estado = 'Desaprobado';
                                    $class = 'bg-danger';
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($alumno['alumno_nombre']) ?></td>
                                <td><?= htmlspecialchars($alumno['dni'] ?? 'No registrado') ?></td>
                                <td><?= $alumno['Edad'] ?> años</td>
                                <td><?= htmlspecialchars($alumno['carrera_nombre'] ?? 'Sin carrera') ?></td>
                                <td><?= htmlspecialchars($alumno['materia_nombre']) ?></td>
                                <td><strong><?= $alumno['promedio'] > 0 ? $alumno['promedio'] : '-' ?></strong></td>
                                <td><span class="badge <?= $class ?>"><?= $estado ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No tienes alumnos registrados en tus materias.
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
    
    // Manejar navegación suave para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Actualizar enlaces activos
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
    
    // Destacar sección activa al hacer scroll
    const sections = document.querySelectorAll('.card[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    function updateActiveNav() {
        let current = '';
        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= 100) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveNav);
});
</script>

</body>
</html>