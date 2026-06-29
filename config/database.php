<?php
require_once __DIR__ . '/env.php';

class Database {
    private static $instancia = null;
    private $conexao;

    private function __construct() {
        try {
            $this->conexao = new PDO(
                "pgsql:host=" . DB_HOST . ";port=5432;dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $e) {
            echo '<h3>Erro de conexão à base de dados</h3>';
            echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Host:</strong> ' . DB_HOST . '</p>';
            echo '<p><strong>Base de dados:</strong> ' . DB_NAME . '</p>';
            echo '<p><strong>Utilizador:</strong> ' . DB_USER . '</p>';
            exit;
        }
    }

    public static function getConexao(): PDO {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia->conexao;
    }
}
