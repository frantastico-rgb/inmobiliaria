<?php
// Portal Público - Página de Contacto Administrativo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión y Gerencia - Casa Meta</title>
    <link rel="stylesheet" href="css/catalogo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .access-container {
            max-width: 800px;
            margin: 60px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .access-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
        }
        .access-title {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
        }
        .access-message {
            color: #7f8c8d;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .contact-email {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 30px;
            display: inline-block;
            border: 2px solid #e0e0e0;
            text-decoration: none; /* Evita el subrayado del enlace */
        }
        .btn-home {
            background: #27ae60;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-home:hover {
            background: #219150;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-home"></i> Casa Meta</h1>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Inicio</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="access-container">
            <div class="access-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <h1 class="access-title">Gestión y Gerencia</h1>
            
            <p class="access-message">
                Esta ventana es exclusiva para la gestión interna de la plataforma.
                <br><br>
                <strong>¿Eres propietario, agente o cliente?</strong><br>
                Tus solicitudes son muy importantes para nosotros. Déjanos tus datos y nos pondremos en contacto según tus requerimientos.
            </p>

            <a href="mailto:casametaverde@gmail.com" class="contact-email">
                <i class="fas fa-envelope"></i> casametaverde@gmail.com
            </a>

            <div>
                <a href="index.php" class="btn-home">
                    <i class="fas fa-arrow-left"></i> Volver al Catálogo
                </a>
            </div>

            <!-- Enlace de Acceso Interno (Funcional en Local, Oculto/Inaccesible en Producción) -->
            <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
                <p style="font-size: 13px; color: #bdc3c7; margin-bottom: 10px;">Acceso exclusivo para personal</p>
                <a href="../index.php" style="color: #95a5a6; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-lock"></i> Ingreso Administrativo
                </a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Casa Meta. Conectamos sueños.</p>
    </footer>
</body>
</html>