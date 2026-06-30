<?php
require_once __DIR__ . '/HistoricoTrait.php';

class Conta
{
    use HistoricoTrait;

    protected int $id;
    protected int $utilizadorId;
    protected string $numeroConta;
    protected string $tipoConta;
    protected float $saldo;

    public function __construct(int $utilizadorId, string $numeroConta, string $tipoConta, float $saldo = 0.00)
    {
        $this->utilizadorId = $utilizadorId;
        $this->numeroConta = $numeroConta;
        $this->tipoConta = $tipoConta;
        $this->saldo = $saldo;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUtilizadorId(): int
    {
        return $this->utilizadorId;
    }

    public function getNumeroConta(): string
    {
        return $this->numeroConta;
    }

    public function getTipoConta(): string
    {
        return $this->tipoConta;
    }

    public function getSaldo(): float
    {
        return $this->saldo;
    }

    public function setSaldo(float $saldo): void
    {
        $this->saldo = $saldo;
    }

    public function salvar(PDO $db): bool
    {
        $stmt = $db->prepare("INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo) VALUES (:utilizador_id, :numero_conta, :tipo_conta, :saldo)");
        $stmt->bindParam(':utilizador_id', $this->utilizadorId, PDO::PARAM_INT);
        $stmt->bindParam(':numero_conta', $this->numeroConta);
        $stmt->bindParam(':tipo_conta', $this->tipoConta);
        $stmt->bindParam(':saldo', $this->saldo);
        return $stmt->execute();
    }

    public function levantar(PDO $db, float $valor): bool
    {
        if ($valor <= 0) {
            return false;
        }

        $stmt = $db->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :id AND saldo >= :valor2");
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':valor2', $valor);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $this->saldo -= $valor;
        $this->registrarTransacao($db, $this->id, null, 'levantamento', $valor);
        return true;
    }

    public function depositar(PDO $db, float $valor): bool
    {
        if ($valor <= 0) {
            return false;
        }

        $stmt = $db->prepare("UPDATE contas SET saldo = saldo + :valor WHERE id = :id");
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $this->saldo += $valor;
        $this->registrarTransacao($db, null, $this->id, 'deposito', $valor);
        return true;
    }
}
