# Informe de viabilidad - CNSM Certificate Validator

## Fuentes de verdad
- [README.md](README.md)
- [docs/sequence_diagram.mmd](docs/sequence_diagram.mmd)

## Resumen ejecutivo
El enfoque es viable y tecnicamente consistente con el flujo descrito. La arquitectura de dos pasadas (QR antes de firma y validacion despues) es adecuada para respetar la integridad criptografica PAdES y evitar romper el sello. El stack descrito en el README (Laravel 13, Filament v5, MySQL, FPDI/TCPDF, Python + pyHanko) es coherente para un MVP, siempre que se atiendan los riesgos operativos y de seguridad descritos abajo.

## Alcance y flujo
- Pasada 1: generar QR, incrustar en PDF, entregar borrador.
- Firma externa: RENIEC aplica PAdES (hash se congela).
- Pasada 2: subir PDF firmado, validar con pyHanko, persistir estado y metadatos.
- Verificacion publica: QR apunta a endpoint oficial de consulta.

## Viabilidad tecnica por componente
### Backend (Laravel 13)
- Adecuado para orquestar flujo, persistencia, y endpoints publicos/privados.
- El README especifica integracion con Python via `Symfony\Component\Process\Process`, lo cual es valido para ejecutar CLI con control de tiempo y recursos.

### Panel (Filament v5)
- Encaja con el uso administrativo y formularios complejos.
- Mantener compatibilidad con el stack TALL (TailwindCSS, Alpine.js, Livewire) es clave.

### PDF (FPDI + TCPDF)
- Buen binomio para incrustar QR sin romper estructura del PDF.
- Importante no modificar el PDF firmado en la segunda pasada.

### Criptografia (pyHanko)
- Herramienta apropiada para validar PAdES y extraer metadatos.
- Debe correrse en entorno controlado con limites de recursos y validacion de entrada.

## Riesgos principales y mitigaciones
- **Integridad del PDF firmado**: cualquier edicion despues de la firma invalida el PAdES.
  - Mitigar: en la pasada 2 solo almacenar y validar, sin modificar.
- **Seguridad de archivos subidos**: PDFs pueden contener payloads maliciosos.
  - Mitigar: escaneo, limites de tamano, sandbox de proceso Python, timeouts y rutas temporales seguras.
- **Disponibilidad del servicio Python**: fallo en CLI bloquea validacion.
  - Mitigar: reintentos, colas, logging detallado, metricas y alertas.
- **Compatibilidad Filament v5**: la skill local es v4 y puede inducir errores.
  - Mitigar: usar documentacion oficial v5 como fuente de verdad para UI/Livewire.
- **Dependencia de plugins de terceros**: plugins pueden no ser compatibles con v5.
  - Mitigar: minimizar plugins en MVP, versionado estricto y pruebas.
- **Verificacion publica**: endpoint de consulta puede ser objetivo de abuso.
  - Mitigar: rate limiting, caching, y sanitizacion de parametros.

## Adecuacion del enfoque
- La separacion en dos pasadas es la manera correcta de preservar la firma PAdES.
- El uso de un microservicio Python es adecuado por madurez criptografica de pyHanko.

## Recomendaciones para viabilidad operativa
- Establecer un contrato de salida JSON para el CLI Python (versionado).
- Definir limites de recursos del proceso (tiempo, memoria, tamano de archivo).
- Asegurar almacenamiento inmutable del PDF firmado (no reescritura).
- Implementar auditoria basica: timestamps, usuario, hash, estado.
- Verificar que el endpoint publico muestre solo datos necesarios.

## Conclusion
El proyecto es viable y el enfoque es correcto para el problema criptografico descrito. La mayor condicion es mantener disciplina en la no modificacion del PDF firmado, y asegurar compatibilidad real con las versiones usadas en el proyecto. Con mitigaciones basicas de seguridad y operacion, el MVP puede avanzar con bajo riesgo.
