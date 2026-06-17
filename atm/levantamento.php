<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

$pdo    = Database::getConexao();
$erro   = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = str_replace(',', '.', $_POST['valor'] ?? '');
    $valor = (float) $valor;

    $stmt = $pdo->prepare("SELECT * FROM contas WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['conta_id']]);
    $dadosConta = $stmt->fetch();

    if ($_SESSION['tipo_conta'] === 'poupanca') {
        $conta = new ContaPoupanca($dadosConta);
    } else {
        $conta = new ContaCorrente($dadosConta);
    }

    if ($valor <= 0) {
        $erro = 'Valor inválido.';
    } elseif ($conta->levantar($pdo, $valor)) {
        $sucesso = 'Levantamento de ' . number_format($valor, 2, ',', ' ') . ' € realizado com sucesso!';
    } else {
        if ($_SESSION['tipo_conta'] === 'poupanca' && $valor > 200) {
            $erro = 'Conta Poupança: levantamento máximo de 200,00 € por operação.';
        } else {
            $erro = 'Saldo insuficiente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Levantamento | DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>Levantamento</h1>
        </div>

        <?php if ($sucesso): ?>
            <div class="atm-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="atm-form">
            <div class="atm-input-group">
                <label for="valor">Valor (€)</label>
                <input type="text" id="valor" name="valor" placeholder="0,00"
                       inputmode="decimal" required>
            </div>
            <button type="submit" class="atm-btn">Levantar</button>
        </form>

        <div class="atm-menu">
            <a href="menu.php" class="atm-menu-btn">&#9664; Voltar</a>
        </div>
    </div>
</body>
</html>
