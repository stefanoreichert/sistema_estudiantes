<?php
include 'include/header.php';
include 'config.php';
$id=(int)($_GET['id']??0);
$est=$mysqli->query("SELECT id,nombre,edad,id_carrera FROM alumno WHERE id=$id")->fetch_assoc();
if(!$est){die('NO_DATA');}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $nombre=trim($_POST['nombre']??'');
  $edad=(int)($_POST['edad']??0);
  $carrera_id=(int)($_POST['carrera_id']??0);
  if($carrera_id<=0){$carrera_id=1;}
  $stmt=$mysqli->prepare('UPDATE alumno SET nombre=?,edad=?,id_carrera=? WHERE id=?');
  $stmt->bind_param('siii',$nombre,$edad,$carrera_id,$id);
  $stmt->execute();
  header('Location: estudiantes.php');
  exit;
}

$carreras=$mysqli->query('SELECT id,nombre FROM carrera ORDER BY nombre');
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
      <input type="number" name="edad" value="<?=$est['edad']?>" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Carrera</label>
      <select name="carrera_id" class="form-select">
        <?php while($c=$carreras->fetch_assoc()){?>
        <option value="<?=$c['id']?>" <?=$c['id']==$est['id_carrera']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option>
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
