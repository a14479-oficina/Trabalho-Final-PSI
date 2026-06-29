<?php

require_once __DIR__ . '/../traits/HistoricoTrait.php';

// Not loaded here - loaded lazily in factory() to avoid circular deps

abstract class Conta
{
    use HistoricoTrait;

    protected int $id;
    protected int $utilizador_id;
    protected string $numero_conta;
    protected string $tipo_conta;
    protected float $saldo;

    public function __construct(int $id, int $utilizador_id, string $numero_conta, string $tipo_conta, float $saldo)
    {
        $this->id = $id;
        $this->utilizador_id = $utilizador_id;
        $this->numero_conta = $numero_conta;
        $this->tipo_conta = $tipo_conta;
        $this->saldo = $saldo;
    }

    public function getId(): int { return $this->id; }
    public function getUtilizadorId(): int { return $this->utilizador_id; }
    public function getNumeroConta(): string { return $this->numero_conta; }
    public function getTipo(): string { return $this->tipo_conta; }
    public function getSaldo(): float { return $this->saldo; }

    public static function buscarPorId(int $id): ?self
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM contas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();

        if (!$dados) return null;

        return self::factory($dados);
    }

    public static function buscarPorNumero(string $numero_conta): ?self
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM contas WHERE numero_conta = :numero_conta");
        $stmt->execute([':numero_conta' => $numero_conta]);
        $dados = $stmt->fetch();

        if (!$dados) return null;

        return self::factory($dados);
    }

    public static function listarPorUtilizador(int $utilizador_id): array
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM contas WHERE utilizador_id = :utilizador_id ORDER BY id DESC");
        $stmt->execute([':utilizador_id' => $utilizador_id]);
        $contas = [];
        while ($dados = $stmt->fetch()) {
            $contas[] = self::factory($dados);
        }
        return $contas;
    }

    public static function listarTodas(): array
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare(
            "SELECT c.*, u.nome AS utilizador_nome FROM contas c INNER JOIN utilizadores u ON c.utilizador_id = u.id ORDER BY c.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function factory(array $dados): self
    {
        if ($dados['tipo_conta'] === 'poupanca') {
            require_once __DIR__ . '/ContaPoupanca.php';
            return new ContaPoupanca(
                (int)$dados['id'],
                (int)$dados['utilizador_id'],
                $dados['numero_conta'],
                $dados['tipo_conta'],
                (float)$dados['saldo']
            );
        }
        require_once __DIR__ . '/ContaCorrente.php';
        return new ContaCorrente(
            (int)$dados['id'],
            (int)$dados['utilizador_id'],
            $dados['numero_conta'],
            $dados['tipo_conta'],
            (float)$dados['saldo']
        );
    }

    public function creditar(float $valor): bool
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("UPDATE contas SET saldo = saldo + :valor WHERE id = :id");
        $result = $stmt->execute([':valor' => $valor, ':id' => $this->id]);
        if ($result) {
            $this->saldo += $valor;
        }
        return $result;
    }

    public function debitar(float $valor): bool
    {
        if ($this->saldo < $valor) {
            return false;
        }
        $db = \Database::getConnection();
        $stmt = $db->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :id");
        $result = $stmt->execute([':valor' => $valor, ':id' => $this->id]);
        if ($result) {
            $this->saldo -= $valor;
        }
        return $result;
    }

    public abstract function podeLevantar(float $valor): bool;

    public function obterExtrato(int $limite = 5): array
    {
        return $this->obterMovimentos($this->id, $limite);
    }
}
