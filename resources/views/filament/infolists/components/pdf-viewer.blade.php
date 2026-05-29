@php
    $pdfPath = $path ?? null;
    $disk = (string) config('certificados.disk', 'public');
    $pdfUrl = $pdfPath
        ? ($disk === 'public' ? asset('storage/' . $pdfPath) : \Illuminate\Support\Facades\Storage::disk($disk)->url((string) $pdfPath))
        : null;
@endphp

@vite(['resources/css/app.css'])

<div class="pdf-viewer-wrapper">
    @if($pdfUrl)
        <div class="pdf-object-container">
            <object
                data="{{ $pdfUrl }}#view=Fit"
                type="application/pdf"
                title="Vista previa del documento PDF"
                class="pdf-object"
            >
                <div class="pdf-fallback">
                    <p>Tu navegador no puede visualizar este PDF.</p>
                    <a href="{{ $pdfUrl }}" download class="pdf-download-btn">Descargar documento</a>
                </div>
            </object>
        </div>
    @else
        <div class="pdf-viewer-empty">
            No hay documento disponible para previsualizar.
        </div>
    @endif
</div>
