# Guía Definitiva de Despliegue CI/CD Universal (GitHub Actions + GHCR + Coolify)

Esta guía documenta el estándar de oro para desplegar aplicaciones modernas (Laravel, Node, Python, etc.) en un VPS con recursos limitados (1GB-2GB RAM). 

## 🧠 La Filosofía: ¿Por qué este flujo?

Si tienes un servidor pequeño y le pides que compile dependencias pesadas (Node.js, compilación de assets Vite, paquetes de Python C-extensions, o `composer install`), el servidor se asfixiará. Su CPU se irá al 100%, agotará la memoria RAM y se colgará (Out of Memory), matando tu aplicación en producción durante el proceso.

**La solución:** "Delegar el trabajo pesado".
Usamos **GitHub Actions** (servidores de Microsoft potentes y gratuitos) para compilar nuestro código y empaquetarlo en un contenedor. Luego, lo subimos a **GHCR** (GitHub Container Registry). Finalmente, nuestro servidor ligero (gestionado por **Coolify**) solo tiene que descargar (*pull*) la imagen ya lista y ejecutarla.

---

## 🏗️ Arquitectura del Flujo

1. **`git push`**: Subes tu código a GitHub.
2. **GitHub Actions (CI)**: Lee tu `Dockerfile`, instala todo, compila assets y crea una imagen de Docker.
3. **GHCR (CD)**: Guarda esa imagen de forma segura.
4. **Webhook**: GitHub le avisa a Coolify: "¡Hay una nueva versión!".
5. **Coolify**: Descarga la nueva imagen, apaga el contenedor viejo y enciende el nuevo.

---

## 🚀 Paso 1: Preparación del Proyecto (El Dockerfile)

La magia ocurre porque le decimos a GitHub cómo construir la app a través de un `Dockerfile`. 

**Reglas de oro para tu Dockerfile:**
1. **Permisos (Crítico en PHP/Laravel):** Siempre asegúrate de que los directorios donde la app necesita escribir pertenezcan al usuario correcto (usualmente `www-data` u `octane`).
   ```dockerfile
   RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
   ```
2. **Instalación de Dependencias Externas:** Si tu app de Laravel usa Python (ej. `pyHanko`), debes instalarlo en el contenedor final, no solo depender de Nixpacks u otra magia automática.
   ```dockerfile
   RUN apk add --no-cache python3 py3-pip
   ```
3. **No arrastrar basura:** Usa un `.dockerignore` estricto (ignora `.git/`, `node_modules/`, `vendor/`) para que el contexto de Docker sea pequeño. No necesitas subir `node_modules` si el Dockerfile los va a instalar.

---

## ⚙️ Paso 2: Configuración en Coolify

Para que Coolify acepte despliegues externos, necesitamos configurarlo en modo "Imagen de Docker" sin vincularlo directamente a la rama de Git.

### 2.1 Configurar Permisos de API
1. En Coolify, ve a **Settings → Configuration → Advanced**.
2. Activa **"API Access"** (sin esto, el webhook de GitHub será rechazado).

### 2.2 Generar un Token de Despliegue
1. Ve a **Keys & Tokens → API Tokens → New Token**.
2. Asigna el permiso **Deploy** (o equivalente).
3. **Cópialo**. Solo lo verás una vez.

### 2.3 Crear el Recurso
1. En tu proyecto de Coolify, clic en **+ New Resource**.
2. Selecciona **Docker Based → Docker Image**.
   *(Nota: Dice "without Git" porque Coolify no usará Git, solo consumirá la imagen ya compilada).*
3. Completa los datos:
   - **Docker Image:** `ghcr.io/TU_USUARIO/TU_REPOSITORIO:latest` (todo en minúsculas).
   - **Port:** El puerto que expone tu Dockerfile (ej. `80` para FrankenPHP, `3000` para Node).
   - **Domains:** Asigna el dominio de tu app (ej. `https://miapp.com`).
4. **Aún NO hagas deploy.**

### 2.4 Obtener el Webhook
1. En la página de configuración del recurso recién creado, busca la sección **Webhooks** (o el botón *Deploy Webhook*).
2. Copia la URL del webhook (se verá algo como `https://coolify.tudominio.com/api/v1/deploy?uuid=xyz...`).

---

## 🐙 Paso 3: Configuración de GitHub Actions

Ahora le daremos a GitHub el poder de construir y avisar a Coolify.

### 3.1 Crear los Secrets en GitHub
En tu repositorio de GitHub, ve a **Settings → Secrets and variables → Actions → New repository secret**.
Agrega dos secretos:
- `COOLIFY_WEBHOOK`: La URL completa que copiaste en el paso 2.4.
- `COOLIFY_TOKEN`: El token de API que generaste en el paso 2.2.

### 3.2 El archivo `.github/workflows/deploy.yml`
Crea este archivo en la raíz de tu proyecto. Aquí están las **mejores prácticas** ya aplicadas:

```yaml
name: Build, Publish and Deploy to Coolify

on:
  push:
    branches: [ "main" ]

env:
  REGISTRY: ghcr.io

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Set lower case owner name
        run: |
          echo "IMAGE_NAME=${GITHUB_REPOSITORY,,}" >> ${GITHUB_ENV}

      - name: Log in to the Container registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract metadata (tags, labels) for Docker
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}
          tags: type=raw,value=latest

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Build and push Docker image
        uses: docker/build-push-action@v5
        with:
          context: .
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

      - name: Trigger Coolify Deploy Webhook
        env:
          COOLIFY_WEBHOOK: ${{ secrets.COOLIFY_WEBHOOK }}
          COOLIFY_TOKEN: ${{ secrets.COOLIFY_TOKEN }}
        run: |
          # Añadimos force=true para garantizar que Coolify descargue la nueva imagen 
          # y no recicle la cache local del servidor.
          if [[ "$COOLIFY_WEBHOOK" == *"?"* ]]; then
            WEBHOOK_URL="${COOLIFY_WEBHOOK}&force=true"
          else
            WEBHOOK_URL="${COOLIFY_WEBHOOK}?force=true"
          fi
          
          if [ -n "$COOLIFY_TOKEN" ]; then
            curl -sS -X GET "$WEBHOOK_URL" \
              --header "Authorization: Bearer $COOLIFY_TOKEN"
          else
            curl -sS -X GET "$WEBHOOK_URL"
          fi
```

### Explicación de los "Superpoderes" de este workflow:
- **`${GITHUB_REPOSITORY,,}`**: GHCR odia las mayúsculas en los nombres de imágenes. Esto convierte dinámicamente `Usuario/MiRepo` a `usuario/mirepo`, evitando que la build falle.
- **`cache-from/to: type=gha`**: Usa el caché de GitHub Actions. Si no cambiaste tus dependencias de Node o Composer, Docker Buildx se saltará esos pasos. Reduce el tiempo de build de 5 minutos a apenas 30 segundos.
- **`&force=true` en el Webhook**: Coolify a veces es perezoso y si ve que la etiqueta sigue siendo `:latest`, no descarga la imagen nueva. Este parámetro le fuerza a hacer un `docker pull` agresivo y reiniciar tu app.

---

## 🧯 Troubleshooting: Errores Comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| **Error 500 en la app** | Permisos incorrectos (Laravel no puede escribir en `storage/logs`). | Añadir `RUN chown -R www-data:www-data /app` al final del Dockerfile. |
| **Error 500 específico de feature** | Faltan paquetes a nivel SO (ej. Python, zip, pgsql). | Añadir la instalación con `apk add` (Alpine) o `apt-get install` (Ubuntu/Debian) en el Dockerfile. |
| **La build en GH falla por "invalid reference"** | El nombre de la imagen tiene mayúsculas. | Asegúrate de usar la técnica `${GITHUB_REPOSITORY,,}`. |
| **Coolify dice "Deploying" pero la app no cambia** | Caché de la etiqueta `:latest`. | Validar que el webhook esté inyectando `?force=true` o `&force=true`. |
| **Coolify lanza error "Unauthorized" o no hace nada** | Faltan permisos de API en Coolify. | Verificar **API Access** en Settings de Coolify y que pasas el `$COOLIFY_TOKEN` en el Header del curl. |
| **Error `Unauthorized` al hacer PULL de GHCR** | La imagen Docker heredó la privacidad de tu repo de GitHub. | Ve a GitHub > Packages > Package Settings y cambia la visibilidad del paquete a **Public**. |
| **`ERR_TOO_MANY_REDIRECTS` o Alerta de Malware** | Conflicto de Auto-HTTPS entre Coolify (Traefik) y FrankenPHP. | En el `Dockerfile`, añade `ENV SERVER_NAME=":80"`. Y en Laravel (`bootstrap/app.php`), añade `$middleware->trustProxies(at: '*');`. |
| **`php artisan migrate` se congela (Stand by)** | Bloqueo por NAT Hairpinning al usar un dominio público (`database.dominio.com`) para conectarse a una BD en el mismo servidor. | Cambia `DB_HOST` en tu `.env` por el **Internal Host** (ej: `v2ii4ws...`) que te da Coolify en la configuración de la Base de Datos. |
| **Build falla: `Cannot load Zend OPcache` o similar** | `install-php-extensions` falla porque intenta instalar extensiones (`gd`, `opcache`) que ya vienen integradas en FrankenPHP. | Usa el comando nativo omitiendo las conflictivas: `RUN install-php-extensions pdo_mysql intl zip pcntl bcmath`. |
