<?php
abstract class Utilizador
{
    protected int $id;
    protected string $nome;
    protected string $email;
    protected string $palavraPasse;
    protected string $tipoUtilizador;

    public function __construct(string $nome, string $email, string $palavraPasse, string $tipoUtilizador = 'cliente')
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->palavraPasse = $palavraPasse;
        $this->tipoUtilizador = $tipoUtilizador;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTipoUtilizador(): string
    {
        return $this->tipoUtilizador;
    }

    abstract public function salvar(PDO $db): bool;
}
