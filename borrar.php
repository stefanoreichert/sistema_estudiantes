<?php
include 'config.php';

$id=(int)($_GET['id']??0);

if($id>0){
  // Verificar que el alumno exista
  $rs=$mysqli->prepare("SELECT id FROM alumno WHERE id=?");
  $rs->bind_param("i",$id);
  $rs->execute();
  $rs->store_result();

  if($rs->num_rows > 0){
    // El alumno existe, se puede borrar
    $stmt=$mysqli->prepare('DELETE FROM alumno WHERE id=?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
  
  }
}

header('Location: estudiantes.php');
exit;
