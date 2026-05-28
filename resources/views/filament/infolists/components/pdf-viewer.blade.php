@php
    $pdfPath = $path ?? null;
@endphp

@vite(['resources/css/app.css'])

<div class="pdf-viewer-wrapper">
    @if($pdfPath)
        <div class="pdf-object-container">
            <object
                data="{{ asset('storage/' . $pdfPath) }}#view=Fit"
                type="application/pdf"
                title="Vista previa del documento PDF"
                class="pdf-object"
            >
                <div class="pdf-fallback">
                    <p>Tu navegador no puede visualizar este PDF.</p>
                    <a href="{{ asset('storage/' . $pdfPath) }}" download class="pdf-download-btn">Descargar documento</a>
                </div>
            </object>
        </div>
    @else
        <div class="pdf-viewer-empty">
            No hay documento disponible para previsualizar.
        </div>
    @endif
</div>
