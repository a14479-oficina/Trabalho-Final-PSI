<?php
session_start();
session_destroy();
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank - Caixa Multibanco</title>
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
                <h2>Inserir Cartão</h2>
                <form method="POST" action="validar_pin.php">
                    <div class="form-group">
                        <label for="numero_cartao">Número do Cartão (16 dígitos)</label>
                        <input type="text" id="numero_cartao" name="numero_cartao" maxlength="16" pattern="\d{16}" placeholder="5044123456789012" class="atm-input" required>
                    </div>
                    <button type="submit" class="btn btn-atm">Inserir Cartão</button>
                </form>
            </div>
            <div class="atm-footer">
                <p>Insira o seu cartão para continuar</p>
                <a href="../index.php" class="atm-link">Voltar</a>
            </div>
        </div>
    </div>
</body>
</html>
