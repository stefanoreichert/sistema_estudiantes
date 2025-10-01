<?php
// Funciones de autenticación y seguridad

function iniciarSesion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function obtenerRutaLogin() {
    $current_dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
    if (strpos($current_dir, '/profesor') !== false || strpos($current_dir, '/admin') !== false || 
        strpos($current_dir, '/alumno') !== false || strpos($current_dir, '/auth') !== false) {
        return '../auth/login.php';
    } else {
        return 'auth/login.php';
    }
}

function verificarSesion() {
    iniciarSesion();
    
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['username'])) {
        header('Location: ' . obtenerRutaLogin());
        exit;
    }
    
    // Verificar tiempo de inactividad (30 minutos)
    if (isset($_SESSION['tiempo_login']) && (time() - $_SESSION['tiempo_login']) > 1800) {
        cerrarSesion();
        header('Location: ' . obtenerRutaLogin() . '?error=sesion_expirada');
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
        'nivel' => $_SESSION['nivel'] ?? null,
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
    // Cambiado: ahora retorna la contraseña sin hashear
    return $password;
}

function verificarPassword($password, $hash) {
    // Cambiado: ahora compara directamente las contraseñas
    return $password === $hash;
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

function verificarNivel($nivelesPermitidos) {
    iniciarSesion();
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario['nivel']) {
        header('Location: ' . obtenerRutaLogin());
        exit;
    }
    
    if (!in_array($usuario['nivel'], $nivelesPermitidos)) {
        header('Location: acceso_denegado.php');
        exit;
    }
}

function esAdmin() {
    $usuario = obtenerUsuarioActual();
    return $usuario['nivel'] === 'admin';
}

function esProfesor() {
    $usuario = obtenerUsuarioActual();
    return $usuario['nivel'] === 'profesor';
}

function esAlumno() {
    $usuario = obtenerUsuarioActual();
    return $usuario['nivel'] === 'alumno';
}

function obtenerIdProfesor() {
    global $mysqli;
    $usuario = obtenerUsuarioActual();
    
    if ($usuario['nivel'] !== 'profesor') {
        return null;
    }
    
    // Verificar si la tabla profesores tiene columna id_usuario
    $check_column = $mysqli->query("SHOW COLUMNS FROM profesores LIKE 'id_usuario'");
    if ($check_column && $check_column->num_rows > 0) {
        $stmt = $mysqli->prepare("SELECT id_profesor FROM profesores WHERE id_usuario = ?");
        $stmt->bind_param('i', $usuario['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_profesor'] ?? null;
    } else {
        // Si no existe la columna id_usuario, buscar por nombre de usuario o email
        $stmt = $mysqli->prepare("SELECT id_profesor FROM profesores WHERE email = ? OR CONCAT(nombre, ' ', apellido) = ?");
        $stmt->bind_param('ss', $usuario['username'], $usuario['username']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_profesor'] ?? null;
    }
}

function obtenerIdAlumno() {
    global $mysqli;
    $usuario = obtenerUsuarioActual();
    
    if ($usuario['nivel'] !== 'alumno') {
        return null;
    }
    
    // Verificar si la tabla alumnos tiene columna id_usuario
    $check_column = $mysqli->query("SHOW COLUMNS FROM alumnos LIKE 'id_usuario'");
    if ($check_column && $check_column->num_rows > 0) {
        $stmt = $mysqli->prepare("SELECT id_alumno FROM alumnos WHERE id_usuario = ?");
        $stmt->bind_param('i', $usuario['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_alumno'] ?? null;
    } else {
        // Si no existe la columna id_usuario, buscar por nombre de usuario
        $stmt = $mysqli->prepare("SELECT id_alumno FROM alumnos WHERE nombre = ?");
        $stmt->bind_param('s', $usuario['username']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_alumno'] ?? null;
    }
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