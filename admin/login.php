<?php
require_once __DIR__ . '/../config/init.php';

$erro = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $pdo = Database::getConexao();
        $admin = Admin::login($pdo, $email, $password);

        if ($admin) {
            $_SESSION['admin_id']    = $admin->getId();
            $_SESSION['admin_nome']  = $admin->getNome();
            $_SESSION['admin_email'] = $admin->getEmail();
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = 'Email ou palavra-passe inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Admin - Login | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 450px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4>DevBank — Admin</h4>
            </div>
            <div class="card-body">
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Palavra-passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="../atm/index.php" class="text-decoration-none">Ir para o Multibanco</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
