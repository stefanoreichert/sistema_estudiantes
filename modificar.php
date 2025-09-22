<?php
include 'include/header.php';
include 'config.php';
include 'auth.php';
verificarSesion();
$id=(int)($_GET['id']??0);
$est=$mysqli->query("SELECT id_alumno,nombre,Edad,fecha_nacimiento,id_carrera FROM alumnos WHERE id_alumno=$id")->fetch_assoc();
if(!$est){die('NO_DATA');}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $nombre=trim($_POST['nombre']??'');
  $edad=(int)($_POST['Edad']??0);
  $fecha_nacimiento=$_POST['fecha_nacimiento']??'';
  $carrera_id=(int)($_POST['carrera_id']??0);
  if($carrera_id<=0){$carrera_id=1;}
  $stmt=$mysqli->prepare('UPDATE alumnos SET nombre=?,Edad=?,fecha_nacimiento=?,id_carrera=? WHERE id_alumno=?');
  $stmt->bind_param('sisii',$nombre,$edad,$fecha_nacimiento,$carrera_id,$id);
  $stmt->execute();
  header('Location: estudiantes.php');
  exit;
}

$carreras=$mysqli->query('SELECT id_carrera, nombre, duracion_anios FROM carreras ORDER BY nombre');
?>
<div class="container my-4">
  <h2>Modificar Estudiante</h2>
  <form method="post" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" value="<?=htmlspecialchars($est['nombre'])?>" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Edad</label>
      <input type="number" name="Edad" value="<?=$est['Edad']?>" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Fecha de Nacimiento</label>
      <input type="date" name="fecha_nacimiento" value="<?=$est['fecha_nacimiento']?>" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Carrera</label>
      <select name="carrera_id" class="form-select">
        <?php while($c=$carreras->fetch_assoc()){?>
        <option value="<?=$c['id_carrera']?>" <?=$c['id_carrera']==$est['id_carrera']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option>
        <?php }?>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
      <div class="w-150">
      <button type="submit" class="btn btn-primary w-100">Actualizar</button>
    </div>
    <div class="w-50">
      <a href="estudiantes.php" class="btn btn-danger ms-2 w-100">Salir</a>
    </div>
    </div>
  </form>
</div>
<?php include 'include/footer.php'; ?>
