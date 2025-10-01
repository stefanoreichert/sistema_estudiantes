<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden gestionar materias

if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)($_GET['eliminar']);
    if ($id_eliminar > 0) {
        $check = $mysqli->query("SELECT id_materia FROM materias WHERE id_materia=$id_eliminar");
        if ($check && $check->num_rows > 0) {
            $stmt = $mysqli->prepare("DELETE FROM materias WHERE id_materia=?");
            $stmt->bind_param('i', $id_eliminar);
            $stmt->execute();
        }
    }
    header('Location: materias.php');
    exit;
}

// Obtener filtros
$filtro_carrera = isset($_GET['carrera']) ? (int)$_GET['carrera'] : 0;
$filtro_profesor = isset($_GET['profesor']) ? (int)$_GET['profesor'] : 0;

// Construir consulta base (nueva estructura)
$sql = "SELECT m.id_materia, m.nombre, m.id_carrera, m.id_profesor,
        c.nombre AS carrera_nombre, CONCAT(p.nombre, ' ', p.apellido) AS profesor_nombre 
        FROM materias m 
        LEFT JOIN carreras c ON m.id_carrera = c.id_carrera
        LEFT JOIN profesores p ON m.id_profesor = p.id_profesor";

$where_conditions = [];
if ($filtro_carrera > 0) {
    $where_conditions[] = "m.id_carrera = $filtro_carrera";
}
if ($filtro_profesor > 0) {
    $where_conditions[] = "m.id_profesor = $filtro_profesor";
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY m.id_materia";
$rs = $mysqli->query($sql);

// Obtener carreras y profesores para los filtros
$carreras_rs = $mysqli->query("SELECT id_carrera, nombre FROM carreras ORDER BY nombre");
$profesores_rs = $mysqli->query("SELECT id_profesor, nombre FROM profesores ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Materias</title>
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
    <h2 class="mb-4">Listado de Materias</h2>
    
    <a href="alta_materia.php" class="btn btn-primary mb-3">Agregar Materia</a>

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
                <div class="col-md-4">
                    <label for="profesor" class="form-label"><i class="fas fa-chalkboard-teacher me-1"></i>Profesor</label>
                    <select class="form-select" id="profesor" name="profesor">
                        <option value="0">Todos los profesores</option>
                        <?php while ($profesor = $profesores_rs->fetch_assoc()): ?>
                            <option value="<?= $profesor['id_profesor'] ?>" <?= ($filtro_profesor == $profesor['id_profesor']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($profesor['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <a href="materias.php" class="btn btn-outline-secondary">
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
                <th>Carrera</th>
                <th>Profesor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaMaterias">
            <?php while ($row = $rs->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id_materia'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['carrera_nombre'] ?? 'Sin carrera') ?></td>
                <td><?= htmlspecialchars($row['profesor_nombre'] ?? 'Sin profesor') ?></td>
                <td>
                    <a href="modificar_materia.php?id=<?= $row['id_materia'] ?>" class="btn btn-sm btn-outline-secondary">Modificar</a>
                    <a href="materias.php?eliminar=<?= $row['id_materia'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar esta materia?')">Eliminar</a>
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
    const tablaBody = document.getElementById('tablaMaterias');
    const filas = tablaBody.querySelectorAll('tr');
    
    // Contar materias
    function contarMaterias() {
        const totalElement = document.querySelector('.total-materias');
        if (totalElement) {
            const filasVisibles = Array.from(filas).filter(fila => 
                fila.style.display !== 'none'
            );
            totalElement.textContent = filasVisibles.length;
        }
    }
    
    // Mostrar información de materias
    const infoDiv = document.createElement('div');
    infoDiv.className = 'alert alert-info mt-3';
    infoDiv.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        Total de materias mostradas: <strong class="total-materias">${filas.length}</strong>
    `;
    document.querySelector('.container').appendChild(infoDiv);
    
    // Animaciones para las filas
    filas.forEach((fila, index) => {
        fila.style.animationDelay = `${index * 0.05}s`;
        fila.classList.add('fade-in');
    });
    
    // Actualizar contador inicial
    contarMaterias();
});

// Confirmación mejorada para eliminar
function confirmarEliminacion(nombre, id) {
    if (confirm(`¿Está seguro que desea eliminar la materia "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `materias.php?eliminar=${id}`;
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
<?php require 'include/footer.php'; ?>
</html>

