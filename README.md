# 📜 Sistema de Validación de Certificados Notariales (MVP)
**Cliente:** Colegio de Notarios de San Martín (CNSM)

## 📌 Descripción del Proyecto
Plataforma web diseñada para gestionar el ciclo de vida, emisión y validación pública de certificados notariales. El sistema resuelve el problema criptográfico y legal de la transición del documento digital al soporte físico impreso (Representación Impresa). 

Garantiza la inalterabilidad de la firma digital oficial (PAdES) de la RENIEC, incrustando un código QR de verificación *antes* del sellado criptográfico y validando los metadatos de la firma *después* del proceso.

## 🏗️ Stack Tecnológico
El proyecto utiliza una **Arquitectura Monolítica Híbrida** optimizada para despliegue en entornos Apache/ISPConfig:

*   **Framework Principal:** Laravel 11 (PHP 8.5+)
*   **Panel Administrativo:** FilamentPHP v5 (TALL Stack: TailwindCSS, Alpine.js, Laravel, Livewire)
*   **Base de Datos:** MySQL
*   **Manipulación PDF (Visual):** `setasign/fpdi` y `tecnickcom/tcpdf` (Incrustación de QR sin romper estructura)
*   **Microservicio de Validación (Criptografía):** Python 3 con librería `pyHanko` (Lectura nativa de firmas PAdES-BES / CAdES)
*   **Interoperabilidad:** `Symfony\Component\Process\Process` (Para orquestar Python desde PHP de forma segura)

## 🔄 Flujo de Trabajo y Arquitectura

El sistema opera bajo un flujo de **Dos Pasadas** para respetar las leyes de integridad criptográfica:

1.  **Pasada 1 (Borrador):** Se sube el PDF, se genera un UUID en estado PENDIENTE, se incrusta el QR visual y se descarga.
2.  **Proceso Externo (RENIEC):** El notario sella el PDF (incluyendo el QR) con su token digital. El hash se congela.
3.  **Pasada 2 (Consolidación):** Se sube el PDF firmado al registro específico. Python valida criptográficamente la firma y PHP actualiza el estado a VÁLIDO.
4.  **Verificación Pública:** El QR dirige a la web oficial del CNSM, actuando como la única fuente de la verdad para el papel físico.

### Diagrama de Secuencia

```mermaid
sequenceDiagram
    actor O as Operador CNSM
    participant L as Laravel (Panel)
    participant BD as Base de Datos
    actor N as Notario (Offline)
    participant R as Software RENIEC
    participant P as Script Python (pyHanko)
    actor C as Ciudadano

    %% PASADA 1: Borrador
    O->>L: Sube Plantilla PDF + Datos (DNI, Nombres)
    L->>BD: Crea Registro (ID, Estado: PENDIENTE)
    L->>L: Genera QR con URL (cnsm.org.pe/verificar/{ID})
    L->>L: Incrusta QR en PDF (FPDI)
    L-->>O: Descarga PDF con QR

    %% PROCESO EXTERNO: Firma
    O->>N: Entrega PDF impreso o por USB/Red local
    N->>R: Abre PDF con QR en software RENIEC
    R->>R: Aplica Firma Digital PAdES (Sella hash)
    R-->>O: Devuelve PDF Firmado Digitalmente

    %% PASADA 2: Consolidación
    O->>L: Clic en "Adjuntar PDF" en la fila del {ID}
    L->>P: Envía PDF a validación local
    P->>P: Verifica Hash, Cadena de Confianza y Extrae Metadatos
    P-->>L: Retorna JSON (Válido, Nombre Notario, Fecha)
    L->>BD: Actualiza Registro (Estado: VÁLIDO, Datos Firma)
    L->>L: Guarda PDF definitivo en Servidor
    L-->>O: Confirmación de Éxito

    %% VERIFICACIÓN PÚBLICA
    C->>C: Escanea QR en papel físico
    C->>L: Petición a cnsm.org.pe/verificar/{ID}
    L->>BD: Consulta Estado de {ID}
    BD-->>L: Retorna VÁLIDO + Datos
    L-->>C: Muestra Pantalla de Autenticidad Oficial