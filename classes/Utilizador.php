<?php

abstract class Utilizador
{
    protected int $id;
    protected string $nome;
    protected ?string $nif;
    protected string $email;
    protected string $palavra_passe;
    protected string $tipo_utilizador;

    public function __construct(int $id, string $nome, ?string $nif, string $email, string $palavra_passe, string $tipo_utilizador)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->nif = $nif;
        $this->email = $email;
        $this->palavra_passe = $palavra_passe;
        $this->tipo_utilizador = $tipo_utilizador;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getNif(): ?string { return $this->nif; }
    public function getEmail(): string { return $this->email; }
    public function getTipo(): string { return $this->tipo_utilizador; }

    public function verificarPassword(string $password): bool
    {
        return password_verify($password, $this->palavra_passe);
    }

    public static function login(string $email, string $password): ?static
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM utilizadores WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $dados = $stmt->fetch();

        if (!$dados || !password_verify($password, $dados['palavra_passe'])) {
            return null;
        }

        $class = $dados['tipo_utilizador'] === 'admin' ? Admin::class : Cliente::class;
        return new $class(
            $dados['id'],
            $dados['nome'],
            $dados['nif'],
            $dados['email'],
            $dados['palavra_passe'],
            $dados['tipo_utilizador']
        );
    }
}
