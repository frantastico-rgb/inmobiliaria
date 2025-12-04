# Sistema de Gestión Inmobiliaria

Sistema completo de gestión inmobiliaria con autenticación por roles, mapas interactivos, sistema de leads y gestión integral de propiedades.

## 🚀 Características Principales

- ✅ **Sistema de autenticación** con 4 niveles de roles
- ✅ **Gestión completa** de inmuebles, propietarios y clientes  
- ✅ **Mapas interactivos** con Leaflet.js
- ✅ **Sistema de favoritos** con localStorage
- ✅ **Captura de leads** con integración WhatsApp
- ✅ **Panel administrativo** con estadísticas
- ✅ **Responsive design** optimizado para móviles
- ✅ **Upload de multimedia** (imágenes y videos)

## 📋 Roles del Sistema

| Rol | Permisos | Dashboard |
|-----|----------|-----------|
| **Administrador** | Acceso total + gestión usuarios | Panel administrativo completo |
| **Secretaria** | Gestión clientes, contratos, visitas | Panel operacional |
| **Agente Senior** | Gestión inmuebles asignados + leads | Panel operacional |
| **Agente Junior** | Consulta inmuebles + captura leads | Panel básico |

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+, MySQL 8.0
- **Frontend**: Bootstrap 4.6, JavaScript ES6
- **Mapas**: Leaflet.js con OpenStreetMap
- **Servidor**: Apache 2.4 (XAMPP)

## 📖 Documentación

Toda la documentación técnica se encuentra en la carpeta `docs/`:

- **[Guía de Documentación](docs/INDEX.md)** - Índice de toda la documentación
- **[Documentación Técnica](docs/README.md)** - Arquitectura y especificaciones
- **[Tutorial de Instalación](docs/TUTORIAL.md)** - Configuración paso a paso
- **[Guía de Testing](docs/TESTING.md)** - Suite de pruebas
- **[Despliegue Docker](docs/DOCKER.md)** - Containerización y producción

## ⚡ Inicio Rápido

### Desarrollo Local (XAMPP)

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/frantastico-rgb/inmobiliaria.git
   cd inmobiliaria
   ```

2. **Configurar XAMPP**
   - Activar Apache + MySQL
   - Crear base de datos `inmobil`

3. **Configurar conexión**
   ```php
   // conexion.php
   $host = 'localhost';
   $dbname = 'inmobil';
   $username = 'root';
   $password = '';  // Vacío en XAMPP
   ```

4. **Acceder al sistema**
   ```
   http://localhost/INMOBILIARIA_1/
   ```

### Producción (Docker)

```bash
# Setup inicial
./docker/scripts/setup.sh

# Desplegar
docker-compose -f docker-compose.prod.yml up -d
```

## 🔐 Credenciales por Defecto

**Administrador:**
- Email: `admin@inmobiliaria.com`
- Password: `admin123`

## 📞 Funcionalidades Destacadas

### 🗺️ Sistema de Mapas
- Geolocalización automática
- Marcadores interactivos por inmueble
- Filtros dinámicos (tipo, precio, ciudad)
- Popups con información e imágenes

### ❤️ Sistema de Favoritos
- Persistencia en localStorage
- Gestión sin necesidad de login
- Interfaz intuitiva con contadores
- Sincronización con backend

### 📱 Captura de Leads
- Formularios optimizados
- Integración directa con WhatsApp
- Asignación automática de agentes
- Dashboard de seguimiento

### 📊 Panel Administrativo
- Estadísticas en tiempo real
- Gestión de usuarios y roles
- Reportes de inmuebles y leads
- Sistema de permisos granular

## 🔧 Configuración Avanzada

Para configuraciones específicas consultar:
- **Autenticación**: [docs/README.md#sistema-de-autenticación](docs/README.md#sistema-de-autenticación)
- **Base de Datos**: [docs/README.md#base-de-datos](docs/README.md#base-de-datos)
- **APIs**: [docs/README.md#sistema-de-mapas](docs/README.md#sistema-de-mapas)
- **Seguridad**: [docs/TESTING.md#security-testing](docs/TESTING.md#security-testing)

## 📈 Performance

- **Queries optimizadas** con índices apropiados
- **Compresión de imágenes** automática
- **Cache de consultas** frecuentes
- **Lazy loading** para multimedia
- **CDN ready** para assets estáticos

## 🛡️ Seguridad

- **Prepared statements** contra SQL injection
- **Validación de entrada** y sanitización
- **Upload seguro** de archivos
- **Gestión de sesiones** robusta
- **Headers de seguridad** configurados

## 📱 Responsive & Mobile

- **Design responsive** con Bootstrap
- **Touch interactions** optimizadas
- **Formularios móviles** mejorados
- **Mapas touch-friendly**
- **Performance móvil** optimizada

## 🔍 Testing

Suite completa de pruebas automatizadas:
```bash
# Ejecutar todos los tests
php tests/run_all_tests.php

# Tests específicos
php tests/auth_test.php
php tests/database_test.php
php tests/api_test.php
```

## 🚀 Contribuir

1. Fork del repositorio
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver [LICENSE](LICENSE) para detalles.

---

**Desarrollado para gestión inmobiliaria profesional**  
*Versión 1.0 - Diciembre 2024*