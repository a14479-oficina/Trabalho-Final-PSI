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
    $entidade = trim($_POST['entidade'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');
    $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
    $valor = (float)$valor;

    if (empty($entidade) || empty($referencia) || $valor <= 0) {
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT saldo FROM contas WHERE id = :id FOR UPDATE");
            $stmt->bindParam(':id', $contaId, PDO::PARAM_INT);
            $stmt->execute();
            $conta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$conta || $conta['saldo'] < $valor) {
                $db->rollBack();
                $erro = 'Saldo insuficiente para realizar o pagamento.';
            } else {
                $stmtUpd = $db->prepare("UPDATE contas SET saldo = saldo - :valor WHERE id = :id");
                $stmtUpd->bindParam(':valor', $valor);
                $stmtUpd->bindParam(':id', $contaId, PDO::PARAM_INT);
                $stmtUpd->execute();

                $descricao = 'Pagamento - Ent: ' . $entidade . ' Ref: ' . $referencia;
                $stmtTrans = $db->prepare("INSERT INTO transacoes (conta_origem_id, tipo_transacao, valor) VALUES (:id, 'transferencia', :valor)");
                $stmtTrans->bindParam(':id', $contaId, PDO::PARAM_INT);
                $stmtTrans->bindParam(':valor', $valor);
                $stmtTrans->execute();

                $db->commit();
                $sucesso = 'Pagamento no valor de ' . number_format($valor, 2, ',', ' ') . ' € realizado com sucesso!';
            }
        } catch (Exception $e) {
            $db->rollBack();
            $erro = 'Erro ao processar pagamento: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Pagamentos</title>
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
                    <p class="text-white/30 text-xs mt-1">Pagamento de Serviços</p>
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
                                <label for="entidade">Entidade</label>
                                <input type="text" id="entidade" name="entidade" class="atm-input" placeholder="12345" required maxlength="5">
                            </div>
                            <div class="form-group">
                                <label for="referencia">Referência</label>
                                <input type="text" id="referencia" name="referencia" class="atm-input" placeholder="123456789" required>
                            </div>
                            <div class="form-group">
                                <label for="valor">Valor (€)</label>
                                <input type="text" id="valor" name="valor" class="atm-input" placeholder="0.00" required>
                            </div>
                            <button type="submit" class="btn btn-atm">Pagar</button>
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
