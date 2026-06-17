<?php
require_once __DIR__ . '/../config/init.php';

$erro = '';

if (isset($_SESSION['conta_id'])) {
    header('Location: menu.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroCartao = preg_replace('/\D/', '', $_POST['numero_cartao'] ?? '');
    $pin          = $_POST['pin'] ?? '';

    if (strlen($numeroCartao) !== 16 || !preg_match('/^\d{4}$/', $pin)) {
        $erro = 'Número de cartão (16 dígitos) e PIN (4 dígitos) são obrigatórios.';
    } else {
        try {
            $pdo = Database::getConexao();

            $stmt = $pdo->prepare("SELECT * FROM cartoes WHERE numero_cartao = :num");
            $stmt->execute([':num' => $numeroCartao]);
            $cartao = $stmt->fetch();

            if (!$cartao) {
                $erro = 'Cartão não encontrado.';
            } elseif ($cartao['estado'] === 'bloqueado') {
                $erro = 'Cartão bloqueado. Contacte o banco.';
            } elseif (strtotime($cartao['validade']) < time()) {
                $erro = 'Cartão expirado.';
            } elseif (!password_verify($pin, $cartao['pin_encriptado'])) {
                $erro = 'PIN incorreto.';
            } else {
                $stmt = $pdo->prepare(
                    "SELECT c.*, u.nome AS cliente_nome
                     FROM contas c
                     JOIN utilizadores u ON u.id = c.utilizador_id
                     WHERE c.id = :cid"
                );
                $stmt->execute([':cid' => $cartao['conta_id']]);
                $conta = $stmt->fetch();

                $_SESSION['cartao_id']    = $cartao['id'];
                $_SESSION['cartao_num']   = $cartao['numero_cartao'];
                $_SESSION['conta_id']     = $conta['id'];
                $_SESSION['conta_num']    = $conta['numero_conta'];
                $_SESSION['tipo_conta']   = $conta['tipo_conta'];
                $_SESSION['cliente_nome'] = $conta['cliente_nome'];

                header('Location: menu.php');
                exit;
            }
        } catch (Exception $e) {
            $erro = 'Erro de sistema. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Multibanco | DevBank</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Caixa Multibanco</p>
        </div>

        <div class="atm-card-slot">
            <div class="slot-icon">&#10691;</div>
        </div>

        <?php if ($erro): ?>
            <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="atm-form">
            <div class="atm-input-group">
                <label for="numero_cartao">Número do Cartão</label>
                <input type="text" id="numero_cartao" name="numero_cartao"
                       maxlength="16" pattern="\d{16}" inputmode="numeric"
                       placeholder="5044 1234 5678 9012" autocomplete="off" required>
            </div>
            <div class="atm-input-group">
                <label for="pin">PIN</label>
                <input type="password" id="pin" name="pin"
                       maxlength="4" pattern="\d{4}" inputmode="numeric"
                       placeholder="****" autocomplete="off" required>
            </div>
            <button type="submit" class="atm-btn">Validar</button>
        </form>

        <div class="atm-footer">
            <a href="../admin/login.php" class="atm-link">Admin</a>
        </div>
    </div>

    <script>
        document.getElementById('numero_cartao').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });
        document.getElementById('pin').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
        });
    </script>
</body>
</html>
