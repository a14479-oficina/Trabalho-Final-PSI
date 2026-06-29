<?php

require_once __DIR__ . '/Conta.php';

class ContaCorrente extends Conta
{
    public function podeLevantar(float $valor): bool
    {
        return $this->saldo >= $valor;
    }
}
