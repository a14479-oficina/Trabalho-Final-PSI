<?php
require_once __DIR__ . '/Conta.php';

final class ContaPoupanca extends Conta
{
    private float $limiteLevantamento = 500.00;

    public function __construct(int $utilizadorId, string $numeroConta, float $saldo = 0.00)
    {
        parent::__construct($utilizadorId, $numeroConta, 'poupanca', $saldo);
    }

    public function levantar(PDO $db, float $valor): bool
    {
        if ($valor > $this->limiteLevantamento) {
            return false;
        }
        return parent::levantar($db, $valor);
    }
}
