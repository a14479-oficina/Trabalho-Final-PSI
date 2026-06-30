<?php
session_start();

require_once __DIR__ . '/../classes/Database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_cartao']) && !isset($_POST['pin'])) {
    $numeroCartao = $_POST['numero_cartao'];

    $db = Database::conectar();
    $stmt = $db->prepare("SELECT c.id, c.pin_encriptado, c.estado, c.conta_id, co.tipo_conta FROM cartoes c JOIN contas co ON c.conta_id = co.id WHERE c.numero_cartao = :numero");
    $stmt->bindParam(':numero', $numeroCartao);
    $stmt->execute();
    $cartao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartao) {
        $erro = 'Cartão não encontrado.';
    } elseif ($cartao['estado'] === 'bloqueado') {
        $erro = 'Cartão bloqueado. Contacte o banco.';
    } else {
        $_SESSION['cartao_id'] = $cartao['id'];
        $_SESSION['conta_id'] = $cartao['conta_id'];
        $_SESSION['tipo_conta'] = $cartao['tipo_conta'];
        $_SESSION['numero_cartao'] = $numeroCartao;
        $_SESSION['pin_encriptado'] = $cartao['pin_encriptado'];
        $_SESSION['aguardar_pin'] = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    $pin = $_POST['pin'];

    if (!isset($_SESSION['pin_encriptado'])) {
        header('Location: index.php');
        exit;
    }

    if (password_verify($pin, $_SESSION['pin_encriptado'])) {
        unset($_SESSION['aguardar_pin']);
        header('Location: menu.php');
        exit;
    } else {
        $erro = 'PIN incorreto. Tente novamente.';
        unset($_SESSION['pin_encriptado']);
        unset($_SESSION['aguardar_pin']);
    }
}

if ($erro) {
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DevBank - Erro</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="bg-gradient-to-br from-[#0a1628] via-[#0f1f3d] to-[#162d50] min-h-screen flex items-center justify-center p-4">
        <div class="atm-machine">
            <div class="atm-brand">DevBank</div>
            <div class="atm-screen-border">
                <div class="atm-screen-inner">
                    <div class="text-center mb-6 pb-4 border-b border-white/5">
                        <h1 class="text-white text-lg font-bold">DevBank</h1>
                        <p class="text-white/30 text-xs mt-1">Caixa Multibanco</p>
                    </div>
                    <div class="min-h-[200px]">
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <a href="index.php" class="btn btn-atm">Tentar Novamente</a>
                    </div>
                </div>
            </div>
            <div class="atm-bottom">
                <div class="atm-btn-side">
                    <span></span><span></span><span></span>
                </div>
                <div class="atm-card-slot">
                    <div class="atm-slot"></div>
                    <span class="atm-slot-label">Leitor de Cartão</span>
                </div>
                <div class="atm-btn-side">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_SESSION['aguardar_pin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DevBank - Digite o PIN</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="bg-gradient-to-br from-[#0a1628] via-[#0f1f3d] to-[#162d50] min-h-screen flex items-center justify-center p-4">
        <div class="atm-machine">
            <div class="atm-brand">DevBank</div>
            <div class="atm-screen-border">
                <div class="atm-screen-inner">
                    <div class="text-center mb-6 pb-4 border-b border-white/5">
                        <h1 class="text-white text-lg font-bold">DevBank</h1>
                        <p class="text-white/30 text-xs mt-1">Caixa Multibanco</p>
                    </div>
                    <div class="min-h-[200px]">
                        <h2>Digite o seu PIN</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="pin">PIN (4 dígitos)</label>
                                <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}" class="atm-input atm-pin" inputmode="numeric" autocomplete="off" required>
                            </div>
                            <button type="submit" class="btn btn-atm">Confirmar</button>
                        </form>
                        <a href="index.php" class="atm-link">Cancelar</a>
                    </div>
                </div>
            </div>
            <div class="atm-bottom">
                <div class="atm-btn-side">
                    <span></span><span></span><span></span>
                </div>
                <div class="atm-card-slot">
                    <div class="atm-slot"></div>
                    <span class="atm-slot-label">Leitor de Cartão</span>
                </div>
                <div class="atm-btn-side">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

header('Location: index.php');
exit;
