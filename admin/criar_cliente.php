<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';

$sucesso = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $palavraPasse = $_POST['palavra_passe'] ?? '';

    if (empty($nome) || empty($email) || empty($palavraPasse)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            $db = Database::conectar();
            $admin = new Admin('', '', '');
            $admin->criarCliente($db, $nome, $nif, $email, $palavraPasse);
            $sucesso = 'Cliente criado com sucesso!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = 'Este email já está registado.';
            } else {
                $erro = 'Erro ao criar cliente: ' . $e->getMessage();
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
    <title>DevBank - Criar Cliente</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">DevBank - Admin</div>
        <div class="navbar-user">
            <span>Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <h2>Registar Novo Cliente</h2>
        <a href="dashboard.php" class="back-link">&laquo; Voltar ao Dashboard</a>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="nif">NIF</label>
                <input type="text" id="nif" name="nif" maxlength="9">
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="palavra_passe">Palavra-passe *</label>
                <input type="password" id="palavra_passe" name="palavra_passe" required>
            </div>
            <button type="submit" class="btn btn-primary">Criar Cliente</button>
        </form>
    </div>
</body>
</html>
