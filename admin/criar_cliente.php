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
    <title>DevBank - Novo Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#f5f0eb]">
  <div class="flex min-h-screen">
    <aside class="sidebar">
      <div class="p-6 border-b border-[#e8e0d5]/30">
        <h1 class="text-xl font-bold text-[#1e3a5f]">DevBank</h1>
        <p class="text-sm text-[#8a7f72]">Administração</p>
      </div>
      <nav class="sidebar-nav flex-1 pt-4">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium transition-all <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"><span>📊</span> Clientes</a>
        <a href="criar_cliente.php" class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium transition-all <?= basename($_SERVER['PHP_SELF']) === 'criar_cliente.php' ? 'active' : '' ?>"><span>➕</span> Novo Cliente</a>
        <a href="criar_conta.php" class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium transition-all <?= basename($_SERVER['PHP_SELF']) === 'criar_conta.php' ? 'active' : '' ?>"><span>💳</span> Nova Conta</a>
        <a href="emitir_cartao.php" class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium transition-all <?= basename($_SERVER['PHP_SELF']) === 'emitir_cartao.php' ? 'active' : '' ?>"><span>🏧</span> Emitir Cartão</a>
      </nav>
      <div class="p-4 border-t border-[#e8e0d5]/30">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition-all"><span>🚪</span> Terminar Sessão</a>
      </div>
    </aside>

    <main class="flex-1">
      <header class="h-16 bg-white/60 backdrop-blur-xl border-b border-[#e8e0d5]/30 flex items-center justify-between px-8 sticky top-0 z-10">
        <h2 class="text-lg font-semibold text-[#1e3a5f]">Novo Cliente</h2>
        <?php if (isset($_SESSION['admin_nome'])): ?>
          <span class="text-sm text-[#8a7f72]">Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
        <?php endif; ?>
      </header>

      <div class="p-8">
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
    </main>
  </div>
</body>
</html>
