<?php
include '../include/header.php';
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden modificar materias
$id=(int)($_GET['id']??0);
$mat=$mysqli->query("SELECT id_materia,nombre,id_carrera,id_profesor FROM materias WHERE id_materia=$id")->fetch_assoc();
if(!$mat){die('NO_DATA');}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $nombre=trim($_POST['nombre']??'');
  $carrera_id=(int)($_POST['carrera_id']??0);
  $profesor_id=(int)($_POST['profesor_id']??0);
  if($carrera_id<=0){$carrera_id=1;}
  
  $stmt=$mysqli->prepare('UPDATE materias SET nombre=?,id_carrera=?,id_profesor=? WHERE id_materia=?');
  $stmt->bind_param('siii',$nombre,$carrera_id,$profesor_id,$id);
  $stmt->execute();
  header('Location: materias.php');
  exit;
}

$carreras=$mysqli->query('SELECT id_carrera, nombre FROM carreras ORDER BY nombre');
$profesores=$mysqli->query('SELECT id_profesor, CONCAT(nombre, " ", apellido) as nombre_completo FROM profesores ORDER BY nombre');
?>
<div class="container my-4">
  <h2>Modificar Materia</h2>
  <form method="post" class="row g-3">
    <div class="col-md-12">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" value="<?=htmlspecialchars($mat['nombre'])?>" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Carrera</label>
      <select name="carrera_id" class="form-select">
        <?php while($c=$carreras->fetch_assoc()){?>
        <option value="<?=$c['id_carrera']?>" <?=$c['id_carrera']==$mat['id_carrera']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option>
        <?php }?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Profesor</label>
      <select name="profesor_id" class="form-select">
        <option value="">Sin profesor</option>
        <?php while($p=$profesores->fetch_assoc()){?>
        <option value="<?=$p['id_profesor']?>" <?=$p['id_profesor']==$mat['id_profesor']?'selected':''?>><?=htmlspecialchars($p['nombre_completo'])?></option>
        <?php }?>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
      <div class="w-150">
      <button type="submit" class="btn btn-primary w-100">Actualizar</button>
    </div>
    <div class="w-50">
      <a href="materias.php" class="btn btn-danger ms-2 w-100">Salir</a>
    </div>
    </div>
  </form>
</div>
<?php include 'include/footer.php'; ?>

