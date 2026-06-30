<?php
require_once __DIR__ . '/Conta.php';

class ContaCorrente extends Conta
{
    public function __construct(int $utilizadorId, string $numeroConta, float $saldo = 0.00)
    {
        parent::__construct($utilizadorId, $numeroConta, 'corrente', $saldo);
    }
}
