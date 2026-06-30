<?php
require_once __DIR__ . '/Utilizador.php';

class Admin extends Utilizador
{
    public function __construct(string $nome, string $email, string $palavraPasse)
    {
        parent::__construct($nome, $email, $palavraPasse, 'admin');
    }

    public function criarCliente(PDO $db, string $nome, string $nif, string $email, string $palavraPasse): bool
    {
        $hash = password_hash($palavraPasse, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador) VALUES (:nome, :nif, :email, :palavra_passe, 'cliente')");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nif', $nif);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':palavra_passe', $hash);
        return $stmt->execute();
    }

    public function salvar(PDO $db): bool
    {
        $hash = password_hash($this->palavraPasse, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO utilizadores (nome, email, palavra_passe, tipo_utilizador) VALUES (:nome, :email, :palavra_passe, 'admin')");
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':palavra_passe', $hash);
        return $stmt->execute();
    }

    public function eliminarCliente(PDO $db, int $clienteId): bool
    {
        $stmt = $db->prepare("DELETE FROM utilizadores WHERE id = :id AND tipo_utilizador = 'cliente'");
        $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
