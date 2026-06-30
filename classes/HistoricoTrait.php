<?php
trait HistoricoTrait
{
    public function registrarTransacao(PDO $db, ?int $contaOrigemId, ?int $contaDestinoId, string $tipo, float $valor): bool
    {
        $stmt = $db->prepare("INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES (:origem, :destino, :tipo, :valor)");
        $stmt->bindParam(':origem', $contaOrigemId, PDO::PARAM_INT);
        $stmt->bindParam(':destino', $contaDestinoId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':valor', $valor);
        return $stmt->execute();
    }
}
