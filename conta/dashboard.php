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
            ca.id AS cartao_id, ca.numero_cartao, ca.estado, ca.validade
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#f5f0eb] min-h-screen">
    <nav class="navbar">
        <div class="navbar-brand">DevBank - Cliente</div>
        <div class="navbar-user">
            <span>Bem-vindo, <?= htmlspecialchars($cliente['nome']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container">
        <div class="glass rounded-2xl p-8 mb-8">
            <h2 class="text-xl font-bold text-[#1e3a5f] mb-6">Os meus dados</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <span class="text-xs text-[#8a7f72] uppercase tracking-wider">Nome</span>
                    <p class="text-[#1e293b] font-medium mt-1"><?= htmlspecialchars($cliente['nome']) ?></p>
                </div>
                <div>
                    <span class="text-xs text-[#8a7f72] uppercase tracking-wider">NIF</span>
                    <p class="text-[#1e293b] font-medium mt-1"><?= htmlspecialchars($cliente['nif'] ?? '---') ?></p>
                </div>
                <div>
                    <span class="text-xs text-[#8a7f72] uppercase tracking-wider">Email</span>
                    <p class="text-[#1e293b] font-medium mt-1"><?= htmlspecialchars($cliente['email']) ?></p>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-[#1e3a5f] mb-4">As minhas contas e cartões</h3>

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
                            <th>Validade</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contas as $conta): ?>
                            <tr>
                                <td><?= htmlspecialchars($conta['numero_conta']) ?></td>
                                <td><?= $conta['tipo_conta'] === 'corrente' ? 'Corrente' : 'Poupança' ?></td>
                                <td class="font-semibold"><?= number_format($conta['saldo'], 2, ',', '.') ?> €</td>
                                <?php if ($conta['cartao_id']): ?>
                                    <td><?= htmlspecialchars($conta['numero_cartao']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($conta['validade'])) ?></td>
                                    <td><span class="text-<?= $conta['estado'] === 'ativo' ? 'green' : 'red' ?>-600 font-medium"><?= $conta['estado'] === 'ativo' ? 'Ativo' : 'Bloqueado' ?></span></td>
                                <?php else: ?>
                                    <td colspan="3" class="text-center">Sem cartão associado</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="../atm/index.php" class="btn btn-primary">Aceder ao Multibanco</a>
        </div>
    </div>
</body>
</html>
