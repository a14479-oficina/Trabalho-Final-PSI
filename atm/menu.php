<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

$pdo = Database::getConexao();
$stmt = $pdo->prepare("SELECT saldo FROM contas WHERE id = :id");
$stmt->execute([':id' => $_SESSION['conta_id']]);
$saldo = (float) $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Menu | Multibanco DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>Bem-vindo(a)</h1>
            <p><?= htmlspecialchars($_SESSION['cliente_nome']) ?></p>
        </div>

        <div class="atm-balance-box">
            Saldo Disponível: <strong><?= number_format($saldo, 2, ',', ' ') ?> €</strong>
            <br><small>Conta: <?= htmlspecialchars($_SESSION['conta_num']) ?>
                   (<?= $_SESSION['tipo_conta'] ?>)</small>
        </div>

        <div class="atm-menu">
            <a href="saldo.php" class="atm-menu-btn">
                <span class="menu-icon">&#9654;</span> Consultar Saldo / Movimentos
            </a>
            <a href="levantamento.php" class="atm-menu-btn">
                <span class="menu-icon">&#9654;</span> Levantamento
            </a>
            <a href="pagamento.php" class="atm-menu-btn">
                <span class="menu-icon">&#9654;</span> Pagamento de Serviços
            </a>
            <a href="transferencia.php" class="atm-menu-btn">
                <span class="menu-icon">&#9654;</span> Transferência
            </a>
            <a href="sair.php" class="atm-menu-btn atm-menu-exit">
                <span class="menu-icon">&#9632;</span> Sair
            </a>
        </div>
    </div>
</body>
</html>
