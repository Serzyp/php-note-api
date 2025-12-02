<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Modelo Note - Gestión de notas
 */
class Note
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Crear nueva nota
     */
    public function create(int $userId, string $title, string $content): ?int
    {
        try {
            $sql = "INSERT INTO notes (user_id, title, content, created_at, updated_at) 
                    VALUES (:user_id, :title, :content, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':user_id' => $userId,
                ':title' => $title,
                ':content' => $content
            ]);
            
            return (int) $this->db->lastInsertId();
            
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Obtener todas las notas de un usuario
     */
    public function getAllByUser(int $userId): array
    {
        $sql = "SELECT id, title, content, created_at, updated_at 
                FROM notes 
                WHERE user_id = :user_id 
                ORDER BY updated_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener una nota específica
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT id, user_id, title, content, created_at, updated_at 
                FROM notes 
                WHERE id = :id 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $note = $stmt->fetch();
        return $note ?: null;
    }
    
    /**
     * Actualizar una nota
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $fields = [];
        $params = [
            ':id' => $id,
            ':user_id' => $userId
        ];
        
        if (isset($data['title'])) {
            $fields[] = "title = :title";
            $params[':title'] = $data['title'];
        }
        
        if (isset($data['content'])) {
            $fields[] = "content = :content";
            $params[':content'] = $data['content'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        
        $sql = "UPDATE notes SET " . implode(', ', $fields) . " 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Eliminar una nota
     */
    public function delete(int $id, int $userId): bool
    {
        $sql = "DELETE FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }
    
    /**
     * Verificar si una nota pertenece a un usuario
     */
    public function belongsToUser(int $noteId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $noteId,
            ':user_id' => $userId
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
