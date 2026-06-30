<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Conta.php';
require_once __DIR__ . '/../classes/HistoricoTrait.php';

$db = Database::conectar();
$contaId = $_SESSION['conta_id'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroDestino = trim($_POST['numero_conta'] ?? '');
    $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
    $valor = (float)$valor;

    if (empty($numeroDestino) || $valor <= 0) {
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        try {
            $db->beginTransaction();

            $stmtOrigem = $db->prepare("SELECT id, saldo FROM contas WHERE id = :id FOR UPDATE");
            $stmtOrigem->bindParam(':id', $contaId, PDO::PARAM_INT);
            $stmtOrigem->execute();
            $contaOrigem = $stmtOrigem->fetch(PDO::FETCH_ASSOC);

            if (!$contaOrigem || $contaOrigem['saldo'] < $valor) {
                $db->rollBack();
                $erro = 'Saldo insuficiente para realizar a transferência.';
            } else {
                $stmtDestino = $db->prepare("SELECT id FROM contas WHERE numero_conta = :numero FOR UPDATE");
                $stmtDestino->bindParam(':numero', $numeroDestino);
                $stmtDestino->execute();
                $contaDestino = $stmtDestino->fetch(PDO::FETCH_ASSOC);

                if (!$contaDestino) {
                    $db->rollBack();
                    $erro = 'Conta de destino não encontrada.';
                } elseif ($contaDestino['id'] == $contaId) {
                    $db->rollBack();
                    $erro = 'Não pode transferir para a mesma conta.';
                } else {
                    $stmtUpdOrigem = $db->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :id");
                    $stmtUpdOrigem->bindParam(':valor', $valor);
                    $stmtUpdOrigem->bindParam(':id', $contaId, PDO::PARAM_INT);
                    $stmtUpdOrigem->execute();

                    $stmtUpdDestino = $db->prepare("UPDATE contas SET saldo = saldo + :valor WHERE id = :id");
                    $stmtUpdDestino->bindParam(':valor', $valor);
                    $stmtUpdDestino->bindParam(':id', $contaDestino['id'], PDO::PARAM_INT);
                    $stmtUpdDestino->execute();

                    $stmtTrans = $db->prepare("INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES (:origem, :destino, 'transferencia', :valor)");
                    $stmtTrans->bindParam(':origem', $contaId, PDO::PARAM_INT);
                    $stmtTrans->bindParam(':destino', $contaDestino['id'], PDO::PARAM_INT);
                    $stmtTrans->bindParam(':valor', $valor);
                    $stmtTrans->execute();

                    $db->commit();
                    $sucesso = 'Transferência de ' . number_format($valor, 2, ',', ' ') . ' € realizada com sucesso!';
                }
            }
        } catch (Exception $e) {
            $db->rollBack();
            $erro = 'Erro ao processar transferência: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Transferências</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-[#0a1628] via-[#0f1f3d] to-[#162d50] min-h-screen flex items-center justify-center p-4">
    <div class="atm-machine">
        <div class="atm-brand">DevBank</div>
        <div class="atm-screen-border">
            <div class="atm-screen-inner">
                <div class="text-center mb-6 pb-4 border-b border-white/5">
                    <h1 class="text-white text-lg font-bold">DevBank</h1>
                    <p class="text-white/30 text-xs mt-1">Transferências</p>
                </div>
                <div class="min-h-[200px]">
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                        <a href="menu.php" class="btn btn-atm">Voltar ao Menu</a>
                    <?php else: ?>
                        <?php if ($erro): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="numero_conta">Número da Conta de Destino</label>
                                <input type="text" id="numero_conta" name="numero_conta" class="atm-input" placeholder="PT5000010001234567890" required>
                            </div>
                            <div class="form-group">
                                <label for="valor">Valor (€)</label>
                                <input type="text" id="valor" name="valor" class="atm-input" placeholder="0.00" required>
                            </div>
                            <button type="submit" class="btn btn-atm">Transferir</button>
                        </form>
                        <a href="menu.php" class="btn btn-atm btn-atm-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="atm-bottom">
            <div class="atm-btn-side">
                <span></span><span></span><span></span>
            </div>
            <div class="atm-card-slot">
                <div class="atm-slot"></div>
                <span class="atm-slot-label">Leitor de Cartão</span>
            </div>
            <div class="atm-btn-side">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>
</body>
</html>
