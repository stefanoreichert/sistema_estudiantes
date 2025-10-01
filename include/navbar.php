<?php
// Navbar unificado para todo el sistema
$usuario = obtenerUsuarioActual();
$current_page = basename($_SERVER['PHP_SELF']);

// Determinar la ruta base dependiendo de desde dónde se incluye el navbar
$current_dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
if (strpos($current_dir, '/profesor') !== false || strpos($current_dir, '/admin') !== false || 
    strpos($current_dir, '/alumno') !== false || strpos($current_dir, '/auth') !== false) {
    $base_path = '..';
} else {
    $base_path = '.';
}
?>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema Estudiantes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (esAdmin()): ?>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'index.php' ? ' active' : '' ?>" href="<?= $base_path ?>/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'estudiantes.php' ? ' active' : '' ?>" href="<?= $base_path ?>/admin/estudiantes.php">Estudiantes</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'profesores.php' ? ' active' : '' ?>" href="<?= $base_path ?>/admin/profesores.php">Profesores</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'materias.php' ? ' active' : '' ?>" href="<?= $base_path ?>/admin/materias.php">Materias</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'notas.php' ? ' active' : '' ?>" href="<?= $base_path ?>/profesor/notas.php">Notas</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'reportes.php' ? ' active' : '' ?>" href="<?= $base_path ?>/admin/reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'perfil.php' ? ' active' : '' ?>" href="<?= $base_path ?>/perfil.php">Ver Perfil</a></li>
                <?php elseif (esProfesor()): ?>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'perfil_profesor.php' ? ' active' : '' ?>" href="<?= $base_path ?>/profesor/perfil_profesor.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'notas.php' ? ' active' : '' ?>" href="<?= $base_path ?>/profesor/notas.php">Gestión de Notas</a></li>
                <?php if ($current_page == 'perfil_profesor.php'): ?>
                <li class="nav-item"><a class="nav-link" href="#informacion">Mi Información</a></li>
                <li class="nav-item"><a class="nav-link" href="#estadisticas">Estadísticas</a></li>
                <li class="nav-item"><a class="nav-link" href="#materias">Mis Materias</a></li>
                <li class="nav-item"><a class="nav-link" href="#alumnos">Mis Alumnos</a></li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>/profesor/perfil_profesor.php#informacion">Mi Información</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>/profesor/perfil_profesor.php#estadisticas">Estadísticas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>/profesor/perfil_profesor.php#materias">Mis Materias</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>/profesor/perfil_profesor.php#alumnos">Mis Alumnos</a></li>
                <?php endif; ?>
                <?php elseif (esAlumno()): ?>
                <li class="nav-item"><a class="nav-link<?= $current_page == 'perfil_alumno.php' ? ' active' : '' ?>" href="<?= $base_path ?>/alumno/perfil_alumno.php">Mi Perfil</a></li>
                <?php if ($current_page == 'perfil_alumno.php'): ?>
                <li class="nav-item"><a class="nav-link" href="#informacion">Mi Información</a></li>
                <li class="nav-item"><a class="nav-link" href="#notas">Mis Notas</a></li>
                <li class="nav-item"><a class="nav-link" href="#promedios">Mis Promedios</a></li>
                <li class="nav-item"><a class="nav-link" href="#companeros">Compañeros</a></li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <?php if ($usuario['username']): ?>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span><?= htmlspecialchars($usuario['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $base_path ?>/auth/cambiar_password.php"><i class="fas fa-key me-2"></i>Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= $base_path ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</div>