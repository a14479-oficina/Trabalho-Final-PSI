<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/Cartao.php';

$clientes = Cliente::listarTodos();
$sucesso = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = (int) $_POST['cliente_id'];
    $tipo_conta = $_POST['tipo_conta'];
    $pin = $_POST['pin'];

    if (!preg_match('/^\d{4}$/', $pin)) {
        $erro = 'O PIN deve ter exatamente 4 dígitos.';
    } else {
        $db = Database::getConnection();
        $numero_conta = 'PT' . str_pad((string)random_int(0, 9999999999), 12, '0', STR_PAD_LEFT);

        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "INSERT INTO contas (utilizador_id, numero_conta, tipo_conta) VALUES (:utilizador_id, :numero_conta, :tipo_conta)"
            );
            $stmt->execute([
                ':utilizador_id' => $cliente_id,
                ':numero_conta'  => $numero_conta,
                ':tipo_conta'    => $tipo_conta,
            ]);
            $conta_id = (int) $db->lastInsertId();

            $numero_cartao = Cartao::gerarNumerosCartao();
            $pin_encriptado = password_hash($pin, PASSWORD_DEFAULT);
            $validade = date('Y-m-d', strtotime('+5 years'));

            $stmt2 = $db->prepare(
                "INSERT INTO cartoes (conta_id, numero_cartao, pin_encriptado, validade) VALUES (:conta_id, :numero_cartao, :pin_encriptado, :validade)"
            );
            $stmt2->execute([
                ':conta_id'       => $conta_id,
                ':numero_cartao'  => $numero_cartao,
                ':pin_encriptado' => $pin_encriptado,
                ':validade'       => $validade,
            ]);

            $db->commit();
            $sucesso = "Conta <strong>{$numero_conta}</strong> criada!<br>Cartão: <strong>{$numero_cartao}</strong><br>PIN: <strong>{$pin}</strong>";
        } catch (\Exception $e) {
            $db->rollBack();
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
    <title>DevBank - Nova Conta</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">DevBank</div>
        <div class="navbar-user">
            <span>Olá, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <h1>Abrir Nova Conta</h1>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= $sucesso ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo_conta">Tipo de Conta *</label>
                <select id="tipo_conta" name="tipo_conta" required>
                    <option value="corrente">Conta Corrente</option>
                    <option value="poupanca">Conta Poupança</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pin">PIN do Cartão (4 dígitos) *</label>
                <input type="text" id="pin" name="pin" maxlength="4" pattern="\d{4}" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Criar Conta</button>
                <a href="contas.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
