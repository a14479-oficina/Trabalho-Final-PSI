<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo     = Database::getConexao();
$mensagem = '';
$erro     = '';

$clientes = $pdo->query(
    "SELECT id, nome, email FROM utilizadores WHERE tipo_utilizador = 'cliente' ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

function gerarNumeroConta(PDO $pdo): string {
    do {
        $numero = 'PT50' . str_pad(random_int(0, 999999999999999), 15, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contas WHERE numero_conta = :num");
        $stmt->execute([':num' => $numero]);
    } while ($stmt->fetchColumn() > 0);
    return $numero;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = (int) ($_POST['cliente_id'] ?? 0);
    $tipoConta = $_POST['tipo_conta'] ?? '';

    if ($clienteId <= 0 || !in_array($tipoConta, ['corrente', 'poupanca'])) {
        $erro = 'Selecione um cliente e um tipo de conta válido.';
    } else {
        try {
            $numero = gerarNumeroConta($pdo);
            $stmt = $pdo->prepare(
                "INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo)
                 VALUES (:uid, :num, :tipo, 0.00)"
            );
            $stmt->execute([
                ':uid'  => $clienteId,
                ':num'  => $numero,
                ':tipo' => $tipoConta
            ]);
            $mensagem = "Conta $tipoConta número $numero criada com sucesso!";
        } catch (Exception $e) {
            $erro = 'Erro ao criar conta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Abrir Conta | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">DevBank — Abrir Conta</span>
            <a href="dashboard.php" class="text-white text-decoration-none">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4" style="max-width: 550px;">
        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header">Nova Conta Bancária</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <option value="">— Selecione —</option>
                            <?php foreach ($clientes as $cli): ?>
                                <option value="<?= $cli['id'] ?>">
                                    <?= htmlspecialchars($cli['nome']) ?> (<?= htmlspecialchars($cli['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Conta</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_conta" value="corrente" id="tc_corrente" checked>
                            <label class="form-check-label" for="tc_corrente">Corrente</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_conta" value="poupanca" id="tc_poupanca">
                            <label class="form-check-label" for="tc_poupanca">Poupança</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Criar Conta</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
