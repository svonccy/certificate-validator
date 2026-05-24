<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use RuntimeException;

final class NormalizadorPdfCli implements NormalizadorPdfContract
{
    public function normalizar(string $rutaPdf): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_repair_').'.pdf';

        if ($this->comandoExiste('qpdf')) {
            $comando = sprintf('qpdf --disable-object-streams %s %s', escapeshellarg($rutaPdf), escapeshellarg($tempPath));
            $output = [];
            $resultCode = 1;
            exec($comando, $output, $resultCode);

            if ($resultCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                copy($tempPath, $rutaPdf);
                @unlink($tempPath);

                return;
            }
        }

        if ($this->comandoExiste('gs')) {
            $comando = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -dEmbedAllFonts=true -dSubsetFonts=false -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellarg($tempPath),
                escapeshellarg($rutaPdf)
            );
            $output = [];
            $resultCode = 1;
            exec($comando, $output, $resultCode);

            if ($resultCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                copy($tempPath, $rutaPdf);
                @unlink($tempPath);

                return;
            }
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        throw new RuntimeException(
            'El PDF original utiliza una compresión no soportada por FPDI y no pudo ser reparado automáticamente.'
        );
    }

    private function comandoExiste(string $comando): bool
    {
        $where = DIRECTORY_SEPARATOR === '\\' ? 'where' : 'which';
        $output = [];
        $res = 1;
        exec("$where ".escapeshellcmd($comando), $output, $res);

        return $res === 0;
    }
}
