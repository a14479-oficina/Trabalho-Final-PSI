<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';

$db = Database::conectar();

$stmt = $db->prepare("SELECT id, nome, nif, email FROM utilizadores WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare(
    "SELECT c.id AS conta_id, c.numero_conta, c.tipo_conta, c.saldo,
            ca.id AS cartao_id, ca.numero_cartao, ca.pin, ca.estado, ca.validade
     FROM contas c
     LEFT JOIN cartoes ca ON ca.conta_id = c.id
     WHERE c.utilizador_id = :id
     ORDER BY c.tipo_conta"
);
$stmt->bindParam(':id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->execute();
$contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - A minha conta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">DevBank - Cliente</div>
        <div class="navbar-user">
            <span>Bem-vindo, <?= htmlspecialchars($cliente['nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <h2>Os meus dados</h2>
        <div class="info-card">
            <p><strong>Nome:</strong> <?= htmlspecialchars($cliente['nome']) ?></p>
            <p><strong>NIF:</strong> <?= htmlspecialchars($cliente['nif'] ?? '---') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
        </div>

        <h3>As minhas contas e cartões</h3>

        <?php if (empty($contas)): ?>
            <div class="alert alert-warning">Não tem contas bancárias associadas.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Conta</th>
                            <th>Tipo</th>
                            <th>Saldo</th>
                            <th>Cartão</th>
                            <th>PIN</th>
                            <th>Validade</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contas as $conta): ?>
                            <tr>
                                <td><?= htmlspecialchars($conta['numero_conta']) ?></td>
                                <td><?= $conta['tipo_conta'] === 'corrente' ? 'Corrente' : 'Poupança' ?></td>
                                <td><?= number_format($conta['saldo'], 2, ',', '.') ?> €</td>
                                <?php if ($conta['cartao_id']): ?>
                                    <td><?= htmlspecialchars($conta['numero_cartao']) ?></td>
                                    <td><?= htmlspecialchars($conta['pin']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($conta['validade'])) ?></td>
                                    <td><?= $conta['estado'] === 'ativo' ? 'Ativo' : 'Bloqueado' ?></td>
                                <?php else: ?>
                                    <td colspan="4" class="text-center">Sem cartão associado</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="admin-actions" style="margin-top: 20px;">
            <a href="../atm/index.php" class="btn btn-primary">Aceder ao Multibanco</a>
        </div>
    </div>
</body>
</html>
