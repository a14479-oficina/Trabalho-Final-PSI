<?php
require_once __DIR__ . '/Utilizador.php';

class Admin extends Utilizador {
    public function __construct(array $dados = []) {
        parent::__construct($dados);
    }

    public function getTipo(): string {
        return 'admin';
    }

    public function criarCliente(PDO $pdo, array $dados): int {
        $stmt = $pdo->prepare(
            "INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador)
             VALUES (:nome, :nif, :email, :palavra_passe, 'cliente')"
        );
        $stmt->execute([
            ':nome'          => $dados['nome'],
            ':nif'           => $dados['nif'] ?? null,
            ':email'         => $dados['email'],
            ':palavra_passe' => password_hash($dados['palavra_passe'], PASSWORD_DEFAULT)
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function login(PDO $pdo, string $email, string $palavraPasse): ?self {
        $stmt = $pdo->prepare(
            "SELECT * FROM utilizadores WHERE email = :email AND tipo_utilizador = 'admin'"
        );
        $stmt->execute([':email' => $email]);
        $dados = $stmt->fetch();

        if ($dados && password_verify($palavraPasse, $dados['palavra_passe'])) {
            return new self($dados);
        }
        return null;
    }
}
