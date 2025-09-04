<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema Estudiantes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
  <div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#">Sistema Estudiantes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
          
          <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
          <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
        </ul>
      </div>
    </nav>
  </div>
  <!-- Container de bienvenida -->
<!-- Container de bienvenida -->
<div class="container mt-5">
  <div class="p-5 bg-primary text-white rounded-4 shadow-lg text-center-bg-primary text-white rounded-4 shadow-lg text-center">
    <h1 class="display-4 fw-bold">Bienvenido al Sistema de Estudiantes</h1>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>



<?php require 'include/footer.php'; ?>
