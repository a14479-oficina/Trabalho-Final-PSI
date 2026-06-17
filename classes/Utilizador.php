<?php
abstract class Utilizador {
    protected $id;
    protected $nome;
    protected $nif;
    protected $email;
    protected $palavraPasse;
    protected $tipoUtilizador;

    public function __construct(array $dados = []) {
        $this->id             = $dados['id'] ?? null;
        $this->nome           = $dados['nome'] ?? '';
        $this->nif            = $dados['nif'] ?? null;
        $this->email          = $dados['email'] ?? '';
        $this->palavraPasse   = $dados['palavra_passe'] ?? '';
        $this->tipoUtilizador = $dados['tipo_utilizador'] ?? 'cliente';
    }

    public function getId()             { return $this->id; }
    public function getNome()           { return $this->nome; }
    public function getNif()            { return $this->nif; }
    public function getEmail()          { return $this->email; }
    public function getTipoUtilizador() { return $this->tipoUtilizador; }

    abstract public function getTipo(): string;
}
