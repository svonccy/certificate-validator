# 🚀 Despliegue en Coolify (Certificate Validator)

> [!WARNING]
> **Atención:** Para servidores con recursos limitados (VPS de 1GB o 2GB de RAM), compilar localmente con Nixpacks o Docker Compose causará problemas de memoria (Out of Memory) y la caída del servidor debido al peso de compilar Node/Vite y Python simultáneamente.
> 
> 👉 **Por favor, lee la [Guía Definitiva de Despliegue CI/CD](docs/DESPLIEGUE.md) para configurar el flujo de GitHub Actions + GHCR. Esa es la única estrategia recomendada y soportada para este proyecto.**

---

Una vez que hayas configurado el recurso **Docker Image** en Coolify siguiendo la [guía de CI/CD](docs/DESPLIEGUE.md), aquí tienes las configuraciones *específicas de este proyecto* que debes aplicar en la interfaz de Coolify para que la app funcione correctamente:

## 1. Variables de Entorno Obligatorias

En la pestaña **Environment Variables** de tu aplicación en Coolify, configura:

*   `APP_ENV`: `production`
*   `APP_DEBUG`: `false`
*   `APP_KEY`: *(Genera una ejecutando `php artisan key:generate --show` en tu entorno local y pégala aquí)*
*   `DB_CONNECTION`: `mysql` 
*   `DB_HOST`: *(CRÍTICO: Usa el "Internal Host" que Coolify le asigna a tu base de datos, ej: `v2ii4wsdrt...`. ¡NO uses tu dominio público o el comando migrate se congelará para siempre por NAT Hairpinning!)*
*   `DB_PORT`: `3306`
*   `DB_DATABASE`: `certificate_validator`
*   `DB_USERNAME`: *(Tu usuario de DB)*
*   `DB_PASSWORD`: *(Tu contraseña de DB)*
*   `QUEUE_CONNECTION`: `database`
*   `SESSION_DRIVER`: `database`
*   `CACHE_STORE`: `database`
*   `FILESYSTEM_DISK`: `public`

## 2. Configurar el Worker de Colas (Obligatorio)

La validación de firmas criptográficas de PDFs en Python (`pyHanko`) es una tarea pesada, por lo que este sistema depende estrictamente de las colas de Laravel.

1. En la configuración de tu app en Coolify, ve al menú **Workers** (o **Additional Processes**).
2. Añade un nuevo Worker y configúralo con el comando:
   ```bash
   php artisan queue:work --verbose --tries=3 --timeout=90
   ```
3. Inícialo. Si no enciendes este worker, los certificados se quedarán en estado "Pendiente" por siempre.

## 3. Automatizar Comandos Post-Despliegue (¡Sin pereza!)

Para que no tengas que entrar a la Terminal manualmente cada vez que haces un cambio en el código (y evitar que el sistema se quede con la caché antigua), Coolify tiene una función de automatización.

1. Ve a la configuración de tu aplicación en Coolify.
2. Busca el campo llamado **Post-deployment Command** (Comando Post-Despliegue).
3. Pega exactamente este bloque de comandos ahí:

```bash
php artisan migrate --force && php artisan storage:link && php artisan optimize:clear && php artisan optimize && php artisan filament:cache-components
```

¡Listo! Ahora cada vez que hagas un `git push`, Coolify descargará la imagen y ejecutará estos comandos por ti automáticamente.

> [!TIP]
> **Privacidad de la Imagen GHCR:** Recuerda que si tu repositorio en GitHub es privado, la imagen Docker compilada en la sección "Packages" también lo será por defecto. Si Coolify lanza un error `Unauthorized` al hacer el despliegue, debes ir a GitHub Packages y cambiar la visibilidad de tu paquete a **Public** (tu código fuente seguirá siendo privado, solo la imagen compilada será pública).
