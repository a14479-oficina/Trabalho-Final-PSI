<?php
require_once __DIR__ . '/HistoricoTrait.php';

class Conta {
    use HistoricoTrait;

    protected $id;
    protected $utilizadorId;
    protected $numeroConta;
    protected $tipoConta;
    protected $saldo;

    public function __construct(array $dados = []) {
        $this->id           = $dados['id'] ?? null;
        $this->utilizadorId = $dados['utilizador_id'] ?? null;
        $this->numeroConta  = $dados['numero_conta'] ?? '';
        $this->tipoConta    = $dados['tipo_conta'] ?? '';
        $this->saldo        = (float) ($dados['saldo'] ?? 0);
    }

    public function getId()           { return $this->id; }
    public function getUtilizadorId() { return $this->utilizadorId; }
    public function getNumeroConta()  { return $this->numeroConta; }
    public function getTipoConta()    { return $this->tipoConta; }
    public function getSaldo()        { return $this->saldo; }

    public function atualizarSaldo(PDO $pdo): void {
        $stmt = $pdo->prepare("SELECT saldo FROM contas WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        $this->saldo = (float) $stmt->fetchColumn();
    }

    public function levantar(PDO $pdo, float $valor): bool {
        if ($valor <= 0 || $valor > $this->saldo) {
            return false;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE contas SET saldo = saldo - :valor WHERE id = :id AND saldo >= :valor2"
            );
            $stmt->execute([
                ':valor'  => $valor,
                ':id'     => $this->id,
                ':valor2' => $valor
            ]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }

            $this->registarTransacao($pdo, $this->id, null, 'levantamento', $valor);
            $pdo->commit();
            $this->saldo -= $valor;
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function depositar(PDO $pdo, float $valor): bool {
        if ($valor <= 0) return false;

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE contas SET saldo = saldo + :valor WHERE id = :id");
            $stmt->execute([':valor' => $valor, ':id' => $this->id]);
            $this->registarTransacao($pdo, null, $this->id, 'deposito', $valor);
            $pdo->commit();
            $this->saldo += $valor;
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
