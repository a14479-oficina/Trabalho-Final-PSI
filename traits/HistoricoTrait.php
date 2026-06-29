<?php

require_once __DIR__ . '/../config/database.php';

trait HistoricoTrait
{
    public function registarTransacao(
        int $conta_origem_id,
        string $tipo_transacao,
        float $valor,
        ?int $conta_destino_id = null,
        string $descricao = ''
    ): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor)
             VALUES (:conta_origem_id, :conta_destino_id, :tipo_transacao, :valor)"
        );
        return $stmt->execute([
            ':conta_origem_id'  => $conta_origem_id,
            ':conta_destino_id' => $conta_destino_id,
            ':tipo_transacao'   => $tipo_transacao,
            ':valor'            => $valor,
        ]);
    }

    public function obterMovimentos(int $conta_id, int $limite = 5): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT tipo_transacao, valor,
                    CASE
                        WHEN conta_origem_id = :conta_id1 THEN 'saida'
                        ELSE 'entrada'
                    END AS direcao,
                    data_movimento
             FROM transacoes
             WHERE conta_origem_id = :conta_id2 OR conta_destino_id = :conta_id3
             ORDER BY data_movimento DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':conta_id1', $conta_id, PDO::PARAM_INT);
        $stmt->bindValue(':conta_id2', $conta_id, PDO::PARAM_INT);
        $stmt->bindValue(':conta_id3', $conta_id, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
