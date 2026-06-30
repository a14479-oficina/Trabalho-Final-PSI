<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "=== Configuração do DevBank ===\n\n";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "[OK] Base de dados '" . DB_NAME . "' criada.\n";

    $sql = file_get_contents(__DIR__ . '/script .sql');
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        if (stripos($statement, 'CREATE DATABASE') !== false) continue;
        if (stripos($statement, 'USE ') !== false) continue;
        if (stripos($statement, 'INSERT INTO') !== false) continue;
        $pdo->exec($statement);
    }
    echo "[OK] Tabelas criadas.\n";

    require_once __DIR__ . '/classes/Admin.php';

    $admin = new Admin('Admin', 'admin@devbank.pt', 'gpsi12');
    $admin->salvar($pdo);
    echo "[OK] Admin criado (email: admin@devbank.pt | password: gpsi12)\n";

    $hashCliente = password_hash('gpsi12', PASSWORD_DEFAULT);
    $hashPin = password_hash('1234', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Ana Silva', '254123987', 'ana.silva@escola.pt', $hashCliente, 'cliente']);
    $stmt->execute(['Rui Santos', '210987654', 'rui.santos@escola.pt', $hashCliente, 'cliente']);
    echo "[OK] Clientes criados (password: gpsi12)\n";

    $stmt = $pdo->prepare("INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo) VALUES (?, ?, ?, ?)");
    $stmt->execute([2, 'PT5000010001234567890', 'corrente', 1250.50]);
    $stmt->execute([2, 'PT5000010001234567891', 'poupanca', 5000.00]);
    $stmt->execute([3, 'PT5000020009876543210', 'corrente', 450.00]);
    echo "[OK] Contas criadas.\n";

    $stmt = $pdo->prepare("INSERT INTO cartoes (conta_id, numero_cartao, pin_encriptado, estado, validade) VALUES (?, ?, ?, 'ativo', ?)");
    $stmt->execute([1, '5044123456789012', $hashPin, '2030-12-31']);
    $stmt->execute([3, '5044987654321098', $hashPin, '2029-08-31']);
    echo "[OK] Cartões criados (PIN: 1234)\n";

    $stmt = $pdo->prepare("INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES (?, ?, ?, ?)");
    $stmt->execute([null, 1, 'deposito', 1000.00]);
    $stmt->execute([1, 3, 'transferencia', 150.00]);
    echo "[OK] Transações iniciais registadas.\n";

    echo "\n=== Configuração concluída com sucesso! ===\n";
    echo "Admin:  admin@devbank.pt / gpsi12\n";
    echo "Cartão: 5044123456789012 / PIN: 1234 (Conta: Ana Silva)\n";
    echo "Cartão: 5044987654321098 / PIN: 1234 (Conta: Rui Santos)\n\n";

} catch (PDOException $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
