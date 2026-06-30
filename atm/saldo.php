<?php
session_start();
if (!isset($_SESSION['conta_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';

$db = Database::conectar();
$contaId = $_SESSION['conta_id'];

$stmt = $db->prepare("SELECT c.*, u.nome as titular FROM contas c JOIN utilizadores u ON c.utilizador_id = u.id WHERE c.id = :id");
$stmt->bindParam(':id', $contaId, PDO::PARAM_INT);
$stmt->execute();
$conta = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtMov = $db->prepare("SELECT * FROM transacoes WHERE conta_origem_id = :id OR conta_destino_id = :id2 ORDER BY data_movimento DESC LIMIT 5");
$stmtMov->bindParam(':id', $contaId, PDO::PARAM_INT);
$stmtMov->bindParam(':id2', $contaId, PDO::PARAM_INT);
$stmtMov->execute();
$movimentos = $stmtMov->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Saldo e Movimentos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="atm-bg">
    <div class="atm-container">
        <div class="atm-screen">
            <div class="atm-header">
                <h1>DevBank</h1>
                <p>Consulta de Saldo</p>
            </div>
            <div class="atm-body">
                <div class="saldo-card">
                    <p class="saldo-label">Saldo Atual</p>
                    <p class="saldo-value"><?= number_format($conta['saldo'], 2, ',', ' ') ?> €</p>
                    <p class="saldo-titular">Titular: <?= htmlspecialchars($conta['titular']) ?></p>
                    <p class="saldo-conta">Conta: <?= htmlspecialchars($conta['numero_conta']) ?> (<?= ucfirst($conta['tipo_conta']) ?>)</p>
                </div>

                <h3>Últimos Movimentos</h3>
                <div class="table-responsive">
                    <table class="table table-atm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimentos)): ?>
                                <tr><td colspan="3" class="text-center">Nenhum movimento encontrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($movimentos as $mov): ?>
                                    <tr>
                                        <td><?= ucfirst($mov['tipo_transacao']) ?></td>
                                        <td class="<?= $mov['conta_origem_id'] == $contaId ? 'valor-negativo' : 'valor-positivo' ?>">
                                            <?= $mov['conta_origem_id'] == $contaId ? '-' : '+' ?>
                                            <?= number_format($mov['valor'], 2, ',', ' ') ?> €
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($mov['data_movimento'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <a href="menu.php" class="btn btn-atm">Voltar ao Menu</a>
            </div>
        </div>
    </div>
</body>
</html>
