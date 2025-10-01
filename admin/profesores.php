<?php
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden gestionar profesores

if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)($_GET['eliminar']);
    if ($id_eliminar > 0) {
        $check = $mysqli->query("SELECT id_profesor FROM profesores WHERE id_profesor=$id_eliminar");
        if ($check && $check->num_rows > 0) {
            $stmt = $mysqli->prepare("DELETE FROM profesores WHERE id_profesor=?");
            $stmt->bind_param('i', $id_eliminar);
            $stmt->execute();
        }
    }
    header('Location: profesores.php');
    exit;
}

// Obtener filtros
$filtro_especialidad = isset($_GET['especialidad']) ? trim($_GET['especialidad']) : '';

// Construir consulta base (nueva estructura)
$sql = "SELECT p.id_profesor, p.nombre, p.apellido, p.email 
        FROM profesores p";

$sql .= " ORDER BY p.id_profesor";
$rs = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profesores</title>
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
    <h2 class="mb-4">Listado de Profesores</h2>
    
    <a href="alta_profesor.php" class="btn btn-primary mb-3">Agregar Profesor</a>


    
    <table class="table table-bordered table-striped table-hover shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaProfesores">
            <?php while ($row = $rs->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id_profesor'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['apellido']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <a href="modificar_profesor.php?id=<?= $row['id_profesor'] ?>" class="btn btn-sm btn-outline-secondary">Modificar</a>
                    <a href="profesores.php?eliminar=<?= $row['id_profesor'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que querés eliminar este profesor?')">Eliminar</a>
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
    const tablaBody = document.getElementById('tablaProfesores');
    const filas = tablaBody.querySelectorAll('tr');
    
    // Contar profesores
    function contarProfesores() {
        const totalElement = document.querySelector('.total-profesores');
        if (totalElement) {
            const filasVisibles = Array.from(filas).filter(fila => 
                fila.style.display !== 'none'
            );
            totalElement.textContent = filasVisibles.length;
        }
    }
    
    // Mostrar información de profesores
    const infoDiv = document.createElement('div');
    infoDiv.className = 'alert alert-info mt-3';
    infoDiv.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        Total de profesores mostrados: <strong class="total-profesores">${filas.length}</strong>
    `;
    document.querySelector('.container').appendChild(infoDiv);
    
    // Animaciones para las filas
    filas.forEach((fila, index) => {
        fila.style.animationDelay = `${index * 0.05}s`;
        fila.classList.add('fade-in');
    });
    
    // Actualizar contador inicial
    contarProfesores();
});

// Confirmación mejorada para eliminar
function confirmarEliminacion(nombre, id) {
    if (confirm(`¿Está seguro que desea eliminar al profesor "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `profesores.php?eliminar=${id}`;
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

