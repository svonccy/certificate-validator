<?php

namespace App\Console\Commands;

use App\Models\Certificado;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('certificados:clean-orphans')]
#[Description('Limpia los archivos PDF huerfanos del almacenamiento que no pertenecen a ningun certificado.')]
class CleanOrphanedCertificadosCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diskName = (string) config('certificados.disk', 'public');
        $disk = Storage::disk($diskName);

        $this->info("Escaneando el disco '{$diskName}' en busca de archivos huérfanos...");

        // Obtener todos los archivos registrados en la BD (incluyendo eliminados lógicamente)
        $certificados = Certificado::withTrashed()->get();

        $registeredFiles = [];
        foreach ($certificados as $certificado) {
            if ($certificado->ruta_pdf_original) {
                $registeredFiles[] = $certificado->ruta_pdf_original;
            }
            if ($certificado->ruta_pdf_borrador) {
                $registeredFiles[] = $certificado->ruta_pdf_borrador;
            }
            if ($certificado->ruta_pdf_firmado) {
                $registeredFiles[] = $certificado->ruta_pdf_firmado;
            }
        }

        // Convertir a un conjunto de búsqueda rápida
        $registeredFilesSet = array_flip($registeredFiles);

        $directories = [
            (string) config('certificados.firmados_dir', 'certificados/firmados'),
            (string) config('certificados.borradores_dir', 'certificados/borradores'),
            'certificados/plantillas',
        ];

        $deletedCount = 0;
        $totalChecked = 0;

        foreach ($directories as $directory) {
            if (! $disk->exists($directory)) {
                continue;
            }

            $files = $disk->allFiles($directory);

            foreach ($files as $file) {
                // Saltar archivos ocultos o de control (como .gitignore)
                if (basename($file) === '.gitignore') {
                    continue;
                }

                $totalChecked++;

                if (! isset($registeredFilesSet[$file])) {
                    $this->line("Eliminando archivo huérfano: {$file}");
                    $disk->delete($file);
                    $deletedCount++;
                }
            }
        }

        $this->info("Escaneo completado. Se revisaron {$totalChecked} archivos. Se eliminaron {$deletedCount} archivos huérfanos.");

        return 0;
    }
}
