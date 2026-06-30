<?php
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::conectar();

    echo "=== A corrigir hashes da base de dados ===\n\n";

    $hashAdmin = password_hash('admin', PASSWORD_DEFAULT);
    $hashCliente = password_hash('gpsi12', PASSWORD_DEFAULT);
    $hashPin = password_hash('1234', PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE utilizadores SET palavra_passe = :hash WHERE tipo_utilizador = 'admin'");
    $stmt->bindParam(':hash', $hashAdmin);
    $stmt->execute();
    echo "[OK] Hash do Admin atualizado (password: admin)\n";

    $stmt = $db->prepare("UPDATE utilizadores SET palavra_passe = :hash WHERE tipo_utilizador = 'cliente'");
    $stmt->bindParam(':hash', $hashCliente);
    $stmt->execute();
    echo "[OK] Hash dos Clientes atualizado (password: gpsi12)\n";

    $stmt = $db->prepare("UPDATE cartoes SET pin_encriptado = :hash WHERE estado = 'ativo'");
    $stmt->bindParam(':hash', $hashPin);
    $stmt->execute();
    echo "[OK] PIN dos Cartões atualizado (PIN: 1234)\n";

    echo "\n=== Hashes corrigidos com sucesso! ===\n";
    echo "Admin:  admin@admin / admin\n";
    echo "Cartões: PIN 1234\n\n";

} catch (PDOException $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
