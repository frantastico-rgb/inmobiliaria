<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Inmobiliaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 40px 30px 30px;
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 300;
            font-size: 1.8rem;
        }
        
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating input {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .form-check {
            margin: 20px 0;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0 20px;
            position: relative;
            color: #6c757d;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
        }
        
        .btn-demo {
            border: 2px solid #e9ecef;
            color: #6c757d;
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .btn-demo:hover {
            border-color: #667eea;
            color: #667eea;
            text-decoration: none;
        }
        
        .loading-spinner {
            display: none;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .login-header {
                padding: 30px 20px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-building me-2"></i>Inmobiliaria</h2>
                <p>Sistema de Gestión Inmobiliaria</p>
            </div>
            
            <div class="login-body">
                <!-- Alertas -->
                <div id="alert-container"></div>
                
                <!-- Formulario de Login -->
                <form id="loginForm" method="POST">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Usuario o Email" required>
                        <label for="username"><i class="fas fa-user me-2"></i>Usuario o Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Contraseña" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Mantener sesión iniciada
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <span class="login-text">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </span>
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin me-2"></i>Iniciando...
                        </div>
                    </button>
                </form>
                
                <!-- Accesos Demo -->
                <div class="divider">
                    <span>Accesos de Prueba</span>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <button class="btn-demo" onclick="fillDemoLogin('admin')">
                            <i class="fas fa-user-shield me-1"></i> Admin
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn-demo" onclick="fillDemoLogin('agente')">
                            <i class="fas fa-user-tie me-1"></i> Agente
                        </button>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-home me-1"></i>
                        <a href="../public/index.php" class="text-decoration-none">Volver al Catálogo</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datos de demo para pruebas
        const demoUsers = {
            admin: {
                username: 'admin',
                password: 'admin123'
            },
            agente: {
                username: 'agente1',
                password: 'agente123'
            }
        };
        
        // Rellenar datos de demo
        function fillDemoLogin(type) {
            console.log('Filling demo for:', type); // Debug
            const demo = demoUsers[type];
            if (demo) {
                document.getElementById('username').value = demo.username;
                document.getElementById('password').value = demo.password;
                
                // Efecto visual
                const inputs = document.querySelectorAll('.form-floating input');
                inputs.forEach(input => {
                    input.classList.add('border-success');
                    setTimeout(() => {
                        input.classList.remove('border-success');
                    }, 2000);
                });
                
                console.log('Demo data filled:', demo); // Debug
            } else {
                console.error('Demo user not found:', type); // Debug
            }
        }
        
        // Manejo del formulario
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('=== INICIO LOGIN ===');
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const loginText = submitBtn.querySelector('.login-text');
            const loadingSpinner = submitBtn.querySelector('.loading-spinner');
            
            // Debug - verificar datos del formulario
            console.log('Form data:', {
                username: formData.get('username'),
                password: formData.get('password') ? '***' : 'empty',
                remember_me: formData.get('remember_me')
            });
            
            // Validación básica
            const username = formData.get('username');
            const password = formData.get('password');
            
            if (!username || !password) {
                showAlert('danger', 'Por favor complete todos los campos');
                return;
            }
            
            // Mostrar loading
            loginText.style.display = 'none';
            loadingSpinner.style.display = 'block';
            submitBtn.disabled = true;
            
            try {
                console.log('Iniciando login...'); // Debug
                
                const response = await fetch('login_process.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status); // Debug
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const responseText = await response.text();
                console.log('Raw response:', responseText); // Debug
                
                let result;
                try {
                    result = JSON.parse(responseText);
                    console.log('Parsed result:', result); // Debug
                } catch (parseError) {
                    console.error('Error parsing JSON:', parseError);
                    console.error('Response text:', responseText);
                    throw new Error('Respuesta del servidor no válida');
                }
                
                if (result.success) {
                    console.log('Login exitoso, usuario:', result.user);
                    console.log('Redirección URL:', result.redirect);
                    showAlert('success', `¡Bienvenido ${result.user.nombre}!`);
                    // Delay para ver el mensaje
                    setTimeout(() => {
                        console.log('Ejecutando redirección a:', result.redirect);
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    console.error('Login falló:', result.error);
                    showAlert('danger', result.error);
                    resetButton();
                }
            } catch (error) {
                console.error('Error completo:', error); // Debug mejorado
                console.error('Error stack:', error.stack); // Stack trace
                showAlert('danger', 'Error de conexión: ' + error.message);
                resetButton();
            }
            
            function resetButton() {
                loginText.style.display = 'block';
                loadingSpinner.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
        
        // Mostrar alertas
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.innerHTML = alertHtml;
            
            // Auto-hide después de 5 segundos
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
        
        // Auto-focus en el primer input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Manejar parámetros URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const success = urlParams.get('success');
        
        if (error) {
            showAlert('danger', decodeURIComponent(error));
        }
        
        if (success) {
            showAlert('success', decodeURIComponent(success));
        }
    </script>
</body>
</html>