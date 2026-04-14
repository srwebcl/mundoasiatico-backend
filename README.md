# Mundo Asiático — Backend API

API REST Laravel 11 + Panel Filament v3 para el e-commerce de repuestos de vehículos chinos.

---

## Stack

| Capa | Tecnología |
|---|---|
| Framework | Laravel 11 (PHP 8.2+) |
| Base de Datos | MySQL 8+ |
| Autenticación API | Laravel Sanctum |
| Panel de Admin | Filament PHP v3 |
| Pagos | Transbank Webpay Plus SDK |
| Email | Laravel Mailables (SMTP) |
| Despliegue | cPanel / LAMP |

---

## Instalación en cPanel

### 1. Clonar el repositorio
```bash
git clone https://github.com/srwebcl/mundoasiatico-backend.git
cd mundoasiatico-backend
```

### 2. Instalar dependencias
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configurar el entorno
```bash
cp .env.example .env
php artisan key:generate
```
Edita `.env` con tus credenciales de MySQL, SMTP y Transbank.

### 4. Ejecutar migraciones
```bash
php artisan migrate --force
```

### 5. Crear el storage link
```bash
php artisan storage:link
```

### 6. Crear el usuario administrador de Filament
```bash
php artisan make:filament-user
```
> Elige el nombre, email y contraseña. Luego en la DB cambia su `role` a `admin`.

### 7. Optimizar para producción
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Estructura de la API

### Endpoints Públicos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/products` | Listado paginado (filtros: `category`, `brand`, `car_model`, `price_min`, `price_max`, `search`, `sort`) |
| GET | `/api/products/{slug}` | Detalle del producto |
| GET | `/api/categories` | Listado de categorías activas |
| GET | `/api/brands` | Listado de marcas con modelos de auto anidados |
| POST | `/api/auth/register` | Registro de cliente |
| POST | `/api/auth/login` | Login · devuelve token Sanctum + rol |
| POST | `/api/checkout/init` | Inicia el pago con Transbank |
| POST | `/api/checkout/return` | Webhook de confirmación Transbank |

### Endpoints Protegidos (requieren `Authorization: Bearer {token}`)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/auth/me` | Perfil del usuario autenticado |
| POST | `/api/auth/logout` | Cierra sesión |
| GET | `/api/orders/{id}` | Detalle de una orden |

---

## Panel de Administración

Accesible en: `https://tu-dominio.cl/admin`

| Sección | Funcionalidades |
|---|---|
| **Repuestos** | CRUD completo, selector de modelos compatibles, badge de stock |
| **Categorías** | CRUD, auto-slug, icono emoji |
| **Marcas** | CRUD, logo |
| **Modelos de Auto** | CRUD, filtro por marca, años de producción |
| **Usuarios** | Gestión de roles (customer / wholesale / admin) |
| **Pedidos** | Solo lectura · Únicamente se puede cambiar el estado |

---

## Transbank — Flujo de Pago

```
Frontend                    API                         Transbank
   │                         │                              │
   │  POST /checkout/init    │                              │
   │ {carrito, cliente}  ──► │  Crea Order (pending)       │
   │                         │  Transaction::create() ──►  │
   │ ◄── {webpay_url, token} │           ◄── {url, token}  │
   │                         │                              │
   │  Redirige al portal ────────────────────────────────►  │
   │  de pago Transbank       │              Usuario paga   │
   │                         │                              │
   │  POST /checkout/return ◄──────────────────────────────│
   │                         │  Transaction::commit()       │
   │                         │  Order → paid               │
   │                         │  Stock decrementado         │
   │                         │  Email enviado              │
   │ ◄── {status: "paid"}    │                              │
```

---

## Email Transaccional

Al cambiar una orden a `paid` (vía Transbank o manualmente desde Filament),
el `OrderObserver` dispara automáticamente `OrderPaidMailable` con:

- Número y fecha de la orden
- Código de autorización Transbank
- Tabla de productos comprados
- Método de envío y dirección (si aplica)
- Botón de contacto por WhatsApp

---

## Variables de Entorno Críticas

```env
APP_URL=https://api.mundoasiatico.cl
FRONTEND_URL=https://mundoasiatico.cl

DB_DATABASE=mundoasiatico_db
DB_USERNAME=mundoasiatico_user
DB_PASSWORD=***

MAIL_HOST=mail.mundoasiatico.cl
MAIL_USERNAME=pedidos@mundoasiatico.cl
MAIL_PASSWORD=***

TRANSBANK_ENV=production        # integration | production
TRANSBANK_COMMERCE_CODE=***
TRANSBANK_API_KEY=***
```
