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
    $contaDestino = trim($_POST['conta_destino'] ?? '');
    $valor        = str_replace(',', '.', $_POST['valor'] ?? '');
    $valor        = (float) $valor;

    if ($contaDestino === '' || $valor <= 0) {
        $erro = 'Preencha o número da conta de destino e o valor.';
    } elseif ($contaDestino === $_SESSION['conta_num']) {
        $erro = 'Não pode transferir para a mesma conta.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM contas WHERE numero_conta = :num");
            $stmt->execute([':num' => $contaDestino]);
            $destino = $stmt->fetch();

            if (!$destino) {
                $erro = 'Conta de destino não encontrada.';
            } else {
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
                        "UPDATE contas SET saldo = saldo + :valor WHERE id = :id"
                    );
                    $stmt->execute([
                        ':valor' => $valor,
                        ':id'    => $destino['id']
                    ]);

                    $stmt = $pdo->prepare(
                        "INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor)
                         VALUES (:origem, :destino, 'transferencia', :valor)"
                    );
                    $stmt->execute([
                        ':origem'  => $_SESSION['conta_id'],
                        ':destino' => $destino['id'],
                        ':valor'   => $valor
                    ]);

                    $pdo->commit();
                    $sucesso = "Transferência de " . number_format($valor, 2, ',', ' ') .
                               " € para conta $contaDestino realizada com sucesso!";
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = 'Erro ao processar transferência.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Transferência | DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>Transferência</h1>
        </div>

        <?php if ($sucesso): ?>
            <div class="atm-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="atm-form">
            <div class="atm-input-group">
                <label for="conta_destino">Conta de Destino</label>
                <input type="text" id="conta_destino" name="conta_destino"
                       placeholder="Ex: PT5000010001234567890" required>
            </div>
            <div class="atm-input-group">
                <label for="valor">Valor (€)</label>
                <input type="text" id="valor" name="valor" placeholder="0,00" inputmode="decimal" required>
            </div>
            <button type="submit" class="atm-btn">Transferir</button>
        </form>

        <div class="atm-menu">
            <a href="menu.php" class="atm-menu-btn">&#9664; Voltar</a>
        </div>
    </div>
</body>
</html>
