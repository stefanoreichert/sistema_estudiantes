<?php
include '../include/header.php';
include '../config.php';
include '../auth.php';
verificarSesion();
verificarNivel(['admin']); // Solo administradores pueden modificar profesores
$id=(int)($_GET['id']??0);
$prof=$mysqli->query("SELECT id_profesor,nombre,apellido,email FROM profesores WHERE id_profesor=$id")->fetch_assoc();
if(!$prof){die('NO_DATA');}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $nombre=trim($_POST['nombre']??'');
  $apellido=trim($_POST['apellido']??'');
  $email=trim($_POST['email']??'');
  
  $stmt=$mysqli->prepare('UPDATE profesores SET nombre=?,apellido=?,email=? WHERE id_profesor=?');
  $stmt->bind_param('sssi',$nombre,$apellido,$email,$id);
  $stmt->execute();
  header('Location: profesores.php');
  exit;
}
?>
<div class="container my-4">
  <h2>Modificar Profesor</h2>
  <form method="post" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" value="<?=htmlspecialchars($prof['nombre'])?>" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" value="<?=htmlspecialchars($prof['apellido'])?>" class="form-control" required>
    </div>
    <div class="col-md-12">
      <label class="form-label">Email</label>
      <input type="email" name="email" value="<?=htmlspecialchars($prof['email'])?>" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
      <div class="w-150">
      <button type="submit" class="btn btn-primary w-100">Actualizar</button>
    </div>
    <div class="w-50">
      <a href="profesores.php" class="btn btn-danger ms-2 w-100">Salir</a>
    </div>
    </div>
  </form>
</div>
<?php include 'include/footer.php'; ?>

