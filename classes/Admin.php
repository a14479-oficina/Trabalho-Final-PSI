<?php

require_once __DIR__ . '/Utilizador.php';

class Admin extends Utilizador
{
    public function __construct(int $id, string $nome, ?string $nif, string $email, string $palavra_passe, string $tipo_utilizador)
    {
        parent::__construct($id, $nome, $nif, $email, $palavra_passe, $tipo_utilizador);
    }

    public function criarCliente(array $dados): int
    {
        $db = \Database::getConnection();
        $palavra_passe = password_hash($dados['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            "INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador)
             VALUES (:nome, :nif, :email, :palavra_passe, 'cliente')"
        );
        $stmt->execute([
            ':nome'          => $dados['nome'],
            ':nif'           => $dados['nif'] ?? null,
            ':email'         => $dados['email'],
            ':palavra_passe' => $palavra_passe,
        ]);

        return (int) $db->lastInsertId();
    }
}
