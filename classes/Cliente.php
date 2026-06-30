<?php
require_once __DIR__ . '/Utilizador.php';

class Cliente extends Utilizador
{
    private ?string $nif;

    public function __construct(string $nome, string $email, string $palavraPasse, ?string $nif = null)
    {
        parent::__construct($nome, $email, $palavraPasse, 'cliente');
        $this->nif = $nif;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function salvar(PDO $db): bool
    {
        $hash = password_hash($this->palavraPasse, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador) VALUES (:nome, :nif, :email, :palavra_passe, 'cliente')");
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':nif', $this->nif);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':palavra_passe', $hash);
        return $stmt->execute();
    }
}
