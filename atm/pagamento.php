<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

$pdo     = Database::getConexao();
$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entidade   = trim($_POST['entidade'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');
    $valor      = str_replace(',', '.', $_POST['valor'] ?? '');
    $valor      = (float) $valor;

    if ($entidade === '' || $referencia === '' || $valor <= 0) {
        $erro = 'Preencha Entidade, Referência e Valor.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE contas SET saldo = saldo - :valor WHERE id = :id AND saldo >= :valor2"
            );
            $stmt->execute([
                ':valor'  => $valor,
                ':id'     => $_SESSION['conta_id'],
                ':valor2' => $valor
            ]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                $erro = 'Saldo insuficiente.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor)
                     VALUES (:origem, NULL, 'transferencia', :valor)"
                );
                $stmt->execute([
                    ':origem' => $_SESSION['conta_id'],
                    ':valor'  => $valor
                ]);

                $pdo->commit();
                $sucesso = "Pagamento de " . number_format($valor, 2, ',', ' ') .
                           " € à Entidade $entidade / Ref. $referencia realizado.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = 'Erro ao processar pagamento.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Pagamento | DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>Pagamento de Serviços</h1>
        </div>

        <?php if ($sucesso): ?>
            <div class="atm-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="atm-form">
            <div class="atm-input-group">
                <label for="entidade">Entidade</label>
                <input type="text" id="entidade" name="entidade" placeholder="Ex: 12345" required>
            </div>
            <div class="atm-input-group">
                <label for="referencia">Referência</label>
                <input type="text" id="referencia" name="referencia" placeholder="Ex: 987654321" required>
            </div>
            <div class="atm-input-group">
                <label for="valor">Valor (€)</label>
                <input type="text" id="valor" name="valor" placeholder="0,00" inputmode="decimal" required>
            </div>
            <button type="submit" class="atm-btn">Pagar</button>
        </form>

        <div class="atm-menu">
            <a href="menu.php" class="atm-menu-btn">&#9664; Voltar</a>
        </div>
    </div>
</body>
</html>
