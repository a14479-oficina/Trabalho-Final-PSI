<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getConexao();
$stmt = $pdo->prepare(
    "SELECT u.id, u.nome, u.nif, u.email, u.criado_em,
            COUNT(DISTINCT c.id) AS total_contas,
            COUNT(DISTINCT ca.id) AS total_cartoes
     FROM utilizadores u
     LEFT JOIN contas c ON c.utilizador_id = u.id
     LEFT JOIN cartoes ca ON ca.conta_id = c.id
     WHERE u.tipo_utilizador = 'cliente'
     GROUP BY u.id
     ORDER BY u.nome"
);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Clientes | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">DevBank — Lista de Clientes</span>
            <a href="dashboard.php" class="text-white text-decoration-none">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (count($clientes) === 0): ?>
            <div class="alert alert-info">Nenhum cliente registado.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>NIF</th>
                            <th>Email</th>
                            <th>Contas</th>
                            <th>Cartões</th>
                            <th>Registo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['nome']) ?></td>
                                <td><?= htmlspecialchars($c['nif'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= $c['total_contas'] ?></td>
                                <td><?= $c['total_cartoes'] ?></td>
                                <td><?= $c['criado_em'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
