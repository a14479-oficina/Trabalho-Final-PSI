<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Cartao.php';

$db = Database::conectar();
$sucesso = '';
$erro = '';

$clienteId = $_GET['cliente_id'] ?? $_POST['cliente_id'] ?? null;

if ($clienteId) {
    $stmt = $db->prepare("SELECT id, nome, email FROM utilizadores WHERE id = :id AND tipo_utilizador = 'cliente'");
    $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $erro = 'Cliente não encontrado.';
        $clienteId = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $clienteId) {
    $tipoConta = $_POST['tipo_conta'] ?? '';

    if (!in_array($tipoConta, ['corrente', 'poupanca'])) {
        $erro = 'Selecione um tipo de conta válido.';
    } else {
        try {
            $numeroConta = Cartao::gerarNumeroConta($db);

            $stmt = $db->prepare("INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo) VALUES (:utilizador_id, :numero_conta, :tipo_conta, 0.00)");
            $stmt->bindParam(':utilizador_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':numero_conta', $numeroConta);
            $stmt->bindParam(':tipo_conta', $tipoConta);
            $stmt->execute();

            $sucesso = 'Conta ' . ($tipoConta === 'corrente' ? 'Corrente' : 'Poupança') . ' criada com sucesso! Número: ' . $numeroConta;
        } catch (PDOException $e) {
            $erro = 'Erro ao criar conta: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Nova Conta</title>
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
        <h2 class="text-lg font-semibold text-[#1e3a5f]">Nova Conta</h2>
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

        <?php if ($clienteId && isset($cliente)): ?>
          <div class="info-card">
            <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['email']) ?>)</p>
          </div>

          <form method="POST" class="form-card">
            <input type="hidden" name="cliente_id" value="<?= $clienteId ?>">
            <div class="form-group">
              <label for="tipo_conta">Tipo de Conta</label>
              <select id="tipo_conta" name="tipo_conta" required>
                <option value="">Selecione...</option>
                <option value="corrente">Conta Corrente</option>
                <option value="poupanca">Conta Poupança</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Criar Conta</button>
          </form>
        <?php else: ?>
          <form method="GET" class="form-card">
            <div class="form-group">
              <label for="cliente_id">ID do Cliente</label>
              <input type="number" id="cliente_id" name="cliente_id" required>
            </div>
            <button type="submit" class="btn btn-primary">Selecionar Cliente</button>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
