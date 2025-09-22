<?php
session_start();
include 'config.php';
include 'auth.php';

$error = '';
$success = '';

// Verificar si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Mostrar mensaje de sesión expirada
if (isset($_GET['error']) && $_GET['error'] === 'sesion_expirada') {
    $error = 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.';
}

// Mostrar mensaje de logout exitoso
if (isset($_GET['logout'])) {
    $success = 'Sesión cerrada exitosamente.';
}

// Manejo del formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    if ($accion === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($username && $password) {
            $stmt = $mysqli->prepare("SELECT id, username, password FROM usuarios WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                // Verificar contraseña (si está hasheada o en texto plano)
                $passwordValida = false;
                
                if (password_verify($password, $user['password'])) {
                    // Password hasheada
                    $passwordValida = true;
                } elseif ($user['password'] === $password) {
                    // Password en texto plano (para compatibilidad)
                    $passwordValida = true;
                    
                    // Actualizar a password hasheada
                    $newHash = hashPassword($password);
                    $updateStmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $updateStmt->bind_param('si', $newHash, $user['id']);
                    $updateStmt->execute();
                }
                
                if ($passwordValida) {
                    // Login exitoso - crear sesión
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['tiempo_login'] = time();
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'Usuario no encontrado.';
            }
        } else {
            $error = 'Por favor complete todos los campos.';
        }
    }
    
    if ($accion === 'registro') {
        $username = trim($_POST['reg_username'] ?? '');
        $password = trim($_POST['reg_password'] ?? '');
        $password_confirm = trim($_POST['reg_password_confirm'] ?? '');
        
        if ($username && $password) {
            if ($password !== $password_confirm) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                // Validar fortaleza de la contraseña
                $erroresPassword = validarPasswordSegura($password);
                if (!empty($erroresPassword)) {
                    $error = 'La contraseña no cumple los requisitos:<br>• ' . implode('<br>• ', $erroresPassword);
                } else {
                    // Verificar si ya existe el usuario
                    $check = $mysqli->prepare("SELECT id FROM usuarios WHERE username = ?");
                    $check->bind_param('s', $username);
                    $check->execute();
                    $exists = $check->get_result();
                    
                    if ($exists->num_rows > 0) {
                        $error = 'Ya existe un usuario con ese nombre.';
                    } else {
                        // Crear nuevo usuario con contraseña hasheada
                        $hashedPassword = hashPassword($password);
                        $stmt = $mysqli->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
                        $stmt->bind_param('ss', $username, $hashedPassword);
                        
                        if ($stmt->execute()) {
                            $success = 'Usuario registrado exitosamente. Ya puede iniciar sesión.';
                        } else {
                            $error = 'Error al registrar el usuario.';
                        }
                    }
                }
            }
        } else {
            $error = 'El nombre de usuario y contraseña son obligatorios.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Estudiantes - Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            margin: auto;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.9;
            margin: 0;
        }
        
        .form-section {
            padding: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 1rem 1.5rem;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
        }
        
        .feature-list {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .feature-list h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .feature-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            color: #6c757d;
        }
        
        .feature-list li i {
            color: #667eea;
            width: 20px;
        }
        
        .sistema-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                <h1>Sistema de Gestión Estudiantil</h1>
                <p>Plataforma Integral para la Administración Académica</p>
            </div>

            <div class="row no-gutters">
                <!-- Formularios -->
                <div class="col-lg-7">
                    <div class="form-section">
                        <!-- Mensajes -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-4" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button">
                                    <i class="fas fa-user-plus me-2"></i>Registrarse
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="authTabsContent">
                            <!-- Login -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form method="POST" action="">
                                    <input type="hidden" name="accion" value="login">
                                    
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Usuario" required>
                                        <label for="username"><i class="fas fa-user me-2"></i>Nombre de Usuario</label>
                                    </div>
                                    
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                                        <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>Ingresar al Sistema
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Register -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form method="POST" action="">
                                    <input type="hidden" name="accion" value="registro">
                                    
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="reg_username" name="reg_username" placeholder="Usuario" required>
                                        <label for="reg_username"><i class="fas fa-user me-2"></i>Nombre de Usuario</label>
                                    </div>
                                    
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="reg_password" name="reg_password" placeholder="Contraseña" required>
                                        <label for="reg_password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                        <div class="password-strength mt-2" id="passwordStrength" style="display: none;">
                                            <small class="text-muted">Requisitos de la contraseña:</small>
                                            <ul class="small mt-1">
                                                <li id="length" class="text-danger"><i class="fas fa-times"></i> Al menos 8 caracteres</li>
                                                <li id="lowercase" class="text-danger"><i class="fas fa-times"></i> Una letra minúscula</li>
                                                <li id="uppercase" class="text-danger"><i class="fas fa-times"></i> Una letra mayúscula</li>
                                                <li id="number" class="text-danger"><i class="fas fa-times"></i> Un número</li>
                                                <li id="special" class="text-danger"><i class="fas fa-times"></i> Un carácter especial</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="reg_password_confirm" name="reg_password_confirm" placeholder="Confirmar Contraseña" required>
                                        <label for="reg_password_confirm"><i class="fas fa-lock me-2"></i>Confirmar Contraseña</label>
                                        <div class="invalid-feedback" id="passwordMismatch">Las contraseñas no coinciden</div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary" id="registerBtn" disabled>
                                            <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="feature-list">
                            <h6><i class="fas fa-info-circle me-2"></i>Información de Acceso</h6>
                            <ul>
                                <li><i class="fas fa-user me-2"></i>Ingrese con su nombre de usuario y contraseña</li>
                                <li><i class="fas fa-shield-alt me-2"></i>Las contraseñas deben ser seguras y están cifradas</li>
                                <li><i class="fas fa-clock me-2"></i>Las sesiones expiran después de 30 minutos de inactividad</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Información del sistema -->
                <div class="col-lg-5">
                    <div class="sistema-info">
                        <h4 class="text-center mb-4">
                            <i class="fas fa-laptop-code me-2"></i>Sistema de Gestión
                        </h4>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-users me-2"></i>Gestión de Estudiantes</h6>
                            <p class="text-muted small">Administre información completa de alumnos, carreras y datos académicos.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-clipboard-list me-2"></i>Control de Notas</h6>
                            <p class="text-muted small">Registre y consulte calificaciones con sistema de promedios automático.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-chart-bar me-2"></i>Reportes Académicos</h6>
                            <p class="text-muted small">Genere reportes estadísticos y de rendimiento por carrera.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-filter me-2"></i>Filtros Avanzados</h6>
                            <p class="text-muted small">Herramientas de búsqueda y filtrado para gestión eficiente.</p>
                        </div>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Sistema Seguro | 
                                <i class="fas fa-mobile-alt me-1"></i>
                                Responsive | 
                                <i class="fas fa-tachometer-alt me-1"></i>
                                Rápido
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-limpiar mensajes después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Enfocar primer campo al cambiar tabs
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
                const firstInput = targetPane.querySelector('input[type="text"], input[type="email"]');
                if (firstInput) firstInput.focus();
            });
        });

        // Validación de contraseña en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('reg_password');
            const confirmInput = document.getElementById('reg_password_confirm');
            const strengthDiv = document.getElementById('passwordStrength');
            const registerBtn = document.getElementById('registerBtn');
            
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    strengthDiv.style.display = password.length > 0 ? 'block' : 'none';
                    
                    // Validar cada requisito
                    validateRequirement('length', password.length >= 8);
                    validateRequirement('lowercase', /[a-z]/.test(password));
                    validateRequirement('uppercase', /[A-Z]/.test(password));
                    validateRequirement('number', /[0-9]/.test(password));
                    validateRequirement('special', /[^a-zA-Z0-9]/.test(password));
                    
                    checkFormValidity();
                });
                
                confirmInput.addEventListener('input', function() {
                    const password = passwordInput.value;
                    const confirm = this.value;
                    const mismatch = document.getElementById('passwordMismatch');
                    
                    if (confirm.length > 0 && password !== confirm) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                    
                    checkFormValidity();
                });
            }
            
            function validateRequirement(id, isValid) {
                const element = document.getElementById(id);
                if (element) {
                    if (isValid) {
                        element.className = 'text-success';
                        element.innerHTML = '<i class="fas fa-check"></i> ' + element.textContent.replace('✓ ', '').replace('✗ ', '');
                    } else {
                        element.className = 'text-danger';
                        element.innerHTML = '<i class="fas fa-times"></i> ' + element.textContent.replace('✓ ', '').replace('✗ ', '');
                    }
                }
            }
            
            function checkFormValidity() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;
                const username = document.getElementById('reg_username').value;
                
                const isPasswordValid = password.length >= 8 &&
                    /[a-z]/.test(password) &&
                    /[A-Z]/.test(password) &&
                    /[0-9]/.test(password) &&
                    /[^a-zA-Z0-9]/.test(password);
                
                const isFormValid = username.length > 0 && 
                    isPasswordValid && 
                    confirm.length > 0 && 
                    password === confirm;
                
                registerBtn.disabled = !isFormValid;
            }
        });
    </script>
</body>
</html>