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
                    <p class="text-white/30 text-xs mt-1">Depósito</p>
                </div>
                <div class="min-h-[200px]">
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
