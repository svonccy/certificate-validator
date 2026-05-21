<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PosicionQr
{
    public function __construct(
        public float $xQr,
        public float $yQr,
        public float $lado,
        public float $anchoBloque,
        public float $altoBloque,
        public bool $textoArriba,
    ) {}
}
