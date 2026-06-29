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

$sucesso = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = (float) ($_POST['valor'] ?? 0);

    if ($valor <= 0) {
        $erro = 'Valor inválido.';
    } elseif (!$conta->podeLevantar($valor)) {
        $erro = 'Não tem saldo suficiente ou excede os limites da conta ' . $conta->getTipo() . '.';
    } else {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $conta->debitar($valor);
            $conta->registarTransacao($conta->getId(), 'levantamento', $valor);
            $db->commit();
            $sucesso = 'Levantamento de <strong>' . number_format($valor, 2, ',', '.') . ' €</strong> realizado com sucesso!';
            $conta = Conta::buscarPorId($_SESSION['conta_id']);
        } catch (\Exception $e) {
            $db->rollBack();
            $erro = 'Erro ao processar levantamento.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Levantamento</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Saldo Disponível: <strong><?= number_format($conta->getSaldo(), 2, ',', '.') ?> €</strong></p>
        </div>

        <div class="atm-display">
            <h2>Levantamento</h2>

            <?php if ($sucesso): ?>
                <div class="atm-success"><?= $sucesso ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if (!$sucesso): ?>
            <form method="POST" class="atm-form">
                <div class="atm-field">
                    <label>Valor a Levantar (€)</label>
                    <input type="number" name="valor" step="0.01" min="1" max="<?= $conta->getSaldo() ?>" required>
                </div>
                <button type="submit" class="atm-btn">Levantar</button>
            </form>
            <?php endif; ?>

            <a href="menu.php" class="atm-btn atm-btn-secondary">Voltar ao Menu</a>
        </div>

        <div class="atm-footer">
            <p>Levantamentos limitados a 500€ por operação (Poupança)</p>
        </div>
    </div>
</body>
</html>
