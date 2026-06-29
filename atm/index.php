<?php
session_start();
session_destroy();
session_start();

$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Multibanco</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Caixa Multibanco</p>
        </div>

        <div class="atm-display">
            <h2>Inserir Cartão</h2>

            <?php if ($erro): ?>
                <div class="atm-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="validar.php" class="atm-form">
                <div class="atm-field">
                    <label>Número do Cartão</label>
                    <input type="text" name="numero_cartao" maxlength="19" placeholder="0000 0000 0000 0000" required
                           oninput="this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim()">
                </div>
                <div class="atm-field">
                    <label>PIN (4 dígitos)</label>
                    <input type="password" name="pin" maxlength="4" pattern="\d{4}" placeholder="****" required>
                </div>
                <button type="submit" class="atm-btn">Confirmar</button>
            </form>
        </div>

        <div class="atm-footer">
            <p>Insira o seu cartão e introduza o PIN</p>
        </div>
    </div>
</body>
</html>
