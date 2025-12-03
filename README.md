# 📝 PHP Note API

API REST desarrollada en PHP puro (sin frameworks) para la gestión de notas de usuario con sistema de autenticación mediante tokens de sesión.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.0-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#️-configuración)
- [Base de Datos](#️-base-de-datos)
- [Uso de la API](#-uso-de-la-api)
- [Endpoints](#-endpoints)
- [Pruebas con Postman](#-pruebas-con-postman)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Seguridad](#-seguridad)
- [Solución de Problemas](#-solución-de-problemas)

## ✨ Características

- ✅ **Autenticación completa**: Registro, login y logout de usuarios
- ✅ **CRUD de notas**: Crear, leer, actualizar y eliminar notas por usuario
- ✅ **Sistema de tokens**: Gestión de sesiones con tokens personalizados (no JWT) con expiración configurable
- ✅ **Respuestas JSON**: Formato estandarizado de respuestas con códigos HTTP correctos
- ✅ **Validaciones**: Validación de datos en todas las operaciones
- ✅ **Seguridad**: Hash de contraseñas con bcrypt, sanitización de datos
- ✅ **Sin frameworks**: PHP puro
- ✅ **CORS habilitado**: Preparado para consumo desde aplicaciones frontend

## 🔧 Requisitos

- **PHP**: >= 8.0
- **MySQL**: >= 5.7
- **Apache**
- **XAMPP**: 8.2+
- **Postman**: Para pruebas de la API

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/Serzyp/php-note-api.git
cd php-note-api
```

### 2. Configurar XAMPP

1. **Descargar e instalar XAMPP** desde [https://www.apachefriends.org/](https://www.apachefriends.org/)

2. **Copiar el proyecto** a la carpeta de XAMPP:
   ```
    C:\xampp\htdocs\php-note-api\
   ```

3. **Iniciar los servicios** desde el Panel de Control de XAMPP:
   - Apache
   - MySQL

## ⚙️ Configuración

### 1. Variables de Entorno

Copiar el archivo de ejemplo y configurar las credenciales:

```bash
cp .env.example .env
```

Editar el archivo `.env`:

```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=notes_api
DB_USER=root
DB_PASS=

# Configuración de Sesiones
SESSION_EXPIRATION=3600
```


## 🗄️ Base de Datos

### Crear la Base de Datos

#### Usando MySQL Workbench

1. **Abrir MySQL Workbench**
2. **Conectar al servidor**:
   - Click en la conexión local (usualmente "Local instance MySQL")
   - Si pide contraseña, dejar en blanco (configuración por defecto de XAMPP)
3. **Abrir el script SQL**:
   - Menú: `File` → `Open SQL Script...`
   - Navegar a: `C:\xampp\htdocs\php-note-api\data\database.sql`
   - Click en "Open"
4. **Ejecutar el script**:
   - El script creará la base de datos `notes_api` y sus tablas automáticamente
5. **Verificar**:
   - En el panel izquierdo, click en "Schemas"
   - Click derecho → "Refresh All"
   - Expandir `notes_api` → `Tables`
   - Deberías ver: `users`, `sessions`, `notes`

### Estructura de la Base de Datos

El archivo SQL crea las siguientes tablas:

- **`users`**: Almacena información de usuarios registrados
  - `id`, `name`, `email`, `password`, `created_at`

- **`sessions`**: Gestiona tokens de sesión activos
  - `id`, `user_id`, `token`, `created_at`, `expires_at`

- **`notes`**: Almacena las notas de cada usuario
  - `id`, `user_id`, `title`, `content`, `created_at`, `updated_at`

## 📡 Uso de la API

### Base URL

```
http://localhost/php-note-api 
```

*o en mi caso*

```
http://localhost:8080/php-note-api
```

### Formato de Respuestas

Todas las respuestas siguen el siguiente formato JSON:

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... }
}
```

**Respuesta de Error:**
```json
{
  "success": false,
  "message": "Descripción del error",
  "data": null
}
```

## 🔗 Endpoints

### Autenticación

#### Registro de Usuario
```http
POST /auth/register
Content-Type: application/json

{
  "name": "Sergio",
  "email": "sergio@ejemplo.com",
  "password": "password123"
}
```

**Respuesta (201):**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Sergio",
      "email": "sergio@ejemplo.com",
      "created_at": "2025-12-03 10:00:00"
    },
    "token": "d9a32457a37b18b855bdec941c78705fb33987953ffbea343da9e4f6fda12891"
  }
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "sergio@ejemplo.com",
  "password": "password123"
}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Sergio",
      "email": "sergio@ejemplo.com"
    },
    "token": "d9a32457a37b18b855bdec941c78705fb33987953ffbea343da9e4f6fda12891"
  }
}
```

#### Obtener Usuario Actual
```http
GET /auth/me
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Usuario obtenido exitosamente",
  "data": {
    "id": 1,
    "name": "Sergio",
    "email": "sergio@ejemplo.com",
    "created_at": "2025-12-03 10:00:00"
  }
}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente",
  "data": null
}
```

### Notas

#### Crear Nota
```http
POST /notes
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Mi primera nota",
  "content": "Este es el contenido de mi nota..."
}
```

**Respuesta (201):**
```json
{
  "success": true,
  "message": "Nota creada exitosamente",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Mi primera nota",
    "content": "Este es el contenido de mi nota...",
    "created_at": "2025-12-03 10:30:00",
    "updated_at": "2025-12-03 10:30:00"
  }
}
```

#### Listar Todas las Notas del Usuario
```http
GET /notes
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Notas obtenidas exitosamente",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Mi primera nota",
      "content": "Este es el contenido de mi nota...",
      "created_at": "2025-12-03 10:30:00",
      "updated_at": "2025-12-03 10:30:00"
    },
    {
      "id": 2,
      "user_id": 1,
      "title": "Mi segunda nota",
      "content": "Otro contenido...",
      "created_at": "2025-12-03 11:00:00",
      "updated_at": "2025-12-03 11:00:00"
    }
  ]
}
```

#### Obtener Nota Específica
```http
GET /notes?id=1
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Nota obtenida exitosamente",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Mi primera nota",
    "content": "Este es el contenido de mi nota...",
    "created_at": "2025-12-03 10:30:00",
    "updated_at": "2025-12-03 10:30:00"
  }
}
```

#### Actualizar Nota
```http
PUT /notes?id=1
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Título actualizado",
  "content": "Contenido actualizado..."
}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Nota actualizada exitosamente",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Título actualizado",
    "content": "Contenido actualizado...",
    "created_at": "2025-12-03 10:30:00",
    "updated_at": "2025-12-03 12:00:00"
  }
}
```

#### Eliminar Nota
```http
DELETE /notes?id=1
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Nota eliminada exitosamente",
  "data": null
}
```

### Códigos HTTP Utilizados

| Código | Significado | Uso |
|--------|------------|-----|
| 200 | OK | Operación exitosa |
| 201 | Created | Recurso creado exitosamente |
| 400 | Bad Request | Error en la petición |
| 401 | Unauthorized | Token inválido o no proporcionado |
| 403 | Forbidden | Sin permisos para el recurso |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error del servidor |

## 📬 Pruebas con Postman

### 1. Importar la Colección

1. Abrir Postman
2. Click en "Import"
3. Seleccionar el archivo `postman/PHP-NOTE-API.postman_collection.json`
4. La colección incluye todos los endpoints configurados

### 2. Configurar Variables

La colección ya incluye las siguientes variables:

- `base_url`: `http://localhost:8080`
- `folder_name`: `php-note-api`

**Ajustar según tu configuración:**

Si usas XAMPP en puerto 80:
- `base_url` = `http://localhost`


### 3. Flujo de Pruebas Recomendado

1. **Registro**: Ejecutar `Auth > Register` para crear un nuevo usuario
2. **Copiar token** de la respuesta del registro o login
3. **Login**: Ejecutar `Auth > Login` con las credenciales
4. **Actualizar token**: El token se guarda automáticamente en las variables de colección
5. **Crear nota**: Ejecutar `Notes > Create Note`
6. **Listar notas**: Ejecutar `Notes > Get All Notes`
7. **Actualizar nota**: Ejecutar `Notes > Update Note` (cambiar el ID)
8. **Obtener nota**: Ejecutar `Notes > Get Note by ID`
9. **Eliminar nota**: Ejecutar `Notes > Delete Note`
10. **Logout**: Ejecutar `Auth > Logout` para cerrar sesión

## 📁 Estructura del Proyecto

```
php-note-api/
├── data/
│   └── database.sql             # Script de creación de BD
├── postman/
│   └── PHP-NOTE-API.postman_collection.json  # Colección Postman
├── public/
│   ├── .htaccess               # Configuración de Apache
│   └── index.php               # Punto de entrada principal
├── src/
│   ├── config/
│   │   ├── Database.php        # Gestión de conexión PDO
│   │   └── Env.php             # Carga de variables de entorno
│   ├── controllers/
│   │   ├── AuthController.php  # Lógica de autenticación
│   │   └── NotesController.php # Lógica de notas
│   ├── docs/
│   │   └── swagger.html        # Documentación visual
│   ├── helpers/
│   │   └── Utils.php           # Funciones auxiliares
│   ├── middleware/
│   │   └── AuthMiddleware.php  # Validación de tokens
│   ├── models/
│   │   ├── Note.php            # Modelo de notas
│   │   └── User.php            # Modelo de usuarios
│   ├── routes/
│   │   ├── auth.php            # Rutas de autenticación
│   │   └── notes.php           # Rutas de notas
│   └── services/
│       ├── Response.php        # Respuestas estandarizadas
│       └── TokenService.php    # Gestión de tokens
├── .env                        # Variables de entorno (no versionado)
├── .env.example                # Plantilla de variables
├── .gitignore
├── .htaccess                   # Redirección a /public
└── README.md
```

### Descripción de Componentes

#### Config
- **Database.php**: Singleton para gestión de conexión PDO con MySQL
- **Env.php**: Carga y parsea variables del archivo .env

#### Controllers
- **AuthController.php**: Maneja registro, login, logout y obtención de usuario
- **NotesController.php**: Maneja CRUD completo de notas

#### Middleware
- **AuthMiddleware.php**: Verifica tokens de autorización en cada petición protegida

#### Models
- **User.php**: Operaciones de base de datos relacionadas con usuarios
- **Note.php**: Operaciones de base de datos relacionadas con notas

#### Services
- **Response.php**: Estandariza respuestas JSON con códigos HTTP
- **TokenService.php**: Genera, valida y gestiona tokens de sesión

#### Helpers
- **Utils.php**: Funciones auxiliares utiles y reutilizables

#### Routes
- **auth.php**: Define rutas de autenticación (POST /register, POST /login, etc.)
- **notes.php**: Define rutas de notas (GET, POST, PUT, DELETE /notes)

## 🔒 Seguridad

### Implementaciones de seguridad

- ✅ **Contraseñas hasheadas** con `password_hash()` usando BCRYPT
- ✅ **Tokens únicos** generados con `random_bytes(32)` y `bin2hex()`
- ✅ **Expiración de tokens** configurable (default: 1 hora = 3600 segundos)
- ✅ **Sanitización de datos** en todas las entradas
- ✅ **Prepared Statements** para prevenir SQL Injection
- ✅ **Validación de emails** y formatos de datos
- ✅ **Verificación de permisos** en operaciones sobre notas (un usuario solo puede acceder a sus notas)
- ✅ **CORS configurado** para permitir peticiones externas
- ✅ **Limpieza automática** de tokens expirados


## 🛠️ Tecnologías utilizadas

- **PHP 8+**: Lenguaje de programación principal
- **MySQL**: Base de datos relacional
- **PDO**: Capa de abstracción de base de datos
- **Apache**: Servidor web
- **Postman**: Testing de API

## 🐛 Soluciones de algunos problemas

### Error: "Base de datos no encontrada"
**Solución:**
- Verificar que MySQL está ejecutándose en XAMPP
- Importar el archivo `data/database.sql` en phpMyAdmin
- Revisar credenciales en el archivo `.env`

### Error: "404 Not Found"
**Solución:**
- Verificar que `mod_rewrite` está habilitado en Apache
- Revisar que existen los archivos `.htaccess` en raíz y en `/public`
- Reiniciar Apache desde el panel de XAMPP
- Verificar que la ruta del proyecto es correcta

### Error: "Token inválido o expirado"
**Solución:**
- El token puede haber expirado (1 hora por defecto)
- Realizar login nuevamente para obtener un nuevo token
- Verificar que el header `Authorization: Bearer {token}` está presente
- Copiar el token completo sin espacios adicionales

### Error: "Could not connect to database"
**Solución:**
- Verificar que MySQL está iniciado en XAMPP
- Revisar las credenciales en `.env` (DB_HOST, DB_NAME, DB_USER, DB_PASS)
- Verificar que la base de datos `notes_api` existe

### Error: "Permission denied" al acceder a una nota
**Solución:**
- Verificar que estás usando el token del usuario que creó la nota
- Un usuario solo puede ver/editar/eliminar sus propias notas

### Error: "Email already exists"
**Solución:**
- El email ya está registrado en la base de datos
- Usar un email diferente o realizar login con el existente


## 🕹️ Mejoras que yo implementaria
- Bateria de pruebas
- Carpeta de frontent para tener una parte visual e interactiva
- Documentacion de la API realizada mediante Swagger


## 👨‍💻 Autor

**Sergio Sebastian Lacalle**
- GitHub: [@Serzyp](https://github.com/Serzyp)

