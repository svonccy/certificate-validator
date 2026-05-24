<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Verificación de Certificado</title>
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', sans-serif;
            --font-title: 'Outfit', sans-serif;
            --bg-gradient: radial-gradient(circle at 0% 0%, rgba(22, 101, 52, 0.12) 0%, transparent 50%),
                           radial-gradient(circle at 100% 100%, rgba(22, 163, 74, 0.08) 0%, transparent 50%),
                           #fafafa;
            --card-bg: rgba(255, 255, 255, 0.75);
            --card-border: rgba(228, 228, 231, 0.8);
            --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
            --text-main: #09090b;
            --text-muted: #71717a;
            --divider: #f4f4f5;

            /* Status Colors */
            --color-success: #16a34a;
            --color-success-bg: rgba(22, 163, 74, 0.1);
            --color-warning: #ca8a04;
            --color-warning-bg: rgba(202, 138, 4, 0.1);
            --color-danger: #dc2626;
            --color-danger-bg: rgba(220, 38, 38, 0.1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-gradient: radial-gradient(circle at 0% 0%, rgba(22, 101, 52, 0.2) 0%, transparent 40%),
                               radial-gradient(circle at 100% 100%, rgba(34, 197, 94, 0.08) 0%, transparent 40%),
                               #09090b;
                --card-bg: rgba(9, 9, 11, 0.6);
                --card-border: rgba(39, 39, 42, 0.6);
                --card-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.8);
                --text-main: #f4f4f5;
                --text-muted: #a1a1aa;
                --divider: rgba(39, 39, 42, 0.8);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }

        /* Decorative blur backgrounds */
        .glow {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--color-success) 0%, transparent 70%);
            opacity: 0.15;
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
        }
        .glow-1 { top: 15%; left: 10%; }
        .glow-2 { bottom: 15%; right: 10%; }

        .container {
            width: 100%;
            max-width: 460px;
            z-index: 10;
            animation: cardFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 32px;
            padding: 40px 32px;
            box-shadow: var(--card-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Badge and status styles */
        .status-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }

        .status-badge {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            position: relative;
            box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.08);
        }

        .status-badge::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid currentColor;
            opacity: 0.2;
            animation: pulseRing 2s infinite cubic-bezier(0.215, 0.610, 0.355, 1);
        }

        .status-badge.success {
            background: var(--color-success-bg);
            color: var(--color-success);
        }
        .status-badge.warning {
            background: var(--color-warning-bg);
            color: var(--color-warning);
        }
        .status-badge.danger {
            background: var(--color-danger-bg);
            color: var(--color-danger);
        }

        .status-badge svg {
            width: 32px;
            height: 32px;
            stroke-width: 2.5;
        }

        .status-label {
            font-family: var(--font-title);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .status-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.4;
            max-width: 280px;
        }

        /* Info Grid */
        .info-grid {
            text-align: left;
            margin-bottom: 32px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.02);
            border-radius: 16px;
            padding: 16px;
        }

        @media (prefers-color-scheme: dark) {
            .info-item {
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(255, 255, 255, 0.02);
            }
        }

        .info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.35;
        }

        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 24px;
            border-radius: 18px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-primary {
            background: var(--text-main);
            color: var(--card-bg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            opacity: 0.95;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        /* Collapsible details style */
        details {
            margin-top: 24px;
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid var(--divider);
            border-radius: 16px;
            padding: 12px 16px;
            text-align: left;
        }
        @media (prefers-color-scheme: dark) {
            details {
                background: rgba(255, 255, 255, 0.01);
            }
        }
        details summary {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            outline: none;
            user-select: none;
        }
        details[open] summary {
            margin-bottom: 12px;
            color: var(--text-main);
        }
        .tech-content {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Footer styling */
        .footer {
            margin-top: 24px;
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Animations */
        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulseRing {
            0% {
                transform: scale(0.95);
                opacity: 0.5;
            }
            70% {
                transform: scale(1.15);
                opacity: 0;
            }
            100% {
                transform: scale(0.95);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Background glows -->
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="container">
        <main class="card">
            <div class="status-container">
                <!-- Válido (success) / Pendiente (warning) / Rechazado (danger) -->
                @if($estadoClase === 'success')
                    <div class="status-badge success">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <h1 class="status-label">Certificado verificado</h1>
                    <p class="status-desc">La firma digital y los datos de registro oficial han sido validados exitosamente.</p>
                @elseif($estadoClase === 'warning')
                    <div class="status-badge warning">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h1 class="status-label">Verificación Pendiente</h1>
                    <p class="status-desc">El certificado está registrado, pero su firma digital está pendiente de validación.</p>
                @else
                    <div class="status-badge danger">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h1 class="status-label">Certificado No Válido</h1>
                    <p class="status-desc">Este certificado no superó la validación o ha sido rechazado.</p>
                @endif
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Titular</div>
                    <div class="info-value">{{ $titular?->nombre_completo ?? 'No disponible' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identificación</div>
                    <div class="info-value">{{ $titular?->dni ?? 'No disponible' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Código de Certificado</div>
                    <div class="info-value">{{ $certificado->codigo_certificado ?? 'No disponible' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Firma</div>
                    <div class="info-value">
                        {{ $firmaDigital?->fecha_firma?->format('d/m/Y H:i') ?? $certificado->created_at?->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            @if($certificado->ruta_pdf_firmado || $certificado->ruta_pdf_borrador)
                <a href="{{ route('certificados.descargar', $certificado) }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Descargar Certificado</span>
                </a>
            @endif

            @if($detalleFirma !== '')
                <details>
                    <summary>Ver detalle técnico</summary>
                    <div class="tech-content">{{ $detalleFirma }}</div>
                </details>
            @endif

            <p class="footer">
                Se hace uso de firmas digitales criptográficas para garantizar la autenticidad de sus documentos.
            </p>
        </main>
    </div>
</body>
</html>
