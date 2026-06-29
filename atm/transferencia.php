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
    $conta_destino_numero = $_POST['conta_destino'] ?? '';
    $valor = (float) ($_POST['valor'] ?? 0);

    if ($valor <= 0 || empty($conta_destino_numero)) {
        $erro = 'Preencha todos os campos corretamente.';
    } elseif ($conta->getSaldo() < $valor) {
        $erro = 'Saldo insuficiente.';
    } elseif ($conta_destino_numero === $conta->getNumeroConta()) {
        $erro = 'Não pode transferir para a mesma conta.';
    } else {
        $conta_destino = Conta::buscarPorNumero($conta_destino_numero);
        if (!$conta_destino) {
            $erro = 'Conta de destino não encontrada.';
        } else {
            $db = Database::getConnection();
            $db->beginTransaction();
            try {
                $conta->debitar($valor);
                $conta_destino->creditar($valor);

                $conta->registarTransacao(
                    $conta->getId(),
                    'transferencia',
                    $valor,
                    $conta_destino->getId()
                );

                $db->commit();
                $sucesso = 'Transferência de <strong>' . number_format($valor, 2, ',', '.') . ' €</strong> realizada com sucesso!';
                $conta = Conta::buscarPorId($_SESSION['conta_id']);
            } catch (\Exception $e) {
                $db->rollBack();
                $erro = 'Erro ao processar transferência.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Transferência</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Saldo Disponível: <strong><?= number_format($conta->getSaldo(), 2, ',', '.') ?> €</strong></p>
        </div>

        <div class="atm-display">
            <h2>Transferência</h2>

            <?php if ($sucesso): ?>
                <div class="atm-success"><?= $sucesso ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if (!$sucesso): ?>
            <form method="POST" class="atm-form">
                <div class="atm-field">
                    <label>Conta de Destino</label>
                    <input type="text" name="conta_destino" placeholder="PT000000000000" required>
                </div>
                <div class="atm-field">
                    <label>Valor (€)</label>
                    <input type="number" name="valor" step="0.01" min="0.01" required>
                </div>
                <button type="submit" class="atm-btn">Transferir</button>
            </form>
            <?php endif; ?>

            <a href="menu.php" class="atm-btn atm-btn-secondary">Voltar ao Menu</a>
        </div>

        <div class="atm-footer">
            <p>Transferências entre contas DevBank</p>
        </div>
    </div>
</body>
</html>
