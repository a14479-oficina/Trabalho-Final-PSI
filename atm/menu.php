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
    <title>DevBank - Menu Principal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="atm-bg">
    <div class="atm-container">
        <div class="atm-screen">
            <div class="atm-header">
                <h1>DevBank</h1>
                <p>Bem-vindo</p>
            </div>
            <div class="atm-body">
                <h2>Menu Principal</h2>
                <div class="atm-menu">
                    <a href="saldo.php" class="atm-menu-btn">
                        <span class="menu-icon">💰</span>
                        <span class="menu-label">Consultar Saldo</span>
                    </a>
                    <a href="levantamento.php" class="atm-menu-btn">
                        <span class="menu-icon">💵</span>
                        <span class="menu-label">Levantamento</span>
                    </a>
                    <a href="depositar.php" class="atm-menu-btn">
                        <span class="menu-icon">🏦</span>
                        <span class="menu-label">Depósito</span>
                    </a>
                    <a href="pagamento.php" class="atm-menu-btn">
                        <span class="menu-icon">📄</span>
                        <span class="menu-label">Pagamentos</span>
                    </a>
                    <a href="transferencia.php" class="atm-menu-btn">
                        <span class="menu-icon">🔄</span>
                        <span class="menu-label">Transferências</span>
                    </a>
                    <a href="logout.php" class="atm-menu-btn atm-menu-btn-danger">
                        <span class="menu-icon">🚪</span>
                        <span class="menu-label">Terminar Sessão</span>
                    </a>
                </div>
            </div>
            <div class="atm-footer">
                <p>Selecione uma operação</p>
            </div>
        </div>
    </div>
</body>
</html>
