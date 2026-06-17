<?php
require_once __DIR__ . '/Utilizador.php';

class Cliente extends Utilizador {
    public function __construct(array $dados = []) {
        parent::__construct($dados);
    }

    public function getTipo(): string {
        return 'cliente';
    }

    public function getContas(PDO $pdo): array {
        $stmt = $pdo->prepare("SELECT * FROM contas WHERE utilizador_id = :id");
        $stmt->execute([':id' => $this->id]);
        return $stmt->fetchAll();
    }

    public function getCartoes(PDO $pdo): array {
        $stmt = $pdo->prepare(
            "SELECT c.* FROM cartoes c
             JOIN contas co ON c.conta_id = co.id
             WHERE co.utilizador_id = :id"
        );
        $stmt->execute([':id' => $this->id]);
        return $stmt->fetchAll();
    }
}
