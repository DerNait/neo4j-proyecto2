# Proyecto Full-Stack: Laravel + Vue 3 + Inertia.js + Neo4j AuraDB

Stack tecnológico:
- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Vue 3 (Composition API) via Inertia.js
- **Base de datos**: Neo4j AuraDB (cloud)
- **Entorno**: Docker (PHP-FPM, Nginx, Node 20)

---

## Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (o Docker Engine + Docker Compose v2 en Linux)
- Git

---

## 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO> mi-proyecto
cd mi-proyecto
```

---

## 2. Crear un proyecto Laravel nuevo dentro del directorio

> Si el repositorio ya contiene el código de Laravel, sáltate este paso.

```bash
# Crea el proyecto Laravel en el directorio actual
docker run --rm -v "$(pwd):/app" composer create-project laravel/laravel . --prefer-dist
```

---

## 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Abre `.env` y completa las credenciales de **Neo4j AuraDB**:

```
NEO4J_URI=neo4j+s://xxxxxxxx.databases.neo4j.io
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=tu-contraseña-aqui
```

Puedes obtener estos datos desde la consola de [Neo4j AuraDB](https://console.neo4j.io/).

---

## 4. Levantar los contenedores Docker

```bash
docker-compose up -d --build
```

Esto construirá la imagen PHP y levantará los tres servicios:
- `app` — PHP-FPM en el puerto interno 9000
- `web` — Nginx en [http://localhost:8080](http://localhost:8080)
- `node` — Node.js 20 (modo standby)

Verifica que los contenedores estén corriendo:

```bash
docker-compose ps
```

---

## 5. Inicializar el proyecto (una sola vez)

Entra al contenedor `app` y ejecuta el script de inicialización:

```bash
docker-compose exec app bash init.sh
```

Este script realiza automáticamente:
1. `composer install`
2. Instalación de `laudis/neo4j-php-client` y `laravel/breeze`
3. `php artisan key:generate`
4. `php artisan breeze:install vue --inertia`

---

## 6. Registrar el Neo4jServiceProvider

En **Laravel 11**, agrega el provider en `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Neo4jServiceProvider::class,  // ← agregar esta línea
];
```

En **Laravel 10**, agrégalo en el array `providers` de `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\Neo4jServiceProvider::class,
],
```

---

## 7. Instalar dependencias de Node y compilar assets

### Para producción (una sola compilación):

```bash
docker-compose exec node sh -c "npm install && npm run build"
```

### Para desarrollo con Hot Module Replacement (HMR):

```bash
docker-compose exec node sh -c "npm install && npm run dev"
```

> Con `npm run dev`, Vite levanta su propio servidor en el puerto 5173. Accede igualmente por [http://localhost:8080](http://localhost:8080) (Nginx sirve el `index.php` que carga el bundle de Vite).

---

## 8. Ejecutar migraciones (base de datos relacional)

Si el proyecto usa SQLite u otra base de datos relacional para tablas de Breeze (usuarios, sesiones, etc.):

```bash
docker-compose exec app php artisan migrate
```

> Las migraciones de Neo4j no aplican en el sentido tradicional; los nodos y relaciones se crean directamente con Cypher.

---

## 9. Verificar la conexión a Neo4j

Abre Tinker dentro del contenedor:

```bash
docker-compose exec app php artisan tinker
```

Ejecuta:

```php
$neo4j = app(\App\Services\Neo4jClient::class);
$result = $neo4j->run('RETURN "Conexión exitosa" AS mensaje');
echo $result->first()->get('mensaje');
```

Si ves `Conexión exitosa`, la configuración es correcta.

---

## 10. Usar Neo4jClient en un controlador

```php
<?php

namespace App\Http\Controllers;

use App\Services\Neo4jClient;

class EjemploController extends Controller
{
    public function __construct(private Neo4jClient $neo4j) {}

    public function index()
    {
        $resultados = $this->neo4j->run(
            'MATCH (n:Persona) RETURN n.nombre AS nombre LIMIT 10'
        );

        $personas = $resultados->map(fn($r) => $r->get('nombre'))->toArray();

        return inertia('Ejemplo/Index', compact('personas'));
    }
}
```

---

## Estructura de archivos del proyecto

```
proyecto/
├── app/
│   ├── Providers/
│   │   └── Neo4jServiceProvider.php   ← registra el cliente Neo4j
│   └── Services/
│       └── Neo4jClient.php            ← cliente Cypher inyectable
├── config/
│   └── neo4j.php                      ← configuración de Neo4j
├── docker/
│   ├── nginx/
│   │   └── nginx.conf
│   └── php/
│       └── Dockerfile
├── .env.example
├── docker-compose.yml
├── init.sh
└── README.md
```

---

## Troubleshooting

| Problema | Solución |
|---|---|
| `docker-compose up` falla al construir | Verifica que Docker esté corriendo y tengas conexión a internet |
| `php artisan key:generate` da error | Asegúrate de que `.env` existe (`cp .env.example .env`) |
| Error de conexión a Neo4j | Verifica que `NEO4J_URI`, `NEO4J_USERNAME` y `NEO4J_PASSWORD` sean correctos en `.env` |
| Vite no encuentra assets | Ejecuta `npm install && npm run build` dentro del contenedor `node` |
| Permisos en `storage/` | `docker-compose exec app chmod -R 775 storage bootstrap/cache` |

---

## Comandos útiles

```bash
# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs solo de PHP
docker-compose logs -f app

# Abrir shell en el contenedor PHP
docker-compose exec app bash

# Abrir shell en el contenedor Node
docker-compose exec node sh

# Detener todos los contenedores
docker-compose down

# Detener y eliminar volúmenes
docker-compose down -v
```
