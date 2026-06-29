<?php

require_once __DIR__ . '/Conta.php';

final class ContaPoupanca extends Conta
{
    private const LIMITE_POR_LEVANTAMENTO = 500.00;
    private const LIMITE_DIARIO = 1000.00;

    public function podeLevantar(float $valor): bool
    {
        if ($valor > self::LIMITE_POR_LEVANTAMENTO) {
            return false;
        }

        $totalHoje = $this->totalLevantamentosHoje();
        if (($totalHoje + $valor) > self::LIMITE_DIARIO) {
            return false;
        }

        return $this->saldo >= $valor;
    }

    private function totalLevantamentosHoje(): float
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM transacoes
             WHERE conta_origem_id = :conta_id AND tipo_transacao = 'levantamento' AND DATE(data_movimento) = CURRENT_DATE"
        );
        $stmt->execute([':conta_id' => $this->id]);
        return (float) $stmt->fetchColumn();
    }
}
