<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Conta.php';
require_once __DIR__ . '/../traits/HistoricoTrait.php';

$conta = Conta::buscarPorId($_SESSION['conta_id']);

if (!$conta) {
    header('Location: index.php');
    exit;
}

$movimentos = $conta->obterExtrato(5);
$saldo = $conta->getSaldo();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Saldo</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Conta: <?= htmlspecialchars($conta->getNumeroConta()) ?></p>
        </div>

        <div class="atm-display">
            <h2>Consulta de Saldo</h2>

            <div class="saldo-box">
                <span class="saldo-label">Saldo Atual</span>
                <span class="saldo-valor"><?= number_format($saldo, 2, ',', '.') ?> €</span>
            </div>

            <h3>Últimos Movimentos</h3>

            <div class="movimentos-list">
                <?php if (empty($movimentos)): ?>
                    <p class="text-center">Nenhum movimento registado.</p>
                <?php else: ?>
                    <?php foreach ($movimentos as $mov): ?>
                    <div class="movimento-item">
                        <div class="movimento-info">
                            <span class="movimento-tipo"><?= ucfirst($mov['tipo_transacao']) ?></span>
                            <span class="movimento-data"><?= date('d/m/Y H:i', strtotime($mov['data_movimento'])) ?></span>
                        </div>
                        <div class="movimento-valor <?= $mov['direcao'] === 'saida' ? 'negativo' : 'positivo' ?>">
                            <?= $mov['direcao'] === 'saida' ? '-' : '+' ?>
                            <?= number_format($mov['valor'], 2, ',', '.') ?> €
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <a href="menu.php" class="atm-btn">Voltar</a>
        </div>

        <div class="atm-footer">
            <p>Devolução do cartão em curso...</p>
        </div>
    </div>
</body>
</html>
