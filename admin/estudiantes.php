<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden gestionar estudiantes

if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)($_GET['eliminar']);
    if ($id_eliminar > 0) {
        $check = $mysqli->query("SELECT id_alumno FROM alumnos WHERE id_alumno=$id_eliminar");
        if ($check && $check->num_rows > 0) {
            $stmt = $mysqli->prepare("DELETE FROM alumnos WHERE id_alumno=?");
            $stmt->bind_param('i', $id_eliminar);
            $stmt->execute();
        }
    }
    header('Location: estudiantes.php');
    exit;
}

// Obtener filtros
$filtro_carrera = isset($_GET['carrera']) ? (int)$_GET['carrera'] : 0;

// Construir consulta base
$sql = "SELECT a.id_alumno, a.nombre, a.dni, a.Edad, a.fecha_nacimiento, a.id_carrera, c.nombre AS carrera_nombre 
        FROM alumnos a 
        LEFT JOIN carreras c ON a.id_carrera = c.id_carrera";

$where_conditions = [];
if ($filtro_carrera > 0) {
    $where_conditions[] = "a.id_carrera = $filtro_carrera";
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY a.id_alumno";
$rs = $mysqli->query($sql);

// Obtener carreras para el filtro
$carreras_rs = $mysqli->query("SELECT id_carrera, nombre FROM carreras ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estudiantes</title>
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
    </style>
</head>
<body>

<?php include '../include/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Listado de Estudiantes</h2>
    
    <a href="alta.php" class="btn btn-primary mb-3">Agregar Alumno</a>

    <!-- Filtros -->
    <div class="card filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="carrera" class="form-label"><i class="fas fa-graduation-cap me-1"></i>Carrera</label>
                    <select class="form-select" id="carrera" name="carrera">
                        <option value="0">Todas las carreras</option>
                        <?php while ($carrera = $carreras_rs->fetch_assoc()): ?>
                            <option value="<?= $carrera['id_carrera'] ?>" <?= ($filtro_carrera == $carrera['id_carrera']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($carrera['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <a href="estudiantes.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <table class="table table-bordered table-striped table-hover shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Edad</th>
                <th>Fecha Nacimiento</th>
                <th>Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaEstudiantes">
            <?php while ($row = $rs->fetch_assoc()) { ?>
            <tr data-carrera="<?= $row['id_carrera'] ?>">
                <td><?= $row['id_alumno'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['dni'] ?? 'No registrado') ?></td>
                <td><?= $row['Edad'] ?></td>
                <td><?= $row['fecha_nacimiento'] ?></td>
                <td><?= htmlspecialchars($row['carrera_nombre'] ?? 'Sin carrera') ?></td>
                <td>
                    <a href="modificar.php?id=<?= $row['id_alumno'] ?>" class="btn btn-sm btn-outline-secondary">Modificar</a>
                    <a href="estudiantes.php?eliminar=<?= $row['id_alumno'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar este alumno?')">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Test para verificar que Bootstrap funciona
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);
    
    // Inicializar dropdown manualmente como última opción
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    console.log('Dropdowns initialized:', dropdownList.length);
});

// Filtrado en tiempo real adicional
document.addEventListener('DOMContentLoaded', function() {
    const tablaBody = document.getElementById('tablaEstudiantes');
    const filas = tablaBody.querySelectorAll('tr');
    
    // Contar estudiantes por carrera
    function contarEstudiantes() {
        const totalElement = document.querySelector('.total-estudiantes');
        if (totalElement) {
            const filasVisibles = Array.from(filas).filter(fila => 
                fila.style.display !== 'none'
            );
            totalElement.textContent = filasVisibles.length;
        }
    }
    
    // Mostrar información de estudiantes
    const infoDiv = document.createElement('div');
    infoDiv.className = 'alert alert-info mt-3';
    infoDiv.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        Total de estudiantes mostrados: <strong class="total-estudiantes">${filas.length}</strong>
    `;
    document.querySelector('.container').appendChild(infoDiv);
    
    // Animaciones para las filas
    filas.forEach((fila, index) => {
        fila.style.animationDelay = `${index * 0.05}s`;
        fila.classList.add('fade-in');
    });
    
    // Actualizar contador inicial
    contarEstudiantes();
});

// Confirmación mejorada para eliminar
function confirmarEliminacion(nombre, id) {
    if (confirm(`¿Está seguro que desea eliminar al estudiante "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `estudiantes.php?eliminar=${id}`;
    }
}
</script>

<style>
.fade-in {
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn-group .btn {
    margin: 0 2px;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.85em;
}

code {
    background-color: #f8f9fa;
    color: #495057;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 0.9em;
}
</style>

</body>
<?php require '../include/footer.php'; ?>
</html>

