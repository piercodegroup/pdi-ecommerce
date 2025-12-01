<?php

namespace App\Services;

class FidelidadeService
{
    public static function calcularPontos($valorTotal)
    {
        $valor = (float) $valorTotal;

        if ($valor >= 10 && $valor < 15) {
            return 5;
        } elseif ($valor >= 15 && $valor < 30) {
            return 10;
        } elseif ($valor >= 30 && $valor < 50) {
            return 15;
        } elseif ($valor >= 50 && $valor < 100) {
            return 25;
        } elseif ($valor >= 100) {
            return 50;
        }

        return 0;
    }
}