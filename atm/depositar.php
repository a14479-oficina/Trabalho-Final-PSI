<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Conta.php';

$db = Database::conectar();
$contaId = $_SESSION['conta_id'];
$erro = '';
$sucesso = '';

$stmt = $db->prepare("SELECT id, saldo FROM contas WHERE id = :id");
$stmt->bindParam(':id', $contaId, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

$conta = new Conta(0, '', '');
$conta->setId((int)$dados['id']);
$conta->setSaldo((float)$dados['saldo']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
    $valor = (float)$valor;

    if ($valor <= 0) {
        $erro = 'Insira um valor válido.';
    } else {
        try {
            $db->beginTransaction();

            if ($conta->depositar($db, $valor)) {
                $db->commit();
                $sucesso = 'Depósito de ' . number_format($valor, 2, ',', ' ') . ' € realizado com sucesso!';
            } else {
                $db->rollBack();
                $erro = 'Erro ao realizar depósito.';
            }
        } catch (Exception $e) {
            $db->rollBack();
            $erro = 'Erro ao processar depósito: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Depósito</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="atm-bg">
    <div class="atm-container">
        <div class="atm-screen">
            <div class="atm-header">
                <h1>DevBank</h1>
                <p>Depósito</p>
            </div>
            <div class="atm-body">
                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <a href="menu.php" class="btn btn-atm">Voltar ao Menu</a>
                <?php else: ?>
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    <p>Saldo atual: <strong><?= number_format($conta->getSaldo(), 2, ',', ' ') ?> €</strong></p>
                    <form method="POST">
                        <div class="form-group">
                            <label for="valor">Valor a depositar (€)</label>
                            <input type="text" id="valor" name="valor" class="atm-input" placeholder="0.00" required>
                        </div>
                        <button type="submit" class="btn btn-atm">Depositar</button>
                    </form>
                    <a href="menu.php" class="btn btn-atm btn-atm-secondary">Cancelar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
