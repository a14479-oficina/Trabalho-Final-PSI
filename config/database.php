<?php
class Database {
    private static $instancia = null;
    private $conexao;

    private function __construct() {
        try {
            $this->conexao = new PDO(
"mysql:host=sql309.infinityfree.com;port=3306;dbname=if0_42295620_devbank;charset=utf8mb4",
            "if0_42295620",
            "9jfEAEMpuMefDb",
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $e) {
            die("Erro de conexão à base de dados: " . $e->getMessage());
        }
    }

    public static function getConexao(): PDO {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia->conexao;
    }
}
