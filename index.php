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

  <?php include 'include/navbar.php'; ?>

  <!-- Sección de Bienvenida -->
  <div class="container mt-4">
    <div class="welcome-section">
      <div class="p-5 bg-primary text-white rounded-4 shadow-lg text-center">
        <h1 class="display-4 fw-bold">Bienvenido, <?= htmlspecialchars($usuario['username']) ?></h1>
        <p class="lead">Sistema de Gestión Estudiantil</p>
      </div>
    </div>
  </div>

  <!-- Sección de Acceso Rápido -->
  <?php if (esAdmin()): ?>
  <div class="container mt-4">
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="mb-4 text-center">Panel de Administración</h3>
      </div>
      
      <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
            <h5 class="card-title">Estudiantes</h5>
            <p class="card-text">Gestiona la información de los estudiantes, inscripciones y datos académicos.</p>
            <a href="estudiantes.php" class="btn btn-primary">
              <i class="fas fa-arrow-right me-1"></i>Ir a Estudiantes
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
            <h5 class="card-title">Profesores</h5>
            <p class="card-text">Administra el personal docente, especialidades y datos de contacto.</p>
            <a href="profesores.php" class="btn btn-success">
              <i class="fas fa-arrow-right me-1"></i>Ir a Profesores
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-book fa-3x text-info mb-3"></i>
            <h5 class="card-title">Materias</h5>
            <p class="card-text">Configura las materias, créditos, horarios y asignación de profesores.</p>
            <a href="materias.php" class="btn btn-info">
              <i class="fas fa-arrow-right me-1"></i>Ir a Materias
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php elseif (esProfesor()): ?>
  <div class="container mt-4">
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="mb-4 text-center">Panel del Profesor</h3>
      </div>
      <div class="col-md-6 mb-3 mx-auto">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-clipboard-list fa-3x text-warning mb-3"></i>
            <h5 class="card-title">Gestión de Notas</h5>
            <p class="card-text">Administra las calificaciones de tus estudiantes en las materias que impartes.</p>
            <a href="notas.php" class="btn btn-warning">
              <i class="fas fa-arrow-right me-1"></i>Gestionar Notas
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php elseif (esAlumno()): ?>
  <div class="container mt-4">
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="mb-4 text-center">Portal del Estudiante</h3>
      </div>
      <div class="col-md-6 mb-3 mx-auto">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-user-graduate fa-3x text-info mb-3"></i>
            <h5 class="card-title">Mi Perfil Académico</h5>
            <p class="card-text">Consulta tu información personal, calificaciones y progreso académico.</p>
            <a href="perfil_alumno.php" class="btn btn-info">
              <i class="fas fa-arrow-right me-1"></i>Ver Mi Perfil
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

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