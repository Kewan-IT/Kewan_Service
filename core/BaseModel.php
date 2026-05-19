<?php
namespace Core;

use PDO;

abstract class BaseModel {
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll(array $conditions = [], string $order = 'id DESC'): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($conditions) {
            $clauses = array_map(fn($k) => "$k = :$k", array_keys($conditions));
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
            $params = $conditions;
        }
        $sql .= " ORDER BY $order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function insert(array $data): int {
        $cols   = implode(', ', array_keys($data));
        $vals   = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $stmt   = $this->db->prepare("INSERT INTO {$this->table} ($cols) VALUES ($vals)");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data[$this->primaryKey] = $id;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET $sets WHERE {$this->primaryKey} = :{$this->primaryKey}");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function paginate(int $page = 1, int $perPage = 20, array $conditions = []): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($conditions) {
            $clauses = array_map(fn($k) => "$k = :$k", array_keys($conditions));
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
            $params = $conditions;
        }
        $countStmt = $this->db->prepare(str_replace('SELECT *', 'SELECT COUNT(*)', $sql));
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql .= " LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
