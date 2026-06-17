<?php
trait HistoricoTrait {
    public function registarTransacao(
        PDO $pdo,
        $contaOrigemId,
        $contaDestinoId,
        string $tipo,
        float $valor
    ): bool {
        $stmt = $pdo->prepare(
            "INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor)
             VALUES (:origem, :destino, :tipo, :valor)"
        );
        return $stmt->execute([
            ':origem'  => $contaOrigemId,
            ':destino' => $contaDestinoId,
            ':tipo'    => $tipo,
            ':valor'   => $valor
        ]);
    }
}
