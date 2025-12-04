# Mika - Asistente Financiero Chat-First

Mika es una aplicación de gestión financiera "invisible" para PyMEs con interfaz chat-first. Los usuarios interactúan mediante lenguaje natural para registrar gastos, ingresos y consultar su situación financiera.

## Stack Tecnológico

- **Backend**: PHP 8.3, Laravel 11
- **Base de Datos**: PostgreSQL 16
- **Frontend**: Livewire 3, TailwindCSS, AlpineJS
- **Infraestructura**: Docker, FrankenPHP
- **Puerto**: 8040

## Requisitos

- Docker y Docker Compose
- Git

## Instalación Rápida (Docker)

```bash
# Clonar
git clone https://github.com/SimonLexRS/Mika.git
cd Mika

# Configurar
cp .env.example .env

# Levantar (puerto 8040)
docker-compose up -d

# Acceder
open http://localhost:8040
```

## Instalación Desarrollo

```bash
# Clonar
git clone https://github.com/SimonLexRS/Mika.git
cd Mika

# Configurar
cp .env.example .env

# Levantar desarrollo
docker-compose -f docker-compose.dev.yml up -d

# Instalar dependencias
docker exec -it mika-app-dev sh
composer install
php artisan key:generate
npm install && npm run build
php artisan migrate

# Acceder
open http://localhost:8040
```

## Deploy en Dokploy

### Opción 1: Docker Compose

1. Crear proyecto en Dokploy
2. Fuente: Git → `https://github.com/SimonLexRS/Mika.git`
3. Build Type: **Docker Compose**
4. Puerto expuesto: **8040**

### Opción 2: Dockerfile

1. Crear proyecto en Dokploy
2. Fuente: Git → `https://github.com/SimonLexRS/Mika.git`
3. Build Type: **Dockerfile**
4. Puerto expuesto: **8040**
5. Crear servicio PostgreSQL separado

### Variables de Entorno (Dokploy)

```env
APP_NAME=Mika
APP_ENV=production
APP_KEY=base64:GENERAR_CON_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=nombre-servicio-postgres
DB_PORT=5432
DB_DATABASE=mika
DB_USERNAME=mika
DB_PASSWORD=contraseña-segura

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Comandos Útiles

```bash
# Desarrollo
docker-compose -f docker-compose.dev.yml up -d
docker exec -it mika-app-dev npm run dev

# Producción
docker-compose up -d

# Logs
docker-compose logs -f app

# Migraciones
docker exec -it mika-app php artisan migrate
```

## Estructura del Proyecto

```
app/
├── Enums/              # TransactionType, MessageType, etc.
├── Livewire/           # Componentes del chat
├── Models/             # User, Transaction, Conversation, Message
├── Providers/          # ChatBrainServiceProvider
└── Services/ChatBrain/ # Motor de procesamiento de chat
    ├── Extractors/     # AmountExtractor, CategoryExtractor
    ├── Intents/        # RegisterExpense, QueryBalance, etc.
    └── Responses/      # TextResponse, CardResponse

resources/views/
├── auth/               # Login, Register
├── components/         # bottom-nav, mika-avatar
├── layouts/            # app.blade.php
├── livewire/           # Componentes Livewire
└── pages/              # chat, scanner, profile
```

## Funcionalidades

### Chat Natural
- "Gasté $500 en comida" → Registra gasto
- "Me pagaron 5 mil" → Registra ingreso
- "¿Cómo voy este mes?" → Muestra resumen
- "Mis últimos gastos" → Lista transacciones

### Categorías Automáticas
- Comida, Transporte, Servicios, Compras, Salud, Entretenimiento

### UI Mobile-First
- Tema oscuro (#121212)
- Acentos violeta (#5D3FD3)
- Navegación inferior
- Respuestas rápidas

## Licencia

MIT
