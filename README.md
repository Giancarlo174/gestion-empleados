# Sistema de Gestión de Empleados

## Descripción General

Este sistema proporciona una plataforma completa para la gestión de empleados, diseñada específicamente para el contexto panameño. Permite administrar información personal y laboral de empleados, gestionar departamentos y cargos, y proporciona diferentes niveles de acceso según el rol del usuario.

## Características Principales

### Administración de Empleados
- Registro de empleados con información completa (datos personales, contacto, ubicación, laboral)
- Gestión de documentos de identidad (cédula panameña)
- Visualización detallada de perfiles de empleados
- Edición y actualización de información
- Eliminación de empleados (con verificación de dependencias)
- Registro de auditoría para cambios realizados por empleados

### Gestión de Departamentos y Cargos
- Creación, edición y eliminación de departamentos
- Creación, edición y eliminación de cargos asociados a departamentos
- Estructuración jerárquica de la organización

### Sistema de Autenticación y Autorización
- Inicio de sesión seguro
- Roles diferenciados (administrador y empleado)
- Gestión de contraseñas (cambio, restablecimiento)
- Protección de rutas según el rol del usuario

### Interfaz de Usuario
- Diseño responsivo basado en Bootstrap
- Navegación intuitiva con sidebar
- Formularios con validación de datos
- Tablas con paginación y funciones de búsqueda/filtrado
- Modales de confirmación para acciones críticas

## Arquitectura Técnica

### Tecnologías Utilizadas
- **Backend**: PHP
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS, JavaScript
- **Frameworks/Bibliotecas**:
  - Bootstrap 5 (UI framework)
  - Font Awesome (iconos)
  - jQuery (manipulación DOM)

### Estructura del Proyecto
```
ds6/
├── assets/            # Recursos estáticos
│   ├── css/           # Hojas de estilo
│   ├── img/           # Imágenes y banderas de países
│   └── js/            # Scripts de JavaScript
├── config/            # Configuración
│   └── db.php         # Conexión a la base de datos
├── includes/          # Componentes reutilizables
│   ├── header.php     # Cabecera común
│   ├── footer.php     # Pie de página común
│   └── sidebar.php    # Barra lateral de navegación
├── modules/           # Módulos funcionales
│   ├── cargos/        # Gestión de cargos/posiciones
│   ├── departamentos/ # Gestión de departamentos
│   ├── empleados/     # Gestión de empleados
│   └── usuarios/      # Gestión de administradores
├── scripts/           # Scripts SQL y otros
├── index.php          # Página de inicio/login
└── dashboard.php      # Panel principal para administradores
```

### Estructura de la Base de Datos

El sistema utiliza las siguientes tablas principales:

1. **empleados**: Almacena la información completa de los empleados
2. **departamento**: Registra los departamentos de la organización
3. **cargo**: Almacena los cargos/posiciones disponibles
4. **usuarios**: Contiene las credenciales de administradores
5. **e_auditoria**: Registra cambios realizados por los empleados
6. **provincia/distrito/corregimiento**: Tablas para ubicaciones geográficas
7. **nacionalidad**: Lista de países y nacionalidades

## Flujos de Usuario

### Flujo para Administradores
1. Inicio de sesión como administrador
2. Acceso al dashboard con estadísticas generales
3. Gestión completa de empleados (agregar, editar, ver, eliminar)
4. Gestión de departamentos y cargos
5. Gestión de otros administradores

### Flujo para Empleados
1. Inicio de sesión como empleado
2. Visualización de perfil personal
3. Edición limitada de información personal
4. Cambio de contraseña

## Funcionalidades Detalladas

### Gestión de Empleados

#### Registro de Empleados (Administradores)
- Formulario completo con múltiples secciones:
  - Información personal (nombres, apellidos, género, estado civil, etc.)
  - Información de contacto
  - Dirección y ubicación
  - Información laboral
  - Credenciales de acceso
- Validación de datos:
  - Formato de cédula panameña
  - Verificación de mayoría de edad
  - Formatos de correo electrónico y números de teléfono
  - Contraseñas seguras

#### Edición de Perfil
- Empleados pueden editar información personal pero no laboral
- Administradores pueden editar toda la información
- Los cambios realizados por empleados se registran en auditoría

### Sistema de Ubicación Geográfica
- Selección en cascada:
  1. Provincia
  2. Distrito (dependiente de la provincia seleccionada)
  3. Corregimiento (dependiente del distrito seleccionado)

### Sistema de Departamentos y Cargos
- Relación jerárquica entre departamentos y cargos
- Cargos solo pueden existir dentro de un departamento
- Validación para prevenir eliminación de elementos con dependencias

## Características de Seguridad

### Autenticación
- Contraseñas almacenadas con hash seguro (password_hash)
- Protección de sesiones
- Prevención de acceso no autorizado a rutas protegidas

### Validación de Datos
- Sanitización de entradas de usuario
- Prevención de inyección SQL mediante consultas preparadas
- Validación de formatos y tipos de datos

### Auditoría
- Registro de cambios realizados por empleados a sus perfiles
- Almacenamiento de cambios en formato JSON para análisis

## Guía de Instalación

### Requisitos Previos
- PHP 7.0 o superior
- MySQL 5.6 o superior
- Servidor web (Apache recomendado)
- Extensiones PHP: mysqli, json

### Pasos de Instalación
1. Clonar o descargar el repositorio en el directorio web
2. Importar el esquema de base de datos desde `/scripts/schema.sql`
3. Importar datos iniciales:
   - `/scripts/nacionalidades.sql` para listado de países
   - `/scripts/departamentos_cargos.sql` para estructura organizacional inicial
4. Configurar la conexión a la base de datos en `/config/db.php`
5. Acceder al sistema mediante el navegador web

### Cuenta Inicial de Administrador
- Usuario: admin
- Contraseña: admin123
- *Se recomienda cambiar la contraseña después del primer inicio de sesión*

## Consideraciones de Desarrollo

### Validación de Cédula Panameña
El sistema implementa validación específica para el formato de cédula panameña:
- Prefijo (puede ser numérico o incluir letras como PE, E, N, etc.)
- Tomo (numérico)
- Asiento (numérico)

### Manejo de Datos Condicionales
- Apellido de casada solo se muestra para mujeres casadas que indican usarlo
- Campos laborales solo pueden ser editados por administradores
- Algunos campos son opcionales dependiendo del contexto

### Interfaz Responsiva
- Diseño adaptable a diferentes tamaños de pantalla
- Optimizado para escritorio y dispositivos móviles

---

© 2025 Sistema de Gestión de Empleados | Giancarlo Santillana & Daniel Nie
