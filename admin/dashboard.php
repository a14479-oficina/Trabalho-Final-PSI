<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';

$db = Database::conectar();
$admin = new Admin('', '', '');

$stmt = $db->prepare("SELECT id, nome, nif, email, criado_em FROM utilizadores WHERE tipo_utilizador = :tipo ORDER BY criado_em DESC");
$stmt->execute([':tipo' => 'cliente']);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cliente'])) {
    $clienteId = (int) $_POST['cliente_id'];
    if ($admin->eliminarCliente($db, $clienteId)) {
        $mensagem = 'Cliente eliminado com sucesso.';
    } else {
        $mensagem = 'Erro ao eliminar cliente.';
    }
    $clientes = array_filter($clientes, fn($c) => $c['id'] != $clienteId);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Gestão de Clientes</title>
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
        <h2 class="text-lg font-semibold text-[#1e3a5f]">Gestão de Clientes</h2>
        <?php if (isset($_SESSION['admin_nome'])): ?>
          <span class="text-sm text-[#8a7f72]">Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome']) ?></span>
        <?php endif; ?>
      </header>

      <div class="p-8">
        <?php if ($mensagem): ?>
          <div class="alert alert-<?= strpos($mensagem, 'sucesso') !== false ? 'success' : 'danger' ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <div class="admin-actions">
          <h2>Gestão de Clientes</h2>
          <a href="criar_cliente.php" class="btn btn-primary">+ Novo Cliente</a>
        </div>

        <h3>Clientes Registados</h3>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>NIF</th>
                <th>Email</th>
                <th>Registado em</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($clientes)): ?>
                <tr><td colspan="6" class="text-center">Nenhum cliente registado.</td></tr>
              <?php else: ?>
                <?php foreach ($clientes as $cliente): ?>
                  <tr>
                    <td><?= $cliente['id'] ?></td>
                    <td><?= htmlspecialchars($cliente['nome']) ?></td>
                    <td><?= htmlspecialchars($cliente['nif'] ?? '---') ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($cliente['criado_em'])) ?></td>
                    <td>
                      <a href="criar_conta.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-sm btn-success">Nova Conta</a>
                      <a href="emitir_cartao.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-sm btn-info">Emitir Cartão</a>
                      <form method="POST" onsubmit="return confirm('Tem a certeza que pretende eliminar este cliente?')" style="display:inline">
                        <input type="hidden" name="cliente_id" value="<?= $cliente['id'] ?>">
                        <button type="submit" name="eliminar_cliente" class="btn btn-sm btn-danger">Eliminar</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
