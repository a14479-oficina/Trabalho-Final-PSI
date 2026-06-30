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

$contas = [];
if ($clienteId) {
    $stmt = $db->prepare("SELECT id, numero_conta, tipo_conta FROM contas WHERE utilizador_id = :id");
    $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $clienteId) {
    $contaId = $_POST['conta_id'] ?? '';
    $pin = $_POST['pin'] ?? '';

    if (empty($contaId) || empty($pin)) {
        $erro = 'Selecione uma conta e defina um PIN.';
    } elseif (!preg_match('/^\d{4}$/', $pin)) {
        $erro = 'O PIN deve ter exatamente 4 dígitos.';
    } else {
        try {
            $stmtCheck = $db->prepare("SELECT id FROM contas WHERE id = :id AND utilizador_id = :utilizador_id");
            $stmtCheck->bindParam(':id', $contaId, PDO::PARAM_INT);
            $stmtCheck->bindParam(':utilizador_id', $clienteId, PDO::PARAM_INT);
            $stmtCheck->execute();

            if (!$stmtCheck->fetch()) {
                $erro = 'Conta não encontrada para este cliente.';
            } else {
                $numeroCartao = Cartao::gerarNumeroCartao($db);
                $validade = date('Y-m-d', strtotime('+5 years'));

                $cartao = new Cartao((int)$contaId, $numeroCartao, $pin, $validade);
                $cartao->salvar($db);

                $sucesso = 'Cartão emitido com sucesso! Número: ' . $numeroCartao;
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao emitir cartão: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Emitir Cartão</title>
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
        <h2 class="text-lg font-semibold text-[#1e3a5f]">Emitir Cartão</h2>
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

          <?php if (empty($contas)): ?>
            <div class="alert alert-warning">Este cliente não tem contas. <a href="criar_conta.php?cliente_id=<?= $clienteId ?>">Criar conta</a></div>
          <?php else: ?>
            <form method="POST" class="form-card">
              <input type="hidden" name="cliente_id" value="<?= $clienteId ?>">
              <div class="form-group">
                <label for="conta_id">Selecionar Conta</label>
                <select id="conta_id" name="conta_id" required>
                  <option value="">Selecione...</option>
                  <?php foreach ($contas as $conta): ?>
                    <option value="<?= $conta['id'] ?>">
                      <?= htmlspecialchars($conta['numero_conta']) ?> (<?= ucfirst($conta['tipo_conta']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="pin">PIN (4 dígitos)</label>
                <input type="text" id="pin" name="pin" maxlength="4" pattern="\d{4}" placeholder="1234" required>
              </div>
              <button type="submit" class="btn btn-primary">Emitir Cartão</button>
            </form>
          <?php endif; ?>
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
