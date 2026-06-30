<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Conta.php';
require_once __DIR__ . '/../classes/ContaCorrente.php';
require_once __DIR__ . '/../classes/ContaPoupanca.php';

$db = Database::conectar();
$contaId = $_SESSION['conta_id'];
$tipoConta = $_SESSION['tipo_conta'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
    $valor = (float)$valor;

    if ($valor <= 0) {
        $erro = 'Valor inválido.';
    } elseif ($valor > 500) {
        $erro = 'Limite máximo de 500 € por levantamento.';
    } else {
        $stmt = $db->prepare("SELECT * FROM contas WHERE id = :id");
        $stmt->bindParam(':id', $contaId, PDO::PARAM_INT);
        $stmt->execute();
        $dadosConta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dadosConta) {
            $erro = 'Conta não encontrada.';
        } else {
            if ($tipoConta === 'poupanca') {
                $conta = new ContaPoupanca($dadosConta['utilizador_id'], $dadosConta['numero_conta'], (float)$dadosConta['saldo']);
            } else {
                $conta = new ContaCorrente($dadosConta['utilizador_id'], $dadosConta['numero_conta'], (float)$dadosConta['saldo']);
            }
            $conta->setId((int)$dadosConta['id']);

            try {
                $db->beginTransaction();

                if ($conta->levantar($db, $valor)) {
                    $db->commit();
                    $sucesso = 'Levantamento de ' . number_format($valor, 2, ',', ' ') . ' € realizado com sucesso!';
                } else {
                    $db->rollBack();
                    $erro = 'Saldo insuficiente ou valor excede o limite permitido.';
                }
            } catch (Exception $e) {
                $db->rollBack();
                $erro = 'Erro ao realizar levantamento: ' . $e->getMessage();
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
    <title>DevBank - Levantamento</title>
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
                    <p class="text-white/30 text-xs mt-1">Levantamento</p>
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
                                <label for="valor">Valor a Levantar (€)</label>
                                <input type="text" id="valor" name="valor" class="atm-input" placeholder="0.00" required>
                            </div>
                            <button type="submit" class="btn btn-atm">Levantar</button>
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
