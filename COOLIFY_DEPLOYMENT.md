# 🚀 Guía de Despliegue en Coolify

Esta guía detalla los pasos para desplegar el **Sistema de Validación de Certificados Notariales** en tu instancia de **Coolify**. Dado que la aplicación combina **Laravel 13** con un validador en **Python 3 (pyHanko)** y requiere un **Queue Worker** para tareas en segundo plano, la compilación de recursos puede ser pesada para servidores con recursos limitados (VPS de 1GB o 2GB de RAM).

Para darte la mayor flexibilidad, hemos configurado y preparado los archivos necesarios para tres opciones de despliegue:
*   **Nixpacks (Construcción local):** [nixpacks.toml](file:///home/svonccy/projects/certificate-validator/nixpacks.toml) y [requirements.txt](file:///home/svonccy/projects/certificate-validator/requirements.txt)
*   **Docker Compose (Construcción local):** [docker-compose.yml](file:///home/svonccy/projects/certificate-validator/docker-compose.yml), [Dockerfile](file:///home/svonccy/projects/certificate-validator/Dockerfile) y [.dockerignore](file:///home/svonccy/projects/certificate-validator/.dockerignore)
*   **GitHub Actions + GHCR (Construcción en la nube - Recomendado para VPS pequeños):** [.github/workflows/deploy.yml](file:///home/svonccy/projects/certificate-validator/.github/workflows/deploy.yml)

---

## ⚡ Opción A: Despliegue con GitHub Actions + GHCR (Recomendado para VPS de 1GB/2GB)

Esta opción delega todo el trabajo pesado de compilación (Vite, Composer, Python) a los servidores de GitHub. Tu VPS solo descargará la imagen Docker ya compilada y lista para correr, reduciendo el tiempo de despliegue en el VPS a menos de 2 minutos y evitando caídos por falta de memoria.

### Paso 1: Configurar GitHub Secrets y Permisos
Para que el pipeline de GitHub Actions pueda publicar la imagen y alertar a tu instancia de Coolify:
1. En tu panel de Coolify, ve a **Settings** > **Configuration** > **Advanced** y asegúrate de activar **API Access**.
2. Ve a **Keys & Tokens** > **API Tokens** > **New Token**, crea un token con permisos de **Deploy** y guárdalo.
3. Crea un nuevo recurso en Coolify haciendo clic en **+ Add Resource** y, en la sección **Docker Based**, selecciona **Docker Image**.
4. Ve a la pestaña **Webhooks** de este nuevo recurso de imagen y copia la URL completa del **Deploy Webhook**.
5. En tu repositorio de GitHub, ve a **Settings** > **Secrets and variables** > **Actions** y añade dos secretos:
   *   **`COOLIFY_WEBHOOK`**: Pega la URL del Deploy Webhook que copiaste.
   *   **`COOLIFY_TOKEN`**: Pega el API Token con permisos de Deploy creado en el paso 2.
6. En la pestaña de configuración del repositorio de GitHub, ve a **Settings** > **Actions** > **General** y en la sección **Workflow permissions** selecciona **Read and write permissions** (y guarda los cambios). Esto permite que el workflow publique imágenes en GHCR.

### Paso 2: Configurar el Recurso "Docker Image" en Coolify y Autorización
En lugar de apuntar Coolify a tu repositorio Git para compilar, le diremos que consuma la imagen de GitHub Container Registry (GHCR):
1. En el campo de imagen del recurso **Docker Image** creado en el Paso 1, introduce la ruta de tu imagen en GHCR (debe estar en minúsculas):
   ```text
   ghcr.io/svonccy/certificate-validator:latest
   ```
   *(Reemplaza `svonccy/certificate-validator` por la ruta real de tu repositorio en minúsculas).*
2. En la configuración del recurso, asegúrate de mapear el puerto de la aplicación (FrankenPHP usa el puerto `80` por defecto).

> [!IMPORTANT]
> **Gestión de la Privacidad de la Imagen (¡Muy importante para evitar error de "Unauthorized"!):**
>
> Si tu repositorio de GitHub es privado, la imagen compilada en GHCR se creará como **privada** por defecto. Para que Coolify pueda descargarla (pull), tienes dos opciones:
>
> *   **Opción 1: Hacer la imagen pública (Recomendado por simplicidad):** 
>     Puedes hacer que la imagen compilada sea pública sin comprometer tu código fuente (que permanecerá privado). Para ello:
>     1. Ve a tu perfil de GitHub y entra a la pestaña **Packages**.
>     2. Selecciona `certificate-validator`.
>     3. Ve a **Package Settings**.
>     4. En la sección *Danger Zone*, cambia la visibilidad a **Public**.
>     Con esto, Coolify podrá descargar tu imagen sin necesidad de contraseñas.
>
> *   **Opción 2: Autenticar tu servidor VPS con GHCR (Si deseas mantener la imagen privada):**
>     Si no deseas hacer pública la imagen, debes autorizar al Docker de tu servidor para que lea de GHCR:
>     1. Accede por SSH a tu servidor VPS como usuario **root** (o el usuario con el que corre Docker).
>     2. Ejecuta el comando de inicio de sesión:
>        ```bash
>        docker login ghcr.io -u TU_USUARIO_GITHUB
>        ```
>     3. Cuando te pida la contraseña, introduce un **Personal Access Token (PAT)** de GitHub con el permiso de `read:packages`. Coolify detectará automáticamente esta credencial en el archivo local `/root/.docker/config.json` para hacer el despliegue.

### Paso 3: Configurar el Worker de Colas
1. En la configuración de tu aplicación de imagen Docker en Coolify, ve al menú **Workers** (o **Additional Processes**).
2. Añade un nuevo Worker y configúralo con el comando:
   ```bash
   php artisan queue:work --verbose --tries=3 --timeout=90
   ```
3. Inícialo para que procese las firmas en segundo plano.

---

## ⚙️ Opción B: Despliegue Nativo con Nixpacks (Construcción local)

Nixpacks es el constructor por defecto de Coolify. Compila todo en tu servidor VPS.

### Paso 1: Agregar el Recurso Git
1. En Coolify, haz clic en **+ Add Resource**.
2. En la sección **Git Based**, selecciona la opción adecuada según la privacidad de tu repositorio:
   *   **Private Repository (with GitHub App):** Selecciona esta opción si tu repositorio es privado y tienes vinculada la GitHub App de Coolify (Recomendado).
   *   **Private Repository (with Deploy Key):** Selecciona esta opción si prefieres autenticarte con una SSH Deploy Key.
   *   **Public Repository:** Selecciona esta opción si tu repositorio es público.
3. Elige tu repositorio y la rama (ej: `main`). Coolify detectará el proyecto y seleccionará **Nixpacks** de forma automática leyendo el archivo [nixpacks.toml](file:///home/svonccy/projects/certificate-validator/nixpacks.toml).

### Paso 2: Crear el Worker de Colas
1. Ve al menú **Workers** (o **Additional Processes**) dentro de la configuración de tu app en Coolify.
2. Añade un Worker con el comando:
   ```bash
   php artisan queue:work --verbose --tries=3 --timeout=90
   ```

---

## 📦 Opción C: Despliegue con Docker Compose (Construcción local)

Orquesta la aplicación y la base de datos de manera conjunta. Compila todo en tu servidor VPS.

### Paso 1: Configurar Docker Compose
1. En Coolify, haz clic en **+ Add Resource** y, en la sección **Docker Based**, selecciona **Docker Compose**.
2. Selecciona tu repositorio. Coolify leerá el archivo [docker-compose.yml](file:///home/svonccy/projects/certificate-validator/docker-compose.yml) y levantará los servicios (`app`, `worker` y `db`).

---

## 🔒 Variables de Entorno Obligatorias (Comunes)

En la pestaña **Environment Variables** de tu aplicación en Coolify, debes configurar:
*   `APP_ENV`: `production`
*   `APP_DEBUG`: `false`
*   `APP_KEY`: *Genera una llave ejecutando `php artisan key:generate --show` en local.*
*   `DB_CONNECTION`: `mysql`
*   `DB_HOST`: *El host de tu base de datos en Coolify.*
*   `DB_PORT`: `3306`
*   `DB_DATABASE`: `certificate_validator`
*   `DB_USERNAME`: *Tu usuario de base de datos.*
*   `DB_PASSWORD`: *Tu contraseña de base de datos.*
*   `QUEUE_CONNECTION`: `database`
*   `SESSION_DRIVER`: `database`
*   `CACHE_STORE`: `database`
*   `FILESYSTEM_DISK`: `public`

---

## 🛠️ Comandos Post-Despliegue inicial

Una vez que la aplicación esté corriendo, entra a la sección **Terminal** del contenedor en Coolify y ejecuta:
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```
