<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo     = Database::getConexao();
$mensagem = '';
$erro     = '';

$contas = $pdo->query(
    "SELECT c.id, c.numero_conta, c.tipo_conta, u.nome AS cliente_nome
     FROM contas c
     JOIN utilizadores u ON u.id = c.utilizador_id
     ORDER BY u.nome"
)->fetchAll(PDO::FETCH_ASSOC);

function gerarNumeroCartao(PDO $pdo): string {
    do {
        $numero = '';
        for ($i = 0; $i < 16; $i++) {
            $numero .= random_int(0, 9);
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cartoes WHERE numero_cartao = :num");
        $stmt->execute([':num' => $numero]);
    } while ($stmt->fetchColumn() > 0);
    return $numero;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contaId      = (int) ($_POST['conta_id'] ?? 0);
    $pin          = $_POST['pin'] ?? '';
    $numeroCartao = $_POST['numero_cartao'] ?? '';

    if ($contaId <= 0 || !preg_match('/^\d{4}$/', $pin)) {
        $erro = 'Selecione uma conta e defina um PIN de 4 dígitos.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cartoes WHERE conta_id = :cid");
        $stmt->execute([':cid' => $contaId]);
        if ($stmt->fetchColumn() > 0) {
            $erro = 'Esta conta já tem um cartão associado.';
        } else {
            try {
                if (!$numeroCartao || strlen($numeroCartao) !== 16) {
                    $numeroCartao = gerarNumeroCartao($pdo);
                }
                $stmt = $pdo->prepare(
                    "INSERT INTO cartoes (conta_id, numero_cartao, pin_encriptado, estado, validade)
                     VALUES (:cid, :num, :pin, 'ativo', :val)"
                );
                $stmt->execute([
                    ':cid' => $contaId,
                    ':num' => $numeroCartao,
                    ':pin' => password_hash($pin, PASSWORD_DEFAULT),
                    ':val' => date('Y-m-d', strtotime('+5 years'))
                ]);
                $mensagem = "Cartão $numeroCartao emitido com sucesso!";
            } catch (Exception $e) {
                $erro = 'Erro ao emitir cartão.';
            }
        }
    }
}

$novoNumero = gerarNumeroCartao($pdo);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Emitir Cartão | DevBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">DevBank — Emitir Cartão</span>
            <a href="dashboard.php" class="text-white text-decoration-none">Voltar</a>
        </div>
    </nav>

    <div class="container mt-4" style="max-width: 550px;">
        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header">Emitir Novo Cartão</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="conta_id" class="form-label">Conta</label>
                        <select class="form-select" id="conta_id" name="conta_id" required>
                            <option value="">— Selecione —</option>
                            <?php foreach ($contas as $ct): ?>
                                <option value="<?= $ct['id'] ?>">
                                    <?= htmlspecialchars($ct['cliente_nome']) ?>
                                    — <?= htmlspecialchars($ct['numero_conta']) ?>
                                    (<?= $ct['tipo_conta'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="numero_cartao" class="form-label">Número do Cartão (16 dígitos)</label>
                        <input type="text" class="form-control" id="numero_cartao" name="numero_cartao"
                               value="<?= $novoNumero ?>" maxlength="16" pattern="\d{16}" readonly
                               style="font-family: monospace; font-size: 1.2em; letter-spacing: 2px;">
                        <div class="form-text">Gerado automaticamente. Editável se pretender um número específico.</div>
                    </div>
                    <div class="mb-3">
                        <label for="pin" class="form-label">PIN (4 dígitos)</label>
                        <input type="password" class="form-control" id="pin" name="pin"
                               maxlength="4" pattern="\d{4}" inputmode="numeric" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Emitir Cartão</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('numero_cartao').readOnly = false;
        document.getElementById('numero_cartao').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });
    </script>
</body>
</html>
