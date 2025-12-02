<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Modelo User - Gestión de usuarios
 */
class User
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create(string $name, string $email, string $password): ?int
    {
        try {
            $sql = "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())";
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $password
            ]);
            
            return (int) $this->db->lastInsertId();
            
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password, created_at FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Buscar usuario por ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Verificar si el email ya existe
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Actualizar información del usuario
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
}
