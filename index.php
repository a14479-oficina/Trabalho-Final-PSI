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
    </div>
</body>
</html>
