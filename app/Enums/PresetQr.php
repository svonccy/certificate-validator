<?php

declare(strict_types=1);

namespace App\Enums;

enum PresetQr: string
{
    case Manual = 'manual';
    case Superior1 = 'superior_1';
    case Superior2 = 'superior_2';
    case Superior3 = 'superior_3';
    case Superior4 = 'superior_4';
    case Superior5 = 'superior_5';
    case Medio1 = 'medio_1';
    case Medio2 = 'medio_2';
    case Medio3 = 'medio_3';
    case Medio4 = 'medio_4';
    case Medio5 = 'medio_5';
    case Inferior1 = 'inferior_1';
    case Inferior2 = 'inferior_2';
    case Inferior3 = 'inferior_3';
    case Inferior4 = 'inferior_4';
    case Inferior5 = 'inferior_5';
    case SuperiorIzquierda = 'superior_izquierda';
    case SuperiorDerecha = 'superior_derecha';
    case InferiorIzquierda = 'inferior_izquierda';
    case InferiorDerecha = 'inferior_derecha';
    case Centro = 'centro';

    public function etiqueta(): string
    {
        return match ($this->normalizado()) {
            self::Manual => 'Manual',
            self::Superior1 => 'Superior 1',
            self::Superior2 => 'Superior 2',
            self::Superior3 => 'Superior 3',
            self::Superior4 => 'Superior 4',
            self::Superior5 => 'Superior 5',
            self::Medio1 => 'Medio 1',
            self::Medio2 => 'Medio 2',
            self::Medio3 => 'Medio 3',
            self::Medio4 => 'Medio 4',
            self::Medio5 => 'Medio 5',
            self::Inferior1 => 'Inferior 1',
            self::Inferior2 => 'Inferior 2',
            self::Inferior3 => 'Inferior 3',
            self::Inferior4 => 'Inferior 4',
            self::Inferior5 => 'Inferior 5',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        return self::opcionesCuadricula();
    }

    /**
     * @return array<string, string>
     */
    public static function opcionesCuadricula(): array
    {
        return [
            self::Superior1->value => 'Posición 1,1',
            self::Superior2->value => 'Posición 1,2',
            self::Superior3->value => 'Posición 1,3',
            self::Superior4->value => 'Posición 1,4',
            self::Superior5->value => 'Posición 1,5',
            self::Medio1->value => 'Posición 2,1',
            self::Medio2->value => 'Posición 2,2',
            self::Medio3->value => 'Posición 2,3',
            self::Medio4->value => 'Posición 2,4',
            self::Medio5->value => 'Posición 2,5',
            self::Inferior1->value => 'Posición 3,1',
            self::Inferior2->value => 'Posición 3,2',
            self::Inferior3->value => 'Posición 3,3',
            self::Inferior4->value => 'Posición 3,4',
            self::Inferior5->value => 'Posición 3,5',
        ];
    }

    public function normalizado(): self
    {
        return match ($this) {
            self::SuperiorIzquierda => self::Superior1,
            self::SuperiorDerecha => self::Superior5,
            self::InferiorIzquierda => self::Inferior1,
            self::InferiorDerecha => self::Inferior5,
            self::Centro => self::Medio3,
            default => $this,
        };
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public function coordenadasCuadricula(): ?array
    {
        return match ($this->normalizado()) {
            self::Superior1 => [1, 1],
            self::Superior2 => [1, 2],
            self::Superior3 => [1, 3],
            self::Superior4 => [1, 4],
            self::Superior5 => [1, 5],
            self::Medio1 => [2, 1],
            self::Medio2 => [2, 2],
            self::Medio3 => [2, 3],
            self::Medio4 => [2, 4],
            self::Medio5 => [2, 5],
            self::Inferior1 => [3, 1],
            self::Inferior2 => [3, 2],
            self::Inferior3 => [3, 3],
            self::Inferior4 => [3, 4],
            self::Inferior5 => [3, 5],
            self::Manual => null,
        };
    }

    public static function desdeValor(string $valor): self
    {
        $preset = self::tryFrom($valor);

        return $preset?->normalizado() ?? self::Superior1;
    }
}
