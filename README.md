# Task API Servicel

API REST para la gestión de proyectos y tareas, construida con PHP nativo, MySQL y Docker.

## 🚀 Requisitos Previos

- **Docker** y **Docker Compose** instalados.
- Un cliente para probar la API (Postman, Insomnia o Thunder Client).

## 🛠️ Instalación y Configuración

Sigue estos pasos para levantar el entorno de desarrollo:

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd task-api-servicel
```

### 2. Configurar variables de entorno
Crea un archivo `.env` dentro de la carpeta `src/` basado en el archivo `.env.example` (si existe) o con el siguiente contenido:

```env
DB_HOST=mysql
DB_NAME=project_management
DB_USER=api_user
DB_PASS=api_password_123

JWT_SECRET=tu_secreto_super_seguro_aca
JWT_EXPIRE=3600

APP_ENV=development
APP_DEBUG=true
ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
```

> **Nota:** El `DB_HOST` debe ser `mysql` para que coincida con el nombre del servicio en Docker.

### 3. Preparar la Base de Datos
El contenedor de MySQL está configurado para inicializarse con un archivo llamado `init.sql`. Como tienes el archivo como `database.sql`, renómbralo o crea un enlace:

```bash
cp database.sql init.sql
```

### 4. Levantar los contenedores
Ejecuta el siguiente comando en la raíz del proyecto:

```bash
docker-compose up -d --build
```

Esto levantará:
- **API:** [http://localhost:8000](http://localhost:8000)
- **phpMyAdmin:** [http://localhost:8080](http://localhost:8080) (Usuario: `root`, Pass: `root_password_123`)
- **MySQL:** Puerto `3306`

## 🛣️ Rutas de la API

### Autenticación
- `POST /login` - Iniciar sesión y obtener el token JWT.
- `POST /change-password` - Cambiar contraseña (Requiere Token).

### Proyectos (Requieren Token)
- `GET /projects` - Listar todos los proyectos.
- `POST /projects` - Crear un nuevo proyecto.
- `GET /projects/{slug}` - Obtener detalles de un proyecto por su slug.

### Tareas (Requieren Token)
- `GET /projects/{projectId}/tasks` - Listar tareas de un proyecto.
- `POST /tasks/{projectId}` - Crear tarea en un proyecto.
- `PUT /tasks/{taskId}` - Actualizar tarea completa.
- `PATCH /tasks/{taskId}/status` - Actualizar solo el estado.
- `PATCH /tasks/{taskId}/worked-hours` - Actualizar horas trabajadas.
- `DELETE /tasks/{taskId}` - Eliminar una tarea.

## 📁 Estructura del Proyecto

```text
src/
├── controllers/    # Lógica de los endpoints
├── models/         # Interacción con la base de datos (POPO/DAO)
├── helpers/        # Utilidades (JWT, Respuestas, EnvLoader, etc.)
├── database/       # Clase de conexión PDO
├── index.php       # Punto de entrada y Router
└── Router.php      # Sistema de rutas sencillo
```

## 🧪 Pruebas
Puedes usar el script incluido para realizar pruebas rápidas:
```bash
./test-api.sh
```

---
Desarrollado para **Servicel Perú**.
