<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Clase de Conexión a Base de Datos
 * ============================================================================
 * Archivo: src/Core/Database.php
 * Descripción: Wrapper PDO Singleton con métodos CRUD y transacciones
 * ============================================================================
 */

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use Exception;

class Database
{
    private ?PDO $pdo = null;
    private static ?Database $instance = null;

    /**
     * Constructor privado para Singleton.
     * Carga la configuración directamente desde variables de entorno.
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Obtener la instancia única de la base de datos
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión PDO
     */
    private function connect(): void
    {
        try {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');
            $db   = env('DB_DATABASE', 'shalom_dental');
            $user = env('DB_USERNAME', 'root');
            $pass = env('DB_PASSWORD', '');
            $charset = env('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $options);

        } catch (PDOException $e) {
            // En producción, no mostrar la contraseña en el error
            throw new Exception('Error de conexión a Base de Datos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el objeto PDO nativo (útil para transacciones manuales)
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    // =========================================================================
    // MÉTODOS DE CONSULTA BÁSICOS
    // =========================================================================

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error en Query SQL: " . $e->getMessage() . " [SQL: $sql]");
        }
    }

    public function select(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function selectOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    // =========================================================================
    // MÉTODOS CRUD (Create, Read, Update, Delete)
    // =========================================================================

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        // Crear placeholders (?, ?, ?)
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));

        return (int) $this->getConnection()->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        // Generar "columna = ?"
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        // Unir valores de actualización con valores del WHERE
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    // =========================================================================
    // TRANSACCIONES
    // =========================================================================

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Ejecutar un callback dentro de una transacción
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}