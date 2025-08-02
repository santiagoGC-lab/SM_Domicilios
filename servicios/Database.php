<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Método para ejecutar consultas preparadas
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta SQL: " . $e->getMessage());
            throw new Exception("Error en la consulta de base de datos");
        }
    }

    // Método para obtener una fila
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Método para obtener múltiples filas
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Método para obtener una columna
    public function fetchColumn($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    // Método para insertar
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);

        return $this->pdo->lastInsertId();
    }

    // Método para actualizar
    public function update($table, $data, $where, $whereParams = [])
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "$column = :$column";
        }
        $setClause = implode(', ', $setClause);

        $sql = "UPDATE $table SET $setClause WHERE $where";
        $params = array_merge($data, $whereParams);

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Método para eliminar
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Método para transacciones
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollback();
    }

    // Método para verificar si hay una transacción activa
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    // Método para escapar strings (aunque se recomienda usar prepared statements)
    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    // Método para obtener información de la base de datos
    public function getInfo()
    {
        return [
            'version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'connection_status' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'driver_name' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
        ];
    }
}

// Función helper para obtener la instancia de la base de datos
function getDB()
{
    return Database::getInstance();
}

// Función helper para obtener la conexión PDO
function getPDO()
{
    return Database::getInstance()->getConnection();
}
