<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Menu</title>
    <link rel="stylesheet" href="../css/atm.css">
</head>
<body class="atm-body">
    <div class="atm-screen">
        <div class="atm-header">
            <h1>DevBank</h1>
            <p>Cartão: **** **** **** <?= substr($_SESSION['numero_cartao'], -4) ?></p>
        </div>

        <div class="atm-display">
            <h2>Menu Principal</h2>

            <div class="atm-menu">
                <a href="saldo.php" class="atm-menu-btn">
                    <span class="menu-icon">&#128202;</span>
                    <span class="menu-label">Saldo e Movimentos</span>
                </a>
                <a href="levantamento.php" class="atm-menu-btn">
                    <span class="menu-icon">&#128176;</span>
                    <span class="menu-label">Levantamento</span>
                </a>
                <a href="pagamento.php" class="atm-menu-btn">
                    <span class="menu-icon">&#128179;</span>
                    <span class="menu-label">Pagamento de Serviços</span>
                </a>
                <a href="transferencia.php" class="atm-menu-btn">
                    <span class="menu-icon">&#128184;</span>
                    <span class="menu-label">Transferências</span>
                </a>
                <a href="index.php?sair=1" class="atm-menu-btn atm-menu-sair">
                    <span class="menu-icon">&#128682;</span>
                    <span class="menu-label">Sair / Cancelar</span>
                </a>
            </div>
        </div>

        <div class="atm-footer">
            <p>Selecione uma operação</p>
        </div>
    </div>
</body>
</html>
