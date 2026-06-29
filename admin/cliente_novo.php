<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Utilizador.php';
require_once __DIR__ . '/../classes/Admin.php';

$sucesso = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = new Admin(
        $_SESSION['admin_id'],
        $_SESSION['admin_nome'],
        null,
        '',
        '',
        'admin'
    );

    try {
        $cliente_id = $admin->criarCliente([
            'nome'     => $_POST['nome'],
            'email'    => $_POST['email'],
            'password' => $_POST['password'],
            'nif'      => $_POST['nif'] ?? '',
        ]);
        $sucesso = 'Cliente registado com sucesso!';
    } catch (\Exception $e) {
        $erro = 'Erro ao registar cliente: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Novo Cliente</title>
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
        <h1>Registar Novo Cliente</h1>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Palavra-passe *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="nif">NIF</label>
                <input type="text" id="nif" name="nif" maxlength="9">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registar Cliente</button>
                <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
