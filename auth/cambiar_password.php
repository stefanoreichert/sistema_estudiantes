<?php
include '../config.php';
include '../auth.php';
verificarSesion();

$usuario = obtenerUsuarioActual();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordActual = trim($_POST['password_actual'] ?? '');
    $passwordNueva = trim($_POST['password_nueva'] ?? '');
    $passwordConfirmar = trim($_POST['password_confirmar'] ?? '');
    
    if ($passwordActual && $passwordNueva && $passwordConfirmar) {
        // Obtener contraseña actual del usuario
        $stmt = $mysqli->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuario['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $datosUsuario = $result->fetch_assoc();
        
        if ($datosUsuario) {
            // Verificar contraseña actual (cambiado: solo comparación directa)
            $passwordCorrecta = ($datosUsuario['password'] === $passwordActual);
            
            if (!$passwordCorrecta) {
                $error = 'La contraseña actual es incorrecta.';
            } elseif ($passwordNueva !== $passwordConfirmar) {
                $error = 'La nueva contraseña y su confirmación no coinciden.';
            } else {
                // Validar fortaleza de la nueva contraseña
                $erroresPassword = validarPasswordSegura($passwordNueva);
                if (!empty($erroresPassword)) {
                    $error = 'La nueva contraseña no cumple los requisitos:<br>• ' . implode('<br>• ', $erroresPassword);
                } else {
                    // Actualizar contraseña (cambiado: sin hashear)
                    $updateStmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $updateStmt->bind_param('si', $passwordNueva, $usuario['id']);
                    
                    if ($updateStmt->execute()) {
                        $success = 'Contraseña actualizada exitosamente.';
                        // Limpiar formulario
                        $_POST = [];
                    } else {
                        $error = 'Error al actualizar la contraseña.';
                    }
                }
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 500;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .password-strength {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .strength-bar {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #fd7e14; width: 50%; }
        .strength-good { background: #ffc107; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>

<?php include '../include/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Cambiar Contraseña</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card filter-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Actualizar Contraseña</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        </div>
                    <?php endif; ?>

                        <form method="POST" action="" id="passwordForm">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_actual" name="password_actual" placeholder="Contraseña Actual" required>
                                <label for="password_actual"><i class="fas fa-lock me-2"></i>Contraseña Actual</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_nueva" name="password_nueva" placeholder="Nueva Contraseña" required>
                                <label for="password_nueva"><i class="fas fa-key me-2"></i>Nueva Contraseña</label>
                                
                                <div class="password-strength" id="passwordStrength" style="display: none;">
                                    <small class="text-muted mb-2 d-block">Fortaleza de la contraseña:</small>
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthBar"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <ul class="list-unstyled small mb-0">
                                                <li id="length" class="text-muted"><i class="fas fa-times me-1"></i>8+ caracteres</li>
                                                <li id="lowercase" class="text-muted"><i class="fas fa-times me-1"></i>Minúscula</li>
                                                <li id="uppercase" class="text-muted"><i class="fas fa-times me-1"></i>Mayúscula</li>
                                            </ul>
                                        </div>
                                        <div class="col-6">
                                            <ul class="list-unstyled small mb-0">
                                                <li id="number" class="text-muted"><i class="fas fa-times me-1"></i>Número</li>
                                                <li id="special" class="text-muted"><i class="fas fa-times me-1"></i>Especial</li>
                                                <li id="strengthText" class="text-muted"><strong>Débil</strong></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" placeholder="Confirmar Nueva Contraseña" required>
                                <label for="password_confirmar"><i class="fas fa-check me-2"></i>Confirmar Nueva Contraseña</label>
                                <div class="invalid-feedback">Las contraseñas no coinciden</div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-filter" id="submitBtn" disabled>
                                    <i class="fas fa-save me-2"></i>Actualizar Contraseña
                                </button>
                                <a href="perfil.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                                </a>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPassword = document.getElementById('password_actual');
            const newPassword = document.getElementById('password_nueva');
            const confirmPassword = document.getElementById('password_confirmar');
            const strengthDiv = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const submitBtn = document.getElementById('submitBtn');
            
            let requirements = {
                length: false,
                lowercase: false,
                uppercase: false,
                number: false,
                special: false
            };
            
            newPassword.addEventListener('input', function() {
                const password = this.value;
                strengthDiv.style.display = password.length > 0 ? 'block' : 'none';
                
                // Verificar cada requisito
                requirements.length = password.length >= 8;
                requirements.lowercase = /[a-z]/.test(password);
                requirements.uppercase = /[A-Z]/.test(password);
                requirements.number = /[0-9]/.test(password);
                requirements.special = /[^a-zA-Z0-9]/.test(password);
                
                // Actualizar indicadores visuales
                updateRequirement('length', requirements.length);
                updateRequirement('lowercase', requirements.lowercase);
                updateRequirement('uppercase', requirements.uppercase);
                updateRequirement('number', requirements.number);
                updateRequirement('special', requirements.special);
                
                // Calcular puntuación de fortaleza
                const score = Object.values(requirements).filter(Boolean).length;
                updateStrengthBar(score);
                
                checkFormValidity();
            });
            
            confirmPassword.addEventListener('input', function() {
                const isMatch = newPassword.value === this.value;
                this.classList.toggle('is-invalid', !isMatch && this.value.length > 0);
                checkFormValidity();
            });
            
            currentPassword.addEventListener('input', checkFormValidity);
            
            function updateRequirement(id, isValid) {
                const element = document.getElementById(id);
                if (element) {
                    const icon = element.querySelector('i');
                    if (isValid) {
                        element.className = 'text-success';
                        icon.className = 'fas fa-check me-1';
                    } else {
                        element.className = 'text-muted';
                        icon.className = 'fas fa-times me-1';
                    }
                }
            }
            
            function updateStrengthBar(score) {
                strengthBar.className = 'strength-fill';
                let strengthClass = '';
                let strengthLabel = '';
                
                switch(score) {
                    case 0:
                    case 1:
                    case 2:
                        strengthClass = 'strength-weak';
                        strengthLabel = 'Débil';
                        break;
                    case 3:
                        strengthClass = 'strength-fair';
                        strengthLabel = 'Regular';
                        break;
                    case 4:
                        strengthClass = 'strength-good';
                        strengthLabel = 'Buena';
                        break;
                    case 5:
                        strengthClass = 'strength-strong';
                        strengthLabel = 'Fuerte';
                        break;
                }
                
                strengthBar.classList.add(strengthClass);
                strengthText.innerHTML = `<strong>${strengthLabel}</strong>`;
            }
            
            function checkFormValidity() {
                const hasCurrentPassword = currentPassword.value.length > 0;
                const allRequirementsMet = Object.values(requirements).every(Boolean);
                const passwordsMatch = newPassword.value === confirmPassword.value && confirmPassword.value.length > 0;
                
                const isFormValid = hasCurrentPassword && allRequirementsMet && passwordsMatch;
                submitBtn.disabled = !isFormValid;
            }
        });
    </script>
</body>
</html>