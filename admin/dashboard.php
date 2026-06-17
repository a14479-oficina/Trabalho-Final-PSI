<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getConexao();

$totalClientes = $pdo->query("SELECT COUNT(*) FROM utilizadores WHERE tipo_utilizador = 'cliente'")->fetchColumn();
$totalContas   = $pdo->query("SELECT COUNT(*) FROM contas")->fetchColumn();
$totalCartoes  = $pdo->query("SELECT COUNT(*) FROM cartoes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0">DevBank — Painel de Administração</span>
            <span class="text-white"><?= htmlspecialchars($_SESSION['admin_nome']) ?> |
                <a href="logout.php" class="text-white text-decoration-none">Sair</a>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <h5 class="card-title">Clientes</h5>
                        <p class="display-6"><?= $totalClientes ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Contas</h5>
                        <p class="display-6"><?= $totalContas ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cartões</h5>
                        <p class="display-6"><?= $totalCartoes ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Gestão de Clientes</div>
                    <div class="card-body">
                        <a href="criar_cliente.php" class="btn btn-primary w-100 mb-2">Registar Novo Cliente</a>
                        <a href="listar_clientes.php" class="btn btn-secondary w-100">Listar Todos os Clientes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Gestão de Contas e Cartões</div>
                    <div class="card-body">
                        <a href="abrir_conta.php" class="btn btn-primary w-100 mb-2">Abrir Nova Conta</a>
                        <a href="emitir_cartao.php" class="btn btn-secondary w-100">Emitir Cartão</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
