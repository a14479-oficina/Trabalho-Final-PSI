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

$stmt = $pdo->prepare(
    "SELECT id, tipo_transacao, valor, data_movimento,
            conta_origem_id, conta_destino_id
     FROM transacoes
     WHERE conta_origem_id = :id OR conta_destino_id = :id
     ORDER BY data_movimento DESC
     LIMIT 5"
);
$stmt->execute([':id' => $_SESSION['conta_id']]);
$movimentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Saldo e Movimentos | DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>Saldo e Movimentos</h1>
        </div>

        <div class="atm-balance-box">
            Saldo Atual: <strong><?= number_format($saldo, 2, ',', ' ') ?> €</strong>
        </div>

        <h3 class="atm-subtitle">Últimos 5 Movimentos</h3>

        <?php if (count($movimentos) === 0): ?>
            <p class="atm-info">Nenhum movimento registado.</p>
        <?php else: ?>
            <div class="atm-transactions">
                <?php foreach ($movimentos as $m): ?>
                    <div class="atm-trans-item">
                        <span class="trans-tipo"><?= ucfirst($m['tipo_transacao']) ?></span>
                        <span class="trans-valor <?= ($m['conta_origem_id'] == $_SESSION['conta_id']) ? 'trans-debito' : 'trans-credito' ?>">
                            <?= number_format($m['valor'], 2, ',', ' ') ?> €
                        </span>
                        <span class="trans-data"><?= date('d/m/Y H:i', strtotime($m['data_movimento'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="atm-menu">
            <a href="menu.php" class="atm-menu-btn">&#9664; Voltar</a>
        </div>
    </div>
</body>
</html>
