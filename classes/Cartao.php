<?php
class Cartao
{
    private int $id;
    private int $contaId;
    private string $numeroCartao;
    private string $pinEncriptado;
    private string $estado;
    private string $validade;

    public function __construct(int $contaId, string $numeroCartao, string $pin, string $validade)
    {
        $this->contaId = $contaId;
        $this->numeroCartao = $numeroCartao;
        $this->pinEncriptado = password_hash($pin, PASSWORD_DEFAULT);
        $this->estado = 'ativo';
        $this->validade = $validade;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContaId(): int
    {
        return $this->contaId;
    }

    public function getNumeroCartao(): string
    {
        return $this->numeroCartao;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getValidade(): string
    {
        return $this->validade;
    }

    public function salvar(PDO $db): bool
    {
        $stmt = $db->prepare("INSERT INTO cartoes (conta_id, numero_cartao, pin_encriptado, estado, validade) VALUES (:conta_id, :numero_cartao, :pin_encriptado, :estado, :validade)");
        $stmt->bindParam(':conta_id', $this->contaId, PDO::PARAM_INT);
        $stmt->bindParam(':numero_cartao', $this->numeroCartao);
        $stmt->bindParam(':pin_encriptado', $this->pinEncriptado);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':validade', $this->validade);
        return $stmt->execute();
    }

    public static function gerarNumeroCartao(PDO $db): string
    {
        do {
            $numero = '';
            for ($i = 0; $i < 16; $i++) {
                $numero .= random_int(0, 9);
            }
            $stmt = $db->prepare("SELECT COUNT(*) FROM cartoes WHERE numero_cartao = :numero");
            $stmt->bindParam(':numero', $numero);
            $stmt->execute();
        } while ($stmt->fetchColumn() > 0);

        return $numero;
    }

    public static function gerarNumeroConta(PDO $db): string
    {
        do {
            $numero = 'PT50' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT) . str_pad((string)random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
            $stmt = $db->prepare("SELECT COUNT(*) FROM contas WHERE numero_conta = :numero");
            $stmt->bindParam(':numero', $numero);
            $stmt->execute();
        } while ($stmt->fetchColumn() > 0);

        return $numero;
    }
}
