<?php
session_start();

if (isset($_GET['sair'])) {
    session_destroy();
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBank</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a1628, #1a237e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container { text-align: center; }
        .logo { font-size: 48px; font-weight: 700; letter-spacing: 6px; color: #00e5ff; text-shadow: 0 0 20px rgba(0,229,255,0.5); margin-bottom: 10px; }
        .subtitle { color: #90a4ae; margin-bottom: 40px; letter-spacing: 2px; font-size: 14px; }
        .cards { display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid #2a3a6e;
            border-radius: 16px;
            padding: 40px;
            width: 280px;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s;
        }
        .card:hover { border-color: #00e5ff; background: rgba(0,229,255,0.08); transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .card-icon { font-size: 48px; margin-bottom: 15px; }
        .card-title { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .card-desc { color: #90a4ae; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">DevBank</div>
        <div class="subtitle">Solução Bancária Modular</div>
        <div class="cards">
            <a href="admin/login.php" class="card">
                <div class="card-icon">&#128272;</div>
                <div class="card-title">Admin</div>
                <div class="card-desc">Painel de administração para gestão de clientes e contas</div>
            </a>
            <a href="atm/index.php" class="card">
                <div class="card-icon">&#127919;</div>
                <div class="card-title">Multibanco</div>
                <div class="card-desc">Simulador de caixa multibanco para operações diárias</div>
            </a>
        </div>
    </div>
</body>
</html>
