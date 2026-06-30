<?php
require_once __DIR__ . '/classes/Database.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || PHP_SAPI === 'cli') {
    try {
        $db = Database::conectar();

        $hashGeral = password_hash('gpsi12', PASSWORD_DEFAULT);
        $hashPin = password_hash('1234', PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE utilizadores SET palavra_passe = :hash WHERE tipo_utilizador = 'admin'");
        $stmt->bindParam(':hash', $hashGeral);
        $stmt->execute();

        $stmt = $db->prepare("UPDATE utilizadores SET palavra_passe = :hash WHERE tipo_utilizador = 'cliente'");
        $stmt->bindParam(':hash', $hashGeral);
        $stmt->execute();

        $stmt = $db->prepare("UPDATE cartoes SET pin_encriptado = :hash WHERE estado = 'ativo'");
        $stmt->bindParam(':hash', $hashPin);
        $stmt->execute();

        $mensagem = 'Hashes atualizados com sucesso!';
    } catch (PDOException $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Fix Hashes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .fix-container { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
        .fix-card { background:rgba(255,255,255,0.75); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border-radius:20px; padding:44px 40px; max-width:480px; box-shadow:0 8px 48px rgba(0,0,0,0.06); border:1px solid rgba(255,255,255,0.8); text-align:center; }
        .fix-card h1 { color:#1e3a5f; font-size:1.5rem; font-weight:700; margin-bottom:8px; }
        .fix-card p { color:#8a7f72; font-size:0.9rem; margin-bottom:24px; line-height:1.5; }
        .fix-card ul { text-align:left; color:#475569; font-size:0.85rem; margin-bottom:24px; padding-left:20px; }
        .fix-card ul li { margin-bottom:6px; }
    </style>
</head>
<body style="background:linear-gradient(160deg,#faf7f2,#f5f0eb,#eff6ff,#dbeafe);">
    <div class="fix-container">
        <div class="fix-card">
            <h1>DevBank</h1>
            <h2 style="color:#8a7f72;font-size:0.9rem;font-weight:400;margin-bottom:20px;">Corrigir Hashes da Base de Dados</h2>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php elseif ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
                <div class="info-card" style="text-align:left;">
                    <strong>Credenciais atualizadas:</strong><br>
                    Admin: <strong>admin@devbank.pt</strong> / <strong>gpsi12</strong><br>
                    Clientes: <strong>gpsi12</strong><br>
                    PIN dos Cartões: <strong>1234</strong>
                </div>
            <?php else: ?>
                <p>Os hashes da base de dados podem estar corrompidos ou incompatíveis.</p>
                <ul>
                    <li><strong>Admin:</strong> admin@devbank.pt / gpsi12</li>
                    <li><strong>Clientes:</strong> gpsi12</li>
                    <li><strong>PIN Cartões:</strong> 1234</li>
                </ul>
                <form method="POST">
                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;">Corrigir Hashes</button>
                </form>
            <?php endif; ?>
            <a href="index.php" class="back-link" style="margin-top:20px;display:inline-block;">Voltar ao Início</a>
        </div>
    </div>
</body>
</html>
