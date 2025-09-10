<?php
include 'config.php';

// Manejo de POST
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='cargar'){
        $id_alumno=(int)($_POST['id_alumno']??0);
        $id_materia=(int)($_POST['id_materia']??0);
        $nota=(float)($_POST['nota']??0);
        if($id_alumno>0 && $id_materia>0){
            $stmt=$mysqli->prepare('INSERT INTO notas(id_alumno,id_materia,nota) VALUES(?,?,?)');
            $stmt->bind_param('iid',$id_alumno,$id_materia,$nota);
            $stmt->execute();
            $mysqli->commit();

        }
    }
    if($accion==='borrar'){
        $id=(int)($_POST['id']??0);
        if($id>0){
            $stmt=$mysqli->prepare('DELETE FROM notas WHERE id=?');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $mysqli->commit();

        }
    }
    if($accion==='modificar'){
        $id=(int)($_POST['id']??0);
        $nota=(float)($_POST['nota']??0);
        if($id>0){
            $stmt=$mysqli->prepare('UPDATE notas SET nota=? WHERE id=?');
            $stmt->bind_param('di',$nota,$id);
            $stmt->execute();
            $mysqli->commit();

        }
    }
    header('Location: notas.php');
    exit;
}

// Traer alumnos
$alumnos=$mysqli->query('SELECT id,nombre,ID_carrera FROM alumno ORDER BY nombre');

// Traer todas las materias con su carrera
$materias=$mysqli->query('SELECT id,nombre,id_carrera FROM materia ORDER BY nombre');

// Traer notas
$rs=$mysqli->query('SELECT n.id,a.nombre AS alumno,m.nombre AS materia,n.nota 
                    FROM notas n 
                    JOIN alumno a ON a.id=n.id_alumno 
                    JOIN materia m ON m.id=n.id_materia 
                    ORDER BY a.nombre,m.nombre');
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notas - Sistema Estudiantes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar igual a estudiantes y alta -->
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
                
                <li class="nav-item"><a class="nav-link active" href="notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
            </ul>
        </div>
    </nav>
</div>

<div class="container my-4">
    <h2 class="mb-4">Gestión de Notas</h2>

    <!-- Formulario Agregar Nota -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="cargar">
        <div class="col-md-4">
            <select name="id_alumno" class="form-select" id="selectAlumno" onchange="filtrarMaterias()" required>
                <option value="">Seleccionar Alumno</option>
                <?php while($a=$alumnos->fetch_assoc()){?>
                    <option value="<?=$a['id']?>" data-carrera="<?=$a['ID_carrera']?>"><?=htmlspecialchars($a['nombre'])?></option>
                <?php }?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="id_materia" class="form-select" id="selectMateria" required>
                <option value="">Seleccione la materia</option>
                <?php
                $materias->data_seek(0); // reset pointer
                while($m=$materias->fetch_assoc()){ ?>
                    <option value="<?=$m['id']?>" data-carrera="<?=$m['id_carrera']?>"><?=htmlspecialchars($m['nombre'])?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="nota" placeholder="Nota" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Agregar Nota</button>
        </div>
    </form>

    <!-- Listado de notas -->
    <h3>Listado de Notas</h3>
    <table class="table table-striped table-bordered">
        <thead>
            <tr><th>Alumno</th><th>Materia</th><th>Nota</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php while($row=$rs->fetch_assoc()){ ?>
            <tr>
                <td><?=htmlspecialchars($row['alumno'])?></td>
                <td><?=htmlspecialchars($row['materia'])?></td>
                <td><?=$row['nota']?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="accion" value="borrar">
                        <input type="hidden" name="id" value="<?=$row['id']?>">
                        <button type="submit" class="btn btn-danger btn-sm">Borrar Nota</button>
                    </form>
                    <form method="post" style="display:inline;" class="ms-1">
                        <input type="hidden" name="accion" value="modificar">
                        <input type="hidden" name="id" value="<?=$row['id']?>">
                        <input type="number" step="0.01" name="nota" value="<?=$row['nota']?>" class="form-control form-control-sm d-inline w-auto" style="display:inline-block" required>
                        <button type="submit" class="btn btn-warning btn-sm">Modificar Nota</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
// Filtrar materias según carrera del alumno
function filtrarMaterias(){
    const selectAlumno=document.getElementById('selectAlumno');
    const selectMateria=document.getElementById('selectMateria');
    const carreraAlumno=selectAlumno.selectedOptions[0]?.dataset.carrera;
    const opciones=selectMateria.querySelectorAll('option');

    opciones.forEach(o=>{
        if(o.value!==''){ 
            o.style.display=(o.dataset.carrera===carreraAlumno)?'block':'none';
        }
    });
    selectMateria.value='';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php require 'include/footer.php'; ?>
</html>
