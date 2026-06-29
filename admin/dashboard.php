<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">DevBank</div>
        <div class="navbar-user">
            <span>Olá, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <h1>Dashboard do Administrador</h1>

        <div class="dashboard-cards">
            <a href="clientes.php" class="card">
                <div class="card-icon">&#128100;</div>
                <div class="card-title">Gerir Clientes</div>
                <div class="card-desc">Registar e consultar clientes</div>
            </a>
            <a href="contas.php" class="card">
                <div class="card-icon">&#128179;</div>
                <div class="card-title">Gerir Contas</div>
                <div class="card-desc">Abrir contas e emitir cartões</div>
            </a>
        </div>
    </div>
</body>
</html>
