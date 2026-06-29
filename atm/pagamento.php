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
    $entidade = $_POST['entidade'] ?? '';
    $referencia = $_POST['referencia'] ?? '';
    $valor = (float) ($_POST['valor'] ?? 0);

    if ($valor <= 0 || empty($entidade) || empty($referencia)) {
        $erro = 'Preencha todos os campos corretamente.';
    } elseif ($conta->getSaldo() < $valor) {
        $erro = 'Saldo insuficiente.';
    } else {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $conta->debitar($valor);
            $conta->registarTransacao($conta->getId(), 'pagamento', $valor);
            $db->commit();
            $sucesso = 'Pagamento de <strong>' . number_format($valor, 2, ',', '.') . ' €</strong> realizado com sucesso!';
            $conta = Conta::buscarPorId($_SESSION['conta_id']);
        } catch (\Exception $e) {
            $db->rollBack();
            $erro = 'Erro ao processar pagamento.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Pagamento</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Saldo Disponível: <strong><?= number_format($conta->getSaldo(), 2, ',', '.') ?> €</strong></p>
        </div>

        <div class="atm-display">
            <h2>Pagamento de Serviços</h2>

            <?php if ($sucesso): ?>
                <div class="atm-success"><?= $sucesso ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if (!$sucesso): ?>
            <form method="POST" class="atm-form">
                <div class="atm-field">
                    <label>Entidade</label>
                    <input type="text" name="entidade" maxlength="10" required>
                </div>
                <div class="atm-field">
                    <label>Referência</label>
                    <input type="text" name="referencia" maxlength="20" required>
                </div>
                <div class="atm-field">
                    <label>Valor (€)</label>
                    <input type="number" name="valor" step="0.01" min="0.01" required>
                </div>
                <button type="submit" class="atm-btn">Pagar</button>
            </form>
            <?php endif; ?>

            <a href="menu.php" class="atm-btn atm-btn-secondary">Voltar ao Menu</a>
        </div>

        <div class="atm-footer">
            <p>Pagamento de serviços - Entidade, Referência e Valor</p>
        </div>
    </div>
</body>
</html>
