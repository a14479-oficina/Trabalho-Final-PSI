<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Conta.php';

$contas = Conta::listarTodas();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Contas</title>
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
        <div class="page-header">
            <h1>Gestão de Contas</h1>
            <a href="conta_nova.php" class="btn btn-primary">+ Nova Conta</a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Número Conta</th>
                        <th>Tipo</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas as $conta): ?>
                    <tr>
                        <td><?= $conta['id'] ?></td>
                        <td><?= htmlspecialchars($conta['utilizador_nome']) ?></td>
                        <td><?= htmlspecialchars($conta['numero_conta']) ?></td>
                        <td><?= ucfirst($conta['tipo_conta']) ?></td>
                        <td><?= number_format($conta['saldo'], 2, ',', '.') ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($contas)): ?>
                    <tr><td colspan="5" class="text-center">Nenhuma conta registada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>
