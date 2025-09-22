<?php
include 'config.php';
include 'auth.php';
verificarSesion();

$mensaje = '';

// Manejo de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'cargar') {
        $id_alumno = (int)($_POST['id_alumno'] ?? 0);
        $id_materia = (int)($_POST['id_materia'] ?? 0);
        $nota1 = (float)($_POST['nota1'] ?? 0);
        $nota2 = (float)($_POST['nota2'] ?? 0);
        $nota3 = (float)($_POST['nota3'] ?? 0);
        
        if ($id_alumno > 0 && $id_materia > 0) {
            // Verificar si ya existe una nota para este alumno y materia
            $check = $mysqli->prepare('SELECT id_nota FROM notas WHERE id_alumno = ? AND id_materia = ?');
            $check->bind_param('ii', $id_alumno, $id_materia);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                $mensaje = "Ya existe una nota registrada para este alumno en esta materia.";
            } else {
                $stmt = $mysqli->prepare('INSERT INTO notas(id_alumno, id_materia, nota1, nota2, nota3) VALUES(?,?,?,?,?)');
                $stmt->bind_param('iiddd', $id_alumno, $id_materia, $nota1, $nota2, $nota3);
                if ($stmt->execute()) {
                    $mensaje = "Nota registrada exitosamente.";
                } else {
                    $mensaje = "Error al registrar la nota.";
                }
            }
        } else {
            $mensaje = "Debe seleccionar alumno y materia.";
        }
    }
    
    if ($accion === 'borrar') {
        $id_nota = (int)($_POST['id_nota'] ?? 0);
        if ($id_nota > 0) {
            $stmt = $mysqli->prepare('DELETE FROM notas WHERE id_nota = ?');
            $stmt->bind_param('i', $id_nota);
            if ($stmt->execute()) {
                $mensaje = "Nota eliminada exitosamente.";
            } else {
                $mensaje = "Error al eliminar la nota.";
            }
        }
    }
    
    if ($accion === 'modificar') {
        $id_nota = (int)($_POST['id_nota'] ?? 0);
        $nota1 = (float)($_POST['nota1'] ?? 0);
        $nota2 = (float)($_POST['nota2'] ?? 0);
        $nota3 = (float)($_POST['nota3'] ?? 0);
        
        if ($id_nota > 0) {
            $stmt = $mysqli->prepare('UPDATE notas SET nota1 = ?, nota2 = ?, nota3 = ? WHERE id_nota = ?');
            $stmt->bind_param('dddi', $nota1, $nota2, $nota3, $id_nota);
            if ($stmt->execute()) {
                $mensaje = "Nota actualizada exitosamente.";
            } else {
                $mensaje = "Error al actualizar la nota.";
            }
        }
    }
}

// Obtener carreras para filtros
$carreras = $mysqli->query('SELECT id_carrera, nombre FROM carreras ORDER BY nombre');

// Obtener alumnos
$alumnos = $mysqli->query('SELECT id_alumno, nombre, id_carrera FROM alumnos ORDER BY nombre');

// Obtener materias
$materias = $mysqli->query('SELECT id_materia, nombre, id_carrera FROM materias ORDER BY nombre');

// Obtener notas con información completa incluyendo carreras
$rs = $mysqli->query('SELECT n.id_nota, a.nombre AS alumno, m.nombre AS materia, n.nota1, n.nota2, n.nota3,
                      ROUND((n.nota1 + n.nota2 + n.nota3) / 3, 2) AS promedio, c.nombre AS carrera
                      FROM notas n 
                      JOIN alumnos a ON a.id_alumno = n.id_alumno 
                      JOIN materias m ON m.id_materia = n.id_materia 
                      JOIN carreras c ON c.id_carrera = a.id_carrera
                      ORDER BY a.nombre, m.nombre');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notas - Sistema Estudiantes</title>
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
        .card-header h6 {
            margin: 0;
            color: white;
        }
        .filter-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
</head>
<body>

<!-- Navbar -->
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
                <li class="nav-item"><a class="nav-link active" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="perfil.php">Ver Perfil</a></li>
            </ul>
            <?php
            $usuario = obtenerUsuarioActual();
            if ($usuario['username']) {
                echo '
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownNotas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

<div class="container my-4">
    <h2 class="mb-4">Gestión de Notas</h2>

    <!-- Mostrar mensajes -->
    <?php if ($mensaje != '') { ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- Filtros -->
    <div class="card filter-card mb-4">
        <div class="card-header">
            <h6><i class="fas fa-filter me-2"></i>Filtros para Agregar Notas</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Carrera</label>
                    <select id="filtroCarrera" class="form-select" onchange="aplicarFiltros()">
                        <option value="">Todas las Carreras</option>
                        <?php while($carrera = $carreras->fetch_assoc()) { ?>
                        <option value="<?= $carrera['id_carrera'] ?>"><?= htmlspecialchars($carrera['nombre']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Materia</label>
                    <select id="filtroMateria" class="form-select" onchange="aplicarFiltrosMateria()">
                        <option value="">Todas las Materias</option>
                        <?php 
                        $materias->data_seek(0);
                        while($materia = $materias->fetch_assoc()) { ?>
                        <option value="<?= $materia['id_materia'] ?>" data-carrera="<?= $materia['id_carrera'] ?>">
                            <?= htmlspecialchars($materia['nombre']) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario Agregar Nota -->
    <div class="card filter-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-plus me-2"></i>Registrar Nueva Nota</h5>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="accion" value="cargar">
                
                <div class="col-md-3">
                    <label class="form-label">Alumno</label>
                    <select name="id_alumno" class="form-select" id="selectAlumno" onchange="filtrarMaterias()" required>
                        <option value="">Seleccionar Alumno</option>
                        <?php while($a = $alumnos->fetch_assoc()) { ?>
                        <option value="<?= $a['id_alumno'] ?>" data-carrera="<?= $a['id_carrera'] ?>">
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Materia</label>
                    <select name="id_materia" class="form-select" id="selectMateria" required>
                        <option value="">Seleccionar Materia</option>
                        <?php 
                        $materias->data_seek(0);
                        while($m = $materias->fetch_assoc()) { ?>
                        <option value="<?= $m['id_materia'] ?>" data-carrera="<?= $m['id_carrera'] ?>">
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Nota 1</label>
                    <input type="number" name="nota1" class="form-control" step="0.1" min="0" max="10" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Nota 2</label>
                    <input type="number" name="nota2" class="form-control" step="0.1" min="0" max="10" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Nota 3</label>
                    <input type="number" name="nota3" class="form-control" step="0.1" min="0" max="10" required>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar Nota</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Notas -->
    <div class="card filter-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Notas Registradas</h5>
            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosTabla">
                <i class="fas fa-filter"></i> Filtros de Visualización
            </button>
        </div>
        
        <!-- Filtros para la tabla -->
        <div class="collapse" id="filtrosTabla">
            <div class="card-body border-bottom bg-light">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Carrera</label>
                        <select id="filtroTablaCarrera" class="form-select form-select-sm" onchange="filtrarTablaNotas()">
                            <option value="">Todas las Carreras</option>
                            <?php 
                            $carreras->data_seek(0);
                            while($carrera = $carreras->fetch_assoc()) { ?>
                            <option value="<?= $carrera['id_carrera'] ?>"><?= htmlspecialchars($carrera['nombre']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Alumno</label>
                        <select id="filtroTablaAlumno" class="form-select form-select-sm" onchange="filtrarTablaNotas()">
                            <option value="">Todos los Alumnos</option>
                            <?php 
                            $alumnos->data_seek(0);
                            while($alumno = $alumnos->fetch_assoc()) { ?>
                            <option value="<?= htmlspecialchars($alumno['nombre']) ?>" data-carrera="<?= $alumno['id_carrera'] ?>">
                                <?= htmlspecialchars($alumno['nombre']) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Materia</label>
                        <select id="filtroTablaMateria" class="form-select form-select-sm" onchange="filtrarTablaNotas()">
                            <option value="">Todas las Materias</option>
                            <?php 
                            $materias->data_seek(0);
                            while($materia = $materias->fetch_assoc()) { ?>
                            <option value="<?= htmlspecialchars($materia['nombre']) ?>" data-carrera="<?= $materia['id_carrera'] ?>">
                                <?= htmlspecialchars($materia['nombre']) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="limpiarFiltrosTabla()">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                        <small class="text-muted align-self-center">
                            <span id="contadorFilas"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tablaNotas">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Materia</th>
                            <th>Nota 1</th>
                            <th>Nota 2</th>
                            <th>Nota 3</th>
                            <th>Promedio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $rs->fetch_assoc()) { ?>
                        <tr data-alumno="<?= htmlspecialchars($row['alumno']) ?>" 
                            data-materia="<?= htmlspecialchars($row['materia']) ?>" 
                            data-carrera="<?= htmlspecialchars($row['carrera']) ?>">
                            <td><?= htmlspecialchars($row['alumno']) ?></td>
                            <td><?= htmlspecialchars($row['materia']) ?></td>
                            <td><?= $row['nota1'] ?></td>
                            <td><?= $row['nota2'] ?></td>
                            <td><?= $row['nota3'] ?></td>
                            <td><strong><?= $row['promedio'] ?></strong></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editarNota(<?= $row['id_nota'] ?>, <?= $row['nota1'] ?>, <?= $row['nota2'] ?>, <?= $row['nota3'] ?>)">
                                    Editar
                                </button>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="accion" value="borrar">
                                    <input type="hidden" name="id_nota" value="<?= $row['id_nota'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('¿Seguro que quiere eliminar esta nota?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar notas -->
<div class="modal fade" id="modalEditarNota" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="modificar">
                    <input type="hidden" name="id_nota" id="editIdNota">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nota 1</label>
                            <input type="number" name="nota1" id="editNota1" class="form-control" step="0.1" min="0" max="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nota 2</label>
                            <input type="number" name="nota2" id="editNota2" class="form-control" step="0.1" min="0" max="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nota 3</label>
                            <input type="number" name="nota3" id="editNota3" class="form-control" step="0.1" min="0" max="10" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Aplicar filtros por carrera
function aplicarFiltros() {
    const filtroCarrera = document.getElementById('filtroCarrera');
    const selectAlumno = document.getElementById('selectAlumno');
    const selectMateria = document.getElementById('selectMateria');
    const carreraSeleccionada = filtroCarrera.value;
    
    // Filtrar alumnos por carrera
    Array.from(selectAlumno.options).forEach(option => {
        if (option.value !== '') {
            if (carreraSeleccionada === '' || option.dataset.carrera === carreraSeleccionada) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Filtrar materias por carrera
    Array.from(selectMateria.options).forEach(option => {
        if (option.value !== '') {
            if (carreraSeleccionada === '' || option.dataset.carrera === carreraSeleccionada) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Limpiar selecciones
    selectAlumno.value = '';
    selectMateria.value = '';
}

// Aplicar filtro por materia (también filtra alumnos de esa carrera)
function aplicarFiltrosMateria() {
    const filtroMateria = document.getElementById('filtroMateria');
    const selectAlumno = document.getElementById('selectAlumno');
    const selectMateria = document.getElementById('selectMateria');
    const materiaSeleccionada = filtroMateria.value;
    
    if (materiaSeleccionada !== '') {
        const materiaOption = filtroMateria.selectedOptions[0];
        const carreraMateria = materiaOption.dataset.carrera;
        
        // Filtrar alumnos por la carrera de la materia seleccionada
        Array.from(selectAlumno.options).forEach(option => {
            if (option.value !== '') {
                if (option.dataset.carrera === carreraMateria) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });
        
        // Pre-seleccionar la materia en el formulario
        selectMateria.value = materiaSeleccionada;
        
        // Actualizar el filtro de carrera para consistencia
        document.getElementById('filtroCarrera').value = carreraMateria;
    }
    
    selectAlumno.value = '';
}

// Limpiar todos los filtros
function limpiarFiltros() {
    document.getElementById('filtroCarrera').value = '';
    document.getElementById('filtroMateria').value = '';
    document.getElementById('selectAlumno').value = '';
    document.getElementById('selectMateria').value = '';
    
    const selectAlumno = document.getElementById('selectAlumno');
    const selectMateria = document.getElementById('selectMateria');
    
    // Mostrar todas las opciones
    Array.from(selectAlumno.options).forEach(option => {
        if (option.value !== '') option.style.display = 'block';
    });
    Array.from(selectMateria.options).forEach(option => {
        if (option.value !== '') option.style.display = 'block';
    });
}

// Filtrar materias según carrera del alumno seleccionado
function filtrarMaterias() {
    const selectAlumno = document.getElementById('selectAlumno');
    const selectMateria = document.getElementById('selectMateria');
    
    if (!selectAlumno.value) {
        // Si no hay alumno seleccionado, aplicar filtros existentes
        const filtroCarrera = document.getElementById('filtroCarrera');
        if (filtroCarrera.value) {
            aplicarFiltros();
        } else {
            Array.from(selectMateria.options).forEach(option => {
                if (option.value !== '') option.style.display = 'block';
            });
        }
        return;
    }
    
    const carreraAlumno = selectAlumno.selectedOptions[0]?.dataset.carrera;
    
    // Mostrar solo las materias de la carrera del alumno
    Array.from(selectMateria.options).forEach(option => {
        if (option.value !== '') {
            option.style.display = (option.dataset.carrera === carreraAlumno) ? 'block' : 'none';
        }
    });
    
    // Si hay una materia pre-seleccionada del filtro, mantenerla si es de la misma carrera
    const materiaActual = selectMateria.value;
    if (materiaActual) {
        const materiaOption = selectMateria.querySelector(`option[value="${materiaActual}"]`);
        if (materiaOption && materiaOption.dataset.carrera !== carreraAlumno) {
            selectMateria.value = '';
        }
    }
}

// Función para editar nota
function editarNota(idNota, nota1, nota2, nota3) {
    document.getElementById('editIdNota').value = idNota;
    document.getElementById('editNota1').value = nota1;
    document.getElementById('editNota2').value = nota2;
    document.getElementById('editNota3').value = nota3;
    
    new bootstrap.Modal(document.getElementById('modalEditarNota')).show();
}

// Filtrar filas de la tabla de notas
function filtrarTablaNotas() {
    const filtroCarrera = document.getElementById('filtroTablaCarrera');
    const filtroAlumno = document.getElementById('filtroTablaAlumno');
    const filtroMateria = document.getElementById('filtroTablaMateria');
    
    const tabla = document.getElementById('tablaNotas');
    const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let filasVisibles = 0;
    
    // Obtener texto seleccionado de los filtros
    const textCarrera = filtroCarrera.selectedOptions[0] ? filtroCarrera.selectedOptions[0].textContent.trim() : '';
    const textAlumno = filtroAlumno.value.trim();
    const textMateria = filtroMateria.value.trim();
    
    for (let i = 0; i < filas.length; i++) {
        const fila = filas[i];
        const carrera = fila.getAttribute('data-carrera').trim();
        const alumno = fila.getAttribute('data-alumno').trim();
        const materia = fila.getAttribute('data-materia').trim();
        
        let mostrar = true;
        
        // Filtro por carrera
        if (textCarrera && textCarrera !== 'Todas las Carreras' && carrera !== textCarrera) {
            mostrar = false;
        }
        
        // Filtro por alumno
        if (textAlumno && textAlumno !== 'Todos los Alumnos' && alumno !== textAlumno) {
            mostrar = false;
        }
        
        // Filtro por materia
        if (textMateria && textMateria !== 'Todas las Materias' && materia !== textMateria) {
            mostrar = false;
        }
        
        if (mostrar) {
            fila.style.display = '';
            filasVisibles++;
        } else {
            fila.style.display = 'none';
        }
    }
    
    // Actualizar contador
    document.getElementById('contadorFilas').textContent = `${filasVisibles} nota(s) mostrada(s)`;
    
    // Si se selecciona una carrera, filtrar las opciones de alumnos y materias
    if (filtroCarrera.value) {
        filtrarOpcionesPorCarrera();
    }
}

// Filtrar opciones de alumnos y materias por carrera seleccionada
function filtrarOpcionesPorCarrera() {
    const carreraSeleccionada = document.getElementById('filtroTablaCarrera').value;
    const selectAlumno = document.getElementById('filtroTablaAlumno');
    const selectMateria = document.getElementById('filtroTablaMateria');
    
    // Filtrar alumnos
    Array.from(selectAlumno.options).forEach(option => {
        if (option.value !== '') {
            if (carreraSeleccionada === '' || option.dataset.carrera === carreraSeleccionada) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Filtrar materias
    Array.from(selectMateria.options).forEach(option => {
        if (option.value !== '') {
            if (carreraSeleccionada === '' || option.dataset.carrera === carreraSeleccionada) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    // Limpiar selecciones si no coinciden con la carrera
    if (selectAlumno.value && selectAlumno.selectedOptions[0].dataset.carrera !== carreraSeleccionada) {
        selectAlumno.value = '';
    }
    if (selectMateria.value && selectMateria.selectedOptions[0].dataset.carrera !== carreraSeleccionada) {
        selectMateria.value = '';
    }
}

// Limpiar filtros de la tabla
function limpiarFiltrosTabla() {
    document.getElementById('filtroTablaCarrera').value = '';
    document.getElementById('filtroTablaAlumno').value = '';
    document.getElementById('filtroTablaMateria').value = '';
    
    // Mostrar todas las opciones
    const selectAlumno = document.getElementById('filtroTablaAlumno');
    const selectMateria = document.getElementById('filtroTablaMateria');
    
    Array.from(selectAlumno.options).forEach(option => {
        if (option.value !== '') option.style.display = 'block';
    });
    Array.from(selectMateria.options).forEach(option => {
        if (option.value !== '') option.style.display = 'block';
    });
    
    // Mostrar todas las filas
    const tabla = document.getElementById('tablaNotas');
    const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let totalFilas = 0;
    
    for (let i = 0; i < filas.length; i++) {
        filas[i].style.display = '';
        totalFilas++;
    }
    
    // Actualizar contador
    document.getElementById('contadorFilas').textContent = `${totalFilas} nota(s) mostrada(s)`;
}

// Inicializar contador al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const tabla = document.getElementById('tablaNotas');
    const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    document.getElementById('contadorFilas').textContent = `${filas.length} nota(s) mostrada(s)`;
});
</script>

</body>
</html>