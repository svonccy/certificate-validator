<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Verificación de certificado</title>
    <style>
        :root {
            --bg: #f3f6f2;
            --panel: rgba(255, 255, 255, 0.82);
            --panel-strong: rgba(255, 255, 255, 0.96);
            --text: #0f1f16;
            --muted: #5f6d63;
            --border: rgba(22, 77, 43, 0.14);
            --success: #1a7f37;
            --success-soft: rgba(26, 127, 55, 0.14);
            --warning: #b26a00;
            --warning-soft: rgba(178, 106, 0, 0.12);
            --danger: #b42318;
            --danger-soft: rgba(180, 35, 24, 0.12);
            --accent: #186002;
            --accent-soft: rgba(24, 96, 2, 0.12);
            --shadow: 0 24px 60px rgba(16, 24, 16, 0.10);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0a0d0b;
                --panel: rgba(17, 22, 18, 0.86);
                --panel-strong: rgba(19, 25, 20, 0.98);
                --text: #eff4ed;
                --muted: #9aa79d;
                --border: rgba(120, 152, 126, 0.18);
                --shadow: 0 24px 60px rgba(0, 0, 0, 0.42);
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(24, 96, 2, 0.20), transparent 36%),
                radial-gradient(circle at top right, rgba(26, 127, 55, 0.16), transparent 32%),
                linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02)),
                var(--bg);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .wrap {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 40px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.45fr 0.95fr;
            gap: 24px;
            align-items: stretch;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--panel);
            box-shadow: var(--shadow);
            width: fit-content;
        }

        .brand-mark {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4caf50, var(--accent));
            box-shadow: 0 0 0 8px var(--accent-soft);
        }

        h1 {
            margin: 18px 0 12px;
            font-size: clamp(2rem, 4vw, 4rem);
            line-height: 0.95;
            letter-spacing: -0.05em;
        }

        .lede {
            margin: 0;
            max-width: 64ch;
            color: var(--muted);
            font-size: 1.02rem;
            line-height: 1.65;
        }

        .status-card,
        .panel,
        .details {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--panel);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .status-card {
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 700;
            width: fit-content;
        }

        .status-chip.success { background: var(--success-soft); color: var(--success); }
        .status-chip.warning { background: var(--warning-soft); color: var(--warning); }
        .status-chip.danger { background: var(--danger-soft); color: var(--danger); }

        .status-title {
            margin: 18px 0 8px;
            font-size: 1.8rem;
            letter-spacing: -0.04em;
        }

        .status-copy {
            color: var(--muted);
            line-height: 1.6;
            margin: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .stat {
            padding: 16px;
            border-radius: 18px;
            background: var(--panel-strong);
            border: 1px solid var(--border);
        }

        .stat-label {
            display: block;
            font-size: 0.8rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1rem;
            font-weight: 700;
            word-break: break-word;
        }

        .panel {
            padding: 22px;
        }

        .panel-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            padding: 16px;
            border-radius: 18px;
            background: var(--panel-strong);
            border: 1px solid var(--border);
        }

        .field small {
            display: block;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.76rem;
        }

        .field strong {
            display: block;
            font-size: 1rem;
            line-height: 1.5;
            word-break: break-word;
        }

        .details {
            margin-top: 24px;
            padding: 20px;
        }

        .details h2 {
            margin: 0 0 16px;
            font-size: 1.3rem;
            letter-spacing: -0.03em;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .summary {
            margin-top: 18px;
            padding: 16px;
            border-radius: 18px;
            background: var(--panel-strong);
            border: 1px solid var(--border);
        }

        .summary p {
            margin: 0;
            line-height: 1.65;
            color: var(--muted);
        }

        details {
            margin-top: 16px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: var(--panel-strong);
            padding: 16px;
        }

        details summary {
            cursor: pointer;
            font-weight: 700;
            color: var(--text);
        }

        pre {
            margin: 14px 0 0;
            padding: 16px;
            border-radius: 16px;
            background: rgba(0,0,0,0.08);
            color: var(--text);
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.6;
        }

        .footer {
            margin-top: 20px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .kpi-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .kpi {
            padding: 10px 12px;
            border-radius: 999px;
            background: var(--panel-strong);
            border: 1px solid var(--border);
            color: var(--text);
            font-weight: 600;
            font-size: 0.92rem;
        }

        @media (max-width: 900px) {
            .hero,
            .panel-grid,
            .details-grid,
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <section class="status-card">
                <div>
                    <div class="brand">
                        <span class="brand-mark"></span>
                        <strong>CNSM · Verificación pública</strong>
                    </div>

                    <h1>Certificado verificado</h1>
                    <p class="lede">
                        Este certificado fue emitido por el sistema y su firma digital fue revisada con la cadena de confianza disponible.
                        La información mostrada corresponde al registro oficial almacenado en el portal.
                    </p>

                    <div class="kpi-row">
                        <span class="kpi">ID: {{ $certificado->id }}</span>
                        <span class="kpi">Código: {{ $certificado->codigo_certificado }}</span>
                        <span class="kpi">Estado: {{ $estadoTexto }}</span>
                    </div>
                </div>

                <div>
                    <div class="status-chip {{ $estadoClase }}">
                        {{ $estadoTexto }}
                    </div>
                    <h2 class="status-title">{{ $certificado->nombre_titular }}</h2>
                    <p class="status-copy">
                        {{ $borradorCoincide ? 'El documento firmado corresponde al borrador registrado.' : 'El documento firmado no coincide con el borrador registrado.' }}
                    </p>

                    <div class="stats">
                        <div class="stat">
                            <span class="stat-label">Integridad</span>
                            <span class="stat-value">{{ ! empty($firma['integridad']) ? 'Correcta' : 'No verificada' }}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Confianza</span>
                            <span class="stat-value">{{ ! empty($firma['cadena_confiable']) ? 'Confiable' : 'Pendiente' }}</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Borrador</span>
                            <span class="stat-value">{{ $borradorCoincide ? 'Coincide' : 'No coincide' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="panel">
                <div class="panel-grid">
                    <div class="field">
                        <small>DNI</small>
                        <strong>{{ $certificado->dni_titular }}</strong>
                    </div>
                    <div class="field">
                        <small>Código</small>
                        <strong>{{ $certificado->codigo_certificado ?? 'No disponible' }}</strong>
                    </div>
                    <div class="field">
                        <small>Firma</small>
                        <strong>{{ $firma['algoritmo'] ?? 'No disponible' }}</strong>
                    </div>
                    <div class="field">
                        <small>Fecha de firma</small>
                        <strong>{{ $certificado->firma_fecha?->format('d/m/Y H:i:s') ?? 'No disponible' }}</strong>
                    </div>
                    <div class="field">
                        <small>Validado en</small>
                        <strong>{{ $certificado->validado_en?->format('d/m/Y H:i:s') ?? 'No disponible' }}</strong>
                    </div>
                    <div class="field">
                        <small>Serial</small>
                        <strong>{{ $certificado->firma_serial ?? 'No disponible' }}</strong>
                    </div>
                </div>
            </aside>
        </div>

        <section class="details">
            <h2>Detalle de verificación</h2>

            <div class="details-grid">
                <div class="field">
                    <small>Firmante</small>
                    <strong>{{ $firmante['nombre'] ?? 'No identificado' }}</strong>
                </div>
                <div class="field">
                    <small>Documento del firmante</small>
                    <strong>{{ $firmante['documento'] ?? 'No disponible' }}</strong>
                </div>
                <div class="field">
                    <small>Emisor</small>
                    <strong>{{ $certificadoFirma['issuer'] ?? 'No disponible' }}</strong>
                </div>
            </div>

            <div class="summary">
                <p>
                    <strong>Resumen:</strong>
                    @if ($estadoTexto === 'Válido')
                        El documento fue validado correctamente y la cadena de confianza fue reconocida por el sistema.
                    @elseif ($estadoTexto === 'Pendiente')
                        La firma es criptográficamente correcta, pero la cadena de confianza no pudo confirmarse con todos los certificados disponibles.
                    @else
                        El documento no superó la validación de firma o no corresponde al borrador registrado.
                    @endif
                </p>
            </div>

            @if ($detalleFirma !== '')
                <details>
                    <summary>Ver detalle técnico</summary>
                    <pre>{{ $detalleFirma }}</pre>
                </details>
            @endif

            <div class="footer">
                Si necesitas verificar otra vez el documento, vuelve a escanear el QR del certificado impreso o consulta el portal del CNSM.
            </div>
        </section>
    </div>
</body>
</html>
