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
                    <p class="text-white/30 text-xs mt-1">Menu Principal</p>
                </div>
                <div class="min-h-[200px]">
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
