<?php
/**
 * Script de setup do DevBank
 * Uso: php setup.php
 *
 * Cria as tabelas e dados de teste na base configurada no .env
 */

require_once __DIR__ . '/config/database.php';

echo "=== DevBank Setup ===\n\n";

$driver = getenv('DB_DRIVER') ?: 'pgsql';

if ($driver === 'sqlite') {
    $schemaFile = __DIR__ . '/sql/schema.sqlite.sql';
} else {
    $schemaFile = __DIR__ . '/sql/schema.sql';
}

if (!file_exists($schemaFile)) {
    die("Erro: Ficheiro schema não encontrado: {$schemaFile}\n");
}

try {
    $db = Database::getConnection();

    $sql = file_get_contents($schemaFile);

    // Remove comentários de uma linha
    $sql = preg_replace('/^--.*$/m', '', $sql);

    // Remove linhas vazias no início/fim de cada statement
    $sql = preg_replace('/;\s*\n\s*/', ";\n", $sql);

    // Divide por ponto e vírgula (ignorando vazios)
    $statements = array_filter(
        array_map('trim', explode(";\n", $sql)),
        fn($s) => !empty($s)
    );

    foreach ($statements as $stmt) {
        $db->exec($stmt);
    }

    // Inserir dados de teste com hashes reais
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $clientePass = password_hash('cliente123', PASSWORD_DEFAULT);
    $pinHash = password_hash('1234', PASSWORD_DEFAULT);

    $db->exec("DELETE FROM transacoes");
    $db->exec("DELETE FROM cartoes");
    $db->exec("DELETE FROM contas");
    $db->exec("DELETE FROM utilizadores");

    // Admin
    $stmt = $db->prepare("INSERT INTO utilizadores (nome, nif, email, palavra_passe, tipo_utilizador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', null, 'admin@devbank.pt', $adminPass, 'admin']);

    // Clientes
    $stmt->execute(['Ana Silva', '254123987', 'ana.silva@escola.pt', $clientePass, 'cliente']);
    $stmt->execute(['Rui Santos', '210987654', 'rui.santos@escola.pt', $clientePass, 'cliente']);

    // Contas
    $stmt = $db->prepare("INSERT INTO contas (utilizador_id, numero_conta, tipo_conta, saldo) VALUES (?, ?, ?, ?)");
    $stmt->execute([2, 'PT5000010001234567890', 'corrente', 1250.50]);
    $stmt->execute([2, 'PT5000010001234567891', 'poupanca', 5000.00]);
    $stmt->execute([3, 'PT5000020009876543210', 'corrente', 450.00]);

    // Cartões
    $stmt = $db->prepare("INSERT INTO cartoes (conta_id, numero_cartao, pin_encriptado, estado, validade) VALUES (?, ?, ?, 'ativo', ?)");
    $stmt->execute([1, '5044123456789012', $pinHash, '2030-12-31']);
    $stmt->execute([3, '5044987654321098', $pinHash, '2029-08-31']);

    // Transações
    $stmt = $db->prepare("INSERT INTO transacoes (conta_origem_id, conta_destino_id, tipo_transacao, valor) VALUES (?, ?, ?, ?)");
    $stmt->execute([null, 1, 'deposito', 1000.00]);
    $stmt->execute([1, 3, 'transferencia', 150.00]);

    echo "Setup concluído com sucesso!\n";
    echo "Driver: " . strtoupper($driver) . "\n";
    echo "Admin:  admin@devbank.pt / admin123\n";
    echo "Cliente: ana.silva@escola.pt / cliente123\n";
    echo "Cliente: rui.santos@escola.pt / cliente123\n";
    echo "Cartão 1 (Ana - Corrente): 5044123456789012 / PIN 1234\n";
    echo "Cartão 2 (Rui - Corrente):  5044987654321098 / PIN 1234\n";

} catch (\Exception $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
