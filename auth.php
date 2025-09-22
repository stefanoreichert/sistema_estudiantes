<?php
// Funciones de autenticación y seguridad

function iniciarSesion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function verificarSesion() {
    iniciarSesion();
    
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
    
    // Verificar tiempo de inactividad (30 minutos)
    if (isset($_SESSION['tiempo_login']) && (time() - $_SESSION['tiempo_login']) > 1800) {
        cerrarSesion();
        header('Location: login.php?error=sesion_expirada');
        exit;
    }
    
    // Actualizar tiempo de actividad
    $_SESSION['tiempo_login'] = time();
}

function cerrarSesion() {
    iniciarSesion();
    session_destroy();
    session_unset();
}

function obtenerUsuarioActual() {
    iniciarSesion();
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'tiempo_login' => $_SESSION['tiempo_login'] ?? null
    ];
}

function validarPasswordSegura($password) {
    $errores = [];
    
    // Mínimo 8 caracteres
    if (strlen($password) < 8) {
        $errores[] = 'Debe tener al menos 8 caracteres';
    }
    
    // Al menos una letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = 'Debe contener al menos una letra minúscula';
    }
    
    // Al menos una letra mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'Debe contener al menos una letra mayúscula';
    }
    
    // Al menos un número
    if (!preg_match('/[0-9]/', $password)) {
        $errores[] = 'Debe contener al menos un número';
    }
    
    // Al menos un carácter especial
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errores[] = 'Debe contener al menos un carácter especial (!@#$%^&*)';
    }
    
    return $errores;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generarTokenCSRF() {
    iniciarSesion();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarTokenCSRF($token) {
    iniciarSesion();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function obtenerNavbarUsuario() {
    $usuario = obtenerUsuarioActual();
    if (!$usuario['username']) return '';
    
    return '
    <div class="navbar-nav ms-auto">
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i>
                <span>' . htmlspecialchars($usuario['username']) . '</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Ver Perfil</a></li>
                <li><a class="dropdown-item" href="cambiar_password.php"><i class="fas fa-key me-2"></i>Cambiar Contraseña</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>';
}
?>