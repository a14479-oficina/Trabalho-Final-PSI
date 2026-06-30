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
                    <h2>Inserir Cartão</h2>
                    <form method="POST" action="validar_pin.php">
                        <div class="form-group">
                            <label for="numero_cartao">Número do Cartão (16 dígitos)</label>
                            <input type="text" id="numero_cartao" name="numero_cartao" maxlength="16" pattern="\d{16}" placeholder="5044123456789012" class="atm-input" required>
                        </div>
                        <button type="submit" class="btn btn-atm">Inserir Cartão</button>
                    </form>
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
