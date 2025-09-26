# DarienTask by Anthony Giannone

Aplicación de gestión de tareas desarrollada con **Laravel 10** y **Vue 3**, utilizando autenticación con **Laravel Sanctum**, estilos con **TailwindCSS**, y un frontend SPA servido con **Vite**.

Este proyecto forma parte de una prueba técnica e incluye backend API, frontend en Vue, autenticación, autorización, validaciones, filtrado/ordenado de tareas, y pruebas unitarias e integración.

---

## 🚀 Tecnologías utilizadas

- **Laravel**: 10.49.0  
- **PHP**: 8.1.33 (con OPcache)  
- **Base de datos**: MariaDB 10.4.27  
- **Node.js**: v20.19.5  
- **NPM**: (incluido con Node)  
- **Vue.js**: 3.5.22  
- **Vue Router**: 4.5.1  
- **Vite**: 5.4.20  
- **TailwindCSS**: 3.4.17  
- **Axios**: 1.6.4  

---

## 📦 Instalación y configuración

### 1. Clonar repositorio
```bash
git clone https://github.com/anthonyrgc/darientask.git
cd darientask
```

### 2. Configurar entorno
Copia el archivo `.env.example` a `.env`:
```bash
cp .env.example .env
```

Configura en `.env` tu base de datos MariaDB/MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=darientask
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Instalar dependencias de PHP
```bash
composer install
```

### 4. Instalar dependencias de Node
```bash
npm install
```

### 5. Generar key de la app
```bash
php artisan key:generate
```

### 6. Migrar y poblar la base de datos
```bash
php artisan migrate:fresh --seed
```

Esto crea las tablas y un usuario demo con tareas:

- **Email:** `demo@example.com`  
- **Password:** `12345678`  

---

## ▶️ Ejecución del proyecto

### Backend (Laravel + API)
```bash
php artisan serve
```
Por defecto se levanta en: **http://127.0.0.1:8000**

### Frontend (Vite + Vue)
En otra terminal:
```bash
npm run dev
```
Por defecto se levanta en: **http://127.0.0.1:5173**

👉 Cuando uses `php artisan serve`, accede a la app en **http://127.0.0.1:8000** (Laravel carga la SPA de Vue).

---

## 👤 Usuario Demo

Para entrar y probar directamente:
- **Email:** `demo@example.com`
- **Password:** `12345678`

Al iniciar sesión tendrás acceso al dashboard con tareas de prueba, filtros, creación/edición, etc.

---

## 🧪 Ejecución de pruebas

### Correr todos los tests
```bash
php artisan test
```

### Framework de testing
Se utilizan:
- PHPUnit integrado en Laravel
- Traits como `RefreshDatabase`
- Sanctum para autenticación en tests

Las pruebas cubren:
- **Unitarias de modelo**: relaciones y casts (`Task`, `User`).
- **Feature/API**: CRUD de tareas, filtros, ordenación, validaciones.
- **Autenticación**: acceso a rutas protegidas, scoping de usuario.
- **Validación**: errores 422 en campos requeridos e inválidos.

---

## 📂 Estructura principal

```
/app
  /Models
    User.php
    Task.php
  /Http/Controllers
    AuthController.php
    TaskController.php
/database
  /factories
    UserFactory.php
    TaskFactory.php
  /seeders
    DatabaseSeeder.php
    DemoUserSeeder.php
/resources/js
  /views
    Login.vue
    Register.vue
    Dashboard.vue
    Tasks.vue
    TaskForm.vue
    TaskShow.vue
  /components
    Navbar.vue
  /api.js
  /router.js
/tests
  /Unit
    TaskModelTest.php
  /Feature
    TaskApiTest.php
    AuthProtectionTest.php
    ValidationTest.php
```

---

## 📌 Notas adicionales

- El proyecto usa **Laravel Sanctum** para autenticación API.  
- Las rutas están definidas en `routes/api.php` (protegidas con `auth:sanctum`).  
- La SPA está montada en `resources/js/` y se sirve desde `resources/views/welcome.blade.php`.  
- En producción, basta compilar assets:
  ```bash
  npm run build
  ```

---

## 📌 Consideraciones especiales

- **Autenticación con Sanctum**  
  Este proyecto utiliza **Laravel Sanctum** para proteger las rutas de la API.  
  Asegúrate de que en `config/cors.php` y `config/sanctum.php` estén configurados los dominios correctos si despliegas en producción.  

- **Puertos por defecto**  
  - Backend Laravel: http://127.0.0.1:8000  
  - Frontend Vite: http://127.0.0.1:5173  

- **Usuario Demo**  
  El seeder crea un usuario para pruebas:  
    Email: demo@example.com
    Password: 12345678

    Con este usuario se puede entrar al sistema y probar todas las funcionalidades.

- **Dependencias clave de frontend**  
- `vue` 3.5.22  
- `vue-router` 4.5.1  
- `axios` 1.6.4  
- `tailwindcss` 3.4.17  
- `vite` 5.4.20  

- **Testing**  
Los tests usan la base de datos en memoria (`RefreshDatabase`).  
