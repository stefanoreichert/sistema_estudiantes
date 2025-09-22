<?php 
require 'config.php'; 
require 'auth.php';
verificarSesion();

$usuario = obtenerUsuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio - Sistema Estudiantes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Asegurar que no haya superposiciones */
    .carousel {
      clear: both;
      margin-top: 0;
    }
    
    .carousel-item img {
      height: 400px;
      object-fit: cover;
    }
    
    /* Espaciado adicional */
    .welcome-section {
      margin-bottom: 30px;
    }
    
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
    .table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>

  <div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#">Sistema Estudiantes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="estudiantes.php">Estudiantes</a></li>
          <li class="nav-item"><a class="nav-link" href="notas.php">Notas</a></li>
          <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
          <li class="nav-item"><a class="nav-link" href="perfil.php">Ver Perfil</a></li>
        </ul>
        <?php
        if ($usuario['username']) {
            echo '
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownIndex" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span>' . htmlspecialchars($usuario['username']) . '</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="cambiar_password.php"><i class="fas fa-key me-2"></i>Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>';
        }
        ?>
      </div>
    </nav>
  </div>

  <!-- Sección de Bienvenida -->
  <div class="container mt-4">
    <div class="welcome-section">
      <div class="p-5 bg-primary text-white rounded-4 shadow-lg text-center">
        <h1 class="display-4 fw-bold">Bienvenido, <?= htmlspecialchars($usuario['username']) ?></h1>
        <p class="lead">Sistema de Gestión Estudiantil</p>
      </div>
    </div>
  </div>

  <!-- Sección del Carousel -->
  <div class="container">
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"></button>
      </div>
      <div class="carousel-inner">

        <div class="carousel-item active">
          <img src="https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&h=300" 
               class="d-block w-100 rounded" alt="Analista de Sistemas"
               onerror="this.src='https://via.placeholder.com/1200x300/007bff/ffffff?text=💻+Analista+de+Sistemas'">
          <div class="carousel-caption d-none d-md-block">
            <div class="bg-dark bg-opacity-75 rounded p-3">
              <h3 class="fw-bold">Analista de Sistemas</h3>
              <p class="mb-0">Innovación tecnológica • Desarrollo de software • Gestión de datos</p>
            </div>
          </div>
        </div>

        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43"
              class="d-block w-100 rounded" alt="Administración de Empresas">
          <div class="carousel-caption d-none d-md-block">
            <div class="bg-dark bg-opacity-75 rounded p-3">
              <h3 class="fw-bold">Administración de Empresas</h3>
              <p class="mb-0">Estrategia, gestión financiera y liderazgo organizacional</p>
            </div>
          </div>
        </div>


        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&h=300" 
               class="d-block w-100 rounded" alt="Recursos Humanos"
               onerror="this.src='https://via.placeholder.com/1200x300/dc3545/ffffff?text=👥+Recursos+Humanos'">
          <div class="carousel-caption d-none d-md-block">
            <div class="bg-dark bg-opacity-75 rounded p-3">
              <h3 class="fw-bold">Recursos Humanos</h3>
              <p class="mb-0">Gestión de talento • Desarrollo organizacional • Bienestar laboral</p>
            </div>
          </div>
        </div>

      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
      </button>
    </div>
  </div>

  <!-- Footer -->
  <?php require 'include/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Inicialización simple sin logs
    document.addEventListener('DOMContentLoaded', function() {
      // Forzar inicialización de todos los dropdowns
      const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
      dropdowns.forEach(function(dropdown) {
        new bootstrap.Dropdown(dropdown);
      });
    });
  </script>
</body>
</html>