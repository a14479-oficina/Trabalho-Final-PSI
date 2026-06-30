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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="atm-bg">
    <div class="atm-container">
        <div class="atm-screen">
            <div class="atm-header">
                <h1>DevBank</h1>
                <p>Pagamento de Serviços</p>
            </div>
            <div class="atm-body">
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
</body>
</html>
