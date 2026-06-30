<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Cartao.php';

$db = Database::conectar();
$sucesso = '';
$erro = '';

$clienteId = $_GET['cliente_id'] ?? $_POST['cliente_id'] ?? null;

if ($clienteId) {
    $stmt = $db->prepare("SELECT id, nome, email FROM utilizadores WHERE id = :id AND tipo_utilizador = 'cliente'");
    $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $erro = 'Cliente não encontrado.';
        $clienteId = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $clienteId) {
    $tipoConta = $_POST['tipo_conta'] ?? '';

    if (!in_array($tipoConta, ['corrente', 'poupanca'])) {
        $erro = 'Selecione um tipo de conta válido.';
    } else {
        try {
            $numeroConta = Cartao::gerarNumeroConta($db);

            $stmt = $db->prepare("INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo) VALUES (:utilizador_id, :numero_conta, :tipo_conta, 0.00)");
            $stmt->bindParam(':utilizador_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':numero_conta', $numeroConta);
            $stmt->bindParam(':tipo_conta', $tipoConta);
            $stmt->execute();

            $sucesso = 'Conta ' . ($tipoConta === 'corrente' ? 'Corrente' : 'Poupança') . ' criada com sucesso! Número: ' . $numeroConta;
        } catch (PDOException $e) {
            $erro = 'Erro ao criar conta: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Criar Conta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">DevBank - Admin</div>
        <div class="navbar-user">
            <span>Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <h2>Abrir Nova Conta</h2>
        <a href="dashboard.php" class="back-link">&laquo; Voltar ao Dashboard</a>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($clienteId && isset($cliente)): ?>
            <div class="info-card">
                <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['email']) ?>)</p>
            </div>

            <form method="POST" class="form-card">
                <input type="hidden" name="cliente_id" value="<?= $clienteId ?>">
                <div class="form-group">
                    <label for="tipo_conta">Tipo de Conta</label>
                    <select id="tipo_conta" name="tipo_conta" required>
                        <option value="">Selecione...</option>
                        <option value="corrente">Conta Corrente</option>
                        <option value="poupanca">Conta Poupança</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Criar Conta</button>
            </form>
        <?php else: ?>
            <form method="GET" class="form-card">
                <div class="form-group">
                    <label for="cliente_id">ID do Cliente</label>
                    <input type="number" id="cliente_id" name="cliente_id" required>
                </div>
                <button type="submit" class="btn btn-primary">Selecionar Cliente</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
