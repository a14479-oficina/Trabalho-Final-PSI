<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $palavraPasse = $_POST['palavra_passe'] ?? '';

    $db = Database::conectar();
    $stmt = $db->prepare("SELECT * FROM utilizadores WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilizador && password_verify($palavraPasse, $utilizador['palavra_passe'])) {
        if ($utilizador['tipo_utilizador'] === 'admin') {
            $_SESSION['admin_id'] = $utilizador['id'];
            $_SESSION['admin_nome'] = $utilizador['nome'];
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['cliente_id'] = $utilizador['id'];
            $_SESSION['cliente_nome'] = $utilizador['nome'];
            header('Location: ../conta/dashboard.php');
            exit;
        }
    } else {
        $erro = 'Email ou palavra-passe inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-bg">
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-6">
                <h1>DevBank</h1>
                <h2>Iniciar Sessão</h2>
            </div>
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@devbank.pt" required>
                </div>
                <div class="form-group">
                    <label for="palavra_passe">Palavra-passe</label>
                    <input type="password" id="palavra_passe" name="palavra_passe" placeholder="********" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Entrar</button>
            </form>
            <div class="text-center mt-6">
                <a href="../index.php" class="back-link">Voltar ao Início</a>
            </div>
            <div class="mt-6 p-4 bg-[#f0f9ff] rounded-xl border border-[#bae6fd] text-xs text-[#0c4a6e] leading-relaxed">
                <strong>Credenciais de teste:</strong><br>
                Admin: admin@devbank.pt / gpsi12<br>
                Clientes: gpsi12<br>
                PIN ATM: 1234
            </div>
        </div>
    </div>
</body>
</html>
