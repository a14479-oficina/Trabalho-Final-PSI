<?php
require_once __DIR__ . '/Conta.php';

final class ContaPoupanca extends Conta {
    public function levantar(PDO $pdo, float $valor): bool {
        if ($valor > 200) {
            return false;
        }
        return parent::levantar($pdo, $valor);
    }
}
