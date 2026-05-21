<?php

declare(strict_types=1);

return [
    'firma_timeout' => (int) env('CNSM_FIRMA_TIMEOUT', 60),
    'disk' => env('CNSM_CERTIFICADOS_DISK', 'public'),
    'firmados_dir' => env('CNSM_CERTIFICADOS_FIRMADOS_DIR', 'certificados/firmados'),
    'borradores_dir' => env('CNSM_CERTIFICADOS_BORRADORES_DIR', 'certificados/borradores'),
    'defaults' => [
        'preset' => env('CNSM_QR_PRESET', 'superior_izquierda'),
        'lado' => (float) env('CNSM_QR_LADO', 30),
        'margen_x' => (float) env('CNSM_QR_MARGEN_X', 5),
        'margen_y' => (float) env('CNSM_QR_MARGEN_Y', 5),
        'ancho_bloque_factor' => (float) env('CNSM_QR_ANCHO_BLOQUE_FACTOR', 1.2),
        'pagina' => (int) env('CNSM_QR_PAGINA', 1),
        'x' => env('CNSM_QR_X'),
        'y' => env('CNSM_QR_Y'),
    ],
];
