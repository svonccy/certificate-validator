<?php

declare(strict_types=1);

return [
    'firma_timeout' => (int) env('CNSM_FIRMA_TIMEOUT', 60),
    'disk' => env('CNSM_CERTIFICADOS_DISK', 'public'),
    'firmados_dir' => env('CNSM_CERTIFICADOS_FIRMADOS_DIR', 'certificados/firmados'),
    'borradores_dir' => env('CNSM_CERTIFICADOS_BORRADORES_DIR', 'certificados/borradores'),
];
