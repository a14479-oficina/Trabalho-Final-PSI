<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}
if (isset($_SESSION['conta_id'])) {
    header('Location: atm/menu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>DevBank — Sistema Bancário Modular</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="landing">
        <h1>DevBank</h1>
        <p>Sistema Bancário Modular</p>
        <div>
            <a href="admin/login.php" class="btn">Painel de Administração</a>
            <a href="atm/index.php" class="btn">Caixa Multibanco</a>
        </div>
        <button class="btn btn-creds" onclick="toggleCreds()">Mostrar Credenciais</button>
        <div id="creds-table" class="creds-hidden creds-box">
            <h2 style="color:var(--accent-2);margin-bottom:16px;text-align:left">Credenciais de Teste</h2>
            <div class="creds-section">
                <h3>Administração</h3>
                <table>
                    <tr><th>Email</th><th>Password</th></tr>
                    <tr><td>admin@devbank.pt</td><td>gpsi12</td></tr>
                </table>
            </div>
            <div class="creds-section">
                <h3>Multibanco (ATM)</h3>
                <table>
                    <tr><th>Cliente</th><th>Cartão</th><th>PIN</th></tr>
                    <tr><td>Ana Silva</td><td>5044123456789012</td><td>1234</td></tr>
                    <tr><td>Rui Santos</td><td>5044987654321098</td><td>1234</td></tr>
                </table>
            </div>
        </div>
    </div>
    <script>
    function toggleCreds() {
        var el = document.getElementById('creds-table');
        el.classList.toggle('creds-hidden');
    }
    </script>
</body>
</html>
