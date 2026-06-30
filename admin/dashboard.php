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

$stmt = $db->query("SELECT id, nome, nif, email, criado_em FROM utilizadores WHERE tipo_utilizador = 'cliente' ORDER BY criado_em DESC");
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
    <title>DevBank - Dashboard Admin</title>
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
</body>
</html>
