# 🚀 Guía de Despliegue en Coolify (Nixpacks vs Docker Compose)

Esta guía detalla los pasos para desplegar el **Sistema de Validación de Certificados Notariales** en tu instancia de **Coolify**. Dado que la aplicación combina **Laravel 13** con un validador en **Python 3 (pyHanko)** y requiere un **Queue Worker** para procesar tareas en segundo plano, tienes dos excelentes formas de desplegarla.

Hemos preparado y creado todos los archivos necesarios en tu repositorio para soportar ambas opciones:
*   Para **Nixpacks**: [nixpacks.toml](file:///home/svonccy/projects/certificate-validator/nixpacks.toml) y [requirements.txt](file:///home/svonccy/projects/certificate-validator/requirements.txt)
*   Para **Docker Compose**: [docker-compose.yml](file:///home/svonccy/projects/certificate-validator/docker-compose.yml), [Dockerfile](file:///home/svonccy/projects/certificate-validator/Dockerfile) y [.dockerignore](file:///home/svonccy/projects/certificate-validator/.dockerignore)

---

## ⚡ Opción A: Despliegue Nativo con Nixpacks (Recomendado por Simplicidad)

Nixpacks es el constructor por defecto de Coolify. Usar esta opción te permite usar la infraestructura nativa de Coolify sin configurar Dockerfiles manuales, delegando la base de datos a un recurso gestionado de Coolify.

### Paso 1: Configurar el Repositorio en Coolify
1. En Coolify, haz clic en **+ Add Resource** > **Public/Private Repository**.
2. Selecciona tu repositorio de Git y la rama de producción (ej: `main`).
3. Coolify detectará el proyecto y seleccionará **Nixpacks** de forma automática.
4. Nixpacks leerá nuestro archivo [nixpacks.toml](file:///home/svonccy/projects/certificate-validator/nixpacks.toml) y preparará los entornos de PHP 8.3, Node.js y Python 3, e instalará las dependencias de Python descritas en [requirements.txt](file:///home/svonccy/projects/certificate-validator/requirements.txt).

### Paso 2: Crear la Base de Datos en Coolify
1. Ve al panel de Coolify de tu proyecto, haz clic en **+ Add Resource** y elige **Databases** > **MySQL**.
2. Asígnale un nombre (ej: `certificados-db`) y completa la creación.
3. Coolify te proveerá los datos de conexión internos (host, puerto, usuario, contraseña).

### Paso 3: Configurar las Variables de Entorno
En la pestaña **Environment Variables** de tu aplicación en Coolify, vincula la base de datos y define el entorno:
*   `APP_ENV`: `production`
*   `APP_DEBUG`: `false`
*   `APP_KEY`: *Genera uno ejecutando `php artisan key:generate --show` en local.*
*   `DB_CONNECTION`: `mysql`
*   `DB_HOST`: *El host interno proporcionado por tu base de datos Coolify (ej: `mysql.cnsm` o dirección interna).*
*   `DB_PORT`: `3306`
*   `DB_DATABASE`: *El nombre configurado para la base de datos.*
*   `DB_USERNAME`: *El usuario configurado.*
*   `DB_PASSWORD`: *La contraseña de la base de datos.*
*   `QUEUE_CONNECTION`: `database`
*   `SESSION_DRIVER`: `database`
*   `CACHE_STORE`: `database`

### Paso 4: Levantar el Queue Worker desde Coolify
Dado que la validación criptográfica de firmas es un proceso pesado, Laravel utiliza colas en segundo plano. En lugar de levantar un contenedor de Docker separado manualmente:
1. En la configuración de tu aplicación en Coolify, ve al menú **Workers** (o **Additional Processes**).
2. Añade un nuevo Worker y configúralo con el comando:
   ```bash
   php artisan queue:work --verbose --tries=3 --timeout=90
   ```
3. Inícialo. Coolify levantará un micro-contenedor paralelo que comparte el mismo volumen y base de datos, dedicado exclusivamente a validar PDFs en segundo plano de forma óptima.

---

## 📦 Opción B: Despliegue con Docker Compose (Contenedorizado y Autónomo)

Si prefieres empaquetar toda la aplicación, el worker y la base de datos en un solo conjunto de contenedores orquestados de forma tradicional.

### Paso 1: Configurar Docker Compose en Coolify
1. En Coolify, haz clic en **+ Add Resource** y selecciona **Docker Compose**.
2. Selecciona tu repositorio Git. Coolify leerá el archivo [docker-compose.yml](file:///home/svonccy/projects/certificate-validator/docker-compose.yml) automáticamente.
3. En la pestaña de **Environment Variables**, define las variables requeridas por el archivo compose (incluyendo `DB_PASSWORD` y `APP_KEY`).

### Paso 2: Persistencia
Coolify montará automáticamente los volúmenes configurados:
*   `app-storage`: Para que los certificados PDFs cargados y firmas de confianza no se borren en cada despliegue.
*   `db-data`: Para persistir los datos de la base de datos MySQL.

---

## 🛠️ Comandos Post-Despliegue (Comunes para ambas opciones)

La primera vez que despliegues, accede a la sección **Terminal** del contenedor de la aplicación desde Coolify y ejecuta:

```bash
# Crear las tablas necesarias en la base de datos
php artisan migrate --force

# Crear el enlace simbólico del storage
php artisan storage:link

# Optimizar el rendimiento en producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

---

## ⚡ Despliegues Automáticos (Auto Deploy)
Una vez configurado el despliegue con cualquiera de las dos opciones:
*   **Si usas GitHub App:** Coolify detectará automáticamente cualquier `git push` a la rama y redesplegará la aplicación en caliente (zero-downtime).
*   **Si usas clave SSH:** Ve al menú **Advanced** de tu aplicación en Coolify, activa la opción **Auto Deploy**, define un webhook secret y cópialo en los ajustes de webhooks de tu repositorio de Git.
