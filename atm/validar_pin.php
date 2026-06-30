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
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="atm-bg">
        <div class="atm-container">
            <div class="atm-screen">
                <div class="atm-header">
                    <h1>DevBank</h1>
                    <p>Caixa Multibanco</p>
                </div>
                <div class="atm-body">
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <a href="index.php" class="btn btn-atm">Tentar Novamente</a>
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
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="atm-bg">
        <div class="atm-container">
            <div class="atm-screen">
                <div class="atm-header">
                    <h1>DevBank</h1>
                    <p>Caixa Multibanco</p>
                </div>
                <div class="atm-body">
                    <h2>Digite o seu PIN</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="pin">PIN (4 dígitos)</label>
                            <input type="password" id="pin" name="pin" maxlength="4" pattern="\d{4}" class="atm-input atm-pin" inputmode="numeric" autocomplete="off" required>
                        </div>
                        <button type="submit" class="btn btn-atm">Confirmar</button>
                    </form>
                </div>
                <div class="atm-footer">
                    <a href="index.php" class="atm-link">Cancelar</a>
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
