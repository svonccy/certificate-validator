<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PosicionQr
{
    public function __construct(
        public float $x,
        public float $y,
        public float $lado,
        public float $anchoBloque,
        public float $altoBloque,
    ) {}
}
