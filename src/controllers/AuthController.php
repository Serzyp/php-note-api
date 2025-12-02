<?php

namespace Controllers;

use Models\User;
use Services\TokenService;
use Services\Response;
use Helpers\Utils;

/**
 * Controlador de autenticación
 */
class AuthController
{
    private User $userModel;
    private TokenService $tokenService;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->tokenService = new TokenService();
    }
    
    /**
     * Registro de nuevo usuario
     * POST /auth/register
     */
    public function register(): void
    {
        $data = Utils::getRequestBody();
        
        // Validaciones
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (!Utils::validateLength($data['name'], 2, 100)) {
            $errors['name'] = 'El nombre debe tener entre 2 y 100 caracteres';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!Utils::isValidEmail($data['email'])) {
            $errors['email'] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors['email'] = 'El email ya está registrado';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'La contraseña es requerida';
        } elseif (!Utils::validateLength($data['password'], 6, 255)) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        // Sanitizar datos
        $name = Utils::sanitize($data['name']);
        $email = strtolower(trim($data['email']));
        $password = Utils::hashPassword($data['password']);
        
        // Crear usuario
        $userId = $this->userModel->create($name, $email, $password);
        
        if (!$userId) {
            Response::serverError('Error al crear el usuario');
        }
        
        // Generar token
        $token = $this->tokenService->generateToken($userId);
        
        // Obtener datos del usuario
        $user = $this->userModel->findById($userId);
        
        Response::success([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ],
            'token' => $token
        ], 'Usuario registrado exitosamente', 201);
    }
    
    /**
     * Login de usuario
     * POST /auth/login
     */
    public function login(): void
    {
        $data = Utils::getRequestBody();
        
        // Validaciones
        $errors = [];
        
        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!Utils::isValidEmail($data['email'])) {
            $errors['email'] = 'El email no es válido';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'La contraseña es requerida';
        }
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        // Buscar usuario
        $email = strtolower(trim($data['email']));
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            Response::error('Credenciales incorrectas', 401);
        }
        
        // Verificar contraseña
        if (!Utils::verifyPassword($data['password'], $user['password'])) {
            Response::error('Credenciales incorrectas', 401);
        }
        
        // Generar token
        $token = $this->tokenService->generateToken($user['id']);
        
        Response::success([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ],
            'token' => $token
        ], 'Login exitoso');
    }
    
    /**
     * Logout de usuario
     * POST /auth/logout
     */
    public function logout(): void
    {
        $token = Utils::getBearerToken();
        
        if (!$token) {
            Response::unauthorized('Token no proporcionado');
        }
        
        $this->tokenService->revokeToken($token);
        
        Response::success(null, 'Logout exitoso');
    }
    
    /**
     * Obtener información del usuario autenticado
     * GET /auth/me
     */
    public function me(int $userId): void
    {
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            Response::notFound('Usuario no encontrado');
        }
        
        Response::success([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ], 'Información del usuario obtenida');
    }
}
