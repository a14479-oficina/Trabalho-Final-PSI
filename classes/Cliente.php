<?php

require_once __DIR__ . '/Utilizador.php';

class Cliente extends Utilizador
{
    public function __construct(int $id, string $nome, ?string $nif, string $email, string $palavra_passe, string $tipo_utilizador)
    {
        parent::__construct($id, $nome, $nif, $email, $palavra_passe, $tipo_utilizador);
    }

    public static function listarTodos(): array
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM utilizadores WHERE tipo_utilizador = 'cliente' ORDER BY nome"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM utilizadores WHERE id = :id AND tipo_utilizador = 'cliente'"
        );
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();
        return $dados ?: null;
    }
}
