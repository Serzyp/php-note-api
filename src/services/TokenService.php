<?php

namespace Services;

use Config\Database;
use Config\Env;
use PDO;

/**
 * Servicio para gestión de tokens de sesión
 */
class TokenService
{
    private PDO $db;
    private int $expirationTime;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->expirationTime = (int) Env::get('SESSION_EXPIRATION', 3600);
    }
    
    /**
     * Generar un nuevo token para un usuario
     */
    public function generateToken(int $userId): string
    {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->expirationTime);
        
        try {
            // Eliminar tokens antiguos del usuario
            $this->revokeUserTokens($userId);
            
            // Insertar nuevo token
            $sql = "INSERT INTO sessions (user_id, token, expires_at, created_at) 
                    VALUES (:user_id, :token, :expires_at, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
            
            return $token;
            
        } catch (\PDOException $e) {
            throw new \Exception("Error al generar token: " . $e->getMessage());
        }
    }
    
    /**
     * Validar token y obtener ID del usuario
     */
    public function validateToken(string $token): ?int
    {
        try {
            // Primero, limpiar tokens expirados
            $this->cleanExpiredTokens();
            
            $sql = "SELECT user_id FROM sessions 
                    WHERE token = :token 
                    AND expires_at > NOW() 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $session = $stmt->fetch();
            
            if ($session) {
                // Actualizar tiempo de expiración (renovar sesión)
                $this->renewToken($token);
                return (int) $session['user_id'];
            }
            
            return null;
            
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Renovar tiempo de expiración de un token
     */
    private function renewToken(string $token): bool
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->expirationTime);
        
        $sql = "UPDATE sessions SET expires_at = :expires_at WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':expires_at' => $expiresAt,
            ':token' => $token
        ]);
    }
    
    /**
     * Revocar token específico
     */
    public function revokeToken(string $token): bool
    {
        $sql = "DELETE FROM sessions WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([':token' => $token]);
    }
    
    /**
     * Revocar todos los tokens de un usuario
     */
    public function revokeUserTokens(int $userId): bool
    {
        $sql = "DELETE FROM sessions WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([':user_id' => $userId]);
    }
    
    /**
     * Limpiar tokens expirados de la base de datos
     */
    public function cleanExpiredTokens(): bool
    {
        $sql = "DELETE FROM sessions WHERE expires_at <= NOW()";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute();
    }
    
}
