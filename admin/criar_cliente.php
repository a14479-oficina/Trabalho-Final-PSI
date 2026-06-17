<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$erro     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $nif      = trim($_POST['nif'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nome === '' || $email === '' || $password === '') {
        $erro = 'Preencha nome, email e palavra-passe.';
    } else {
        try {
            $pdo  = Database::getConexao();
            $admin = new Admin();
            $admin->criarCliente($pdo, [
                'nome'          => $nome,
                'nif'           => $nif ?: null,
                'email'         => $email,
                'palavra_passe' => $password
            ]);
            $mensagem = 'Cliente registado com sucesso!';
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23000'
                ? 'Email já existe na base de dados.'
                : 'Erro ao registar cliente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar Cliente | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">DevBank — Registar Cliente</span>
            <a href="dashboard.php" class="text-white text-decoration-none">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4" style="max-width: 550px;">
        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header">Novo Cliente</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="nif" class="form-label">NIF</label>
                        <input type="text" class="form-control" id="nif" name="nif" maxlength="9">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Palavra-passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Registar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
