<?php

class Cartao
{
    private int $id;
    private int $conta_id;
    private string $numero_cartao;
    private string $pin_encriptado;
    private string $estado;
    private string $validade;

    public function __construct(int $id, int $conta_id, string $numero_cartao, string $pin_encriptado, string $estado, string $validade)
    {
        $this->id = $id;
        $this->conta_id = $conta_id;
        $this->numero_cartao = $numero_cartao;
        $this->pin_encriptado = $pin_encriptado;
        $this->estado = $estado;
        $this->validade = $validade;
    }

    public function getId(): int { return $this->id; }
    public function getContaId(): int { return $this->conta_id; }
    public function getNumeroCartao(): string { return $this->numero_cartao; }
    public function getEstado(): string { return $this->estado; }
    public function isAtivo(): bool { return $this->estado === 'ativo'; }

    public function validarPin(string $pin): bool
    {
        return password_verify($pin, $this->pin_encriptado);
    }

    public static function buscarPorNumero(string $numero_cartao): ?self
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM cartoes WHERE numero_cartao = :numero_cartao AND estado = 'ativo'");
        $stmt->execute([':numero_cartao' => $numero_cartao]);
        $dados = $stmt->fetch();

        if (!$dados) return null;

        return new self(
            (int)$dados['id'],
            (int)$dados['conta_id'],
            $dados['numero_cartao'],
            $dados['pin_encriptado'],
            $dados['estado'],
            $dados['validade']
        );
    }

    public static function gerarNumerosCartao(): string
    {
        $numero = '';
        for ($i = 0; $i < 16; $i++) {
            $numero .= random_int(0, 9);
        }
        return $numero;
    }
}
