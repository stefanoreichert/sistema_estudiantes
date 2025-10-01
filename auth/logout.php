<?php
include '../auth.php';
cerrarSesion();
header('Location: login.php?logout=1');
exit;
?>