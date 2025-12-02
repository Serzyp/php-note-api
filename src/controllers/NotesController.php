<?php

namespace Controllers;

use Models\Note;
use Services\Response;
use Helpers\Utils;

/**
 * Controlador de notas
 */
class NotesController
{
    private Note $noteModel;
    
    public function __construct()
    {
        $this->noteModel = new Note();
    }
    
    /**
     * Crear nueva nota
     * POST /notes
     */
    public function create(int $userId): void
    {
        $data = Utils::getRequestBody();
        
        // Validaciones
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'El título es requerido';
        } elseif (!Utils::validateLength($data['title'], 1, 255)) {
            $errors['title'] = 'El título debe tener entre 1 y 255 caracteres';
        }
        
        if (empty($data['content'])) {
            $errors['content'] = 'El contenido es requerido';
        }
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        // Sanitizar datos
        $title = Utils::sanitize($data['title']);
        $content = Utils::sanitize($data['content']);
        
        // Crear nota
        $noteId = $this->noteModel->create($userId, $title, $content);
        
        if (!$noteId) {
            Response::serverError('Error al crear la nota');
        }
        
        // Obtener la nota creada
        $note = $this->noteModel->getById($noteId);
        
        Response::success([
            'id' => $note['id'],
            'title' => $note['title'],
            'content' => $note['content'],
            'created_at' => $note['created_at'],
            'updated_at' => $note['updated_at']
        ], 'Nota creada exitosamente', 201);
    }
    
    /**
     * Listar todas las notas del usuario
     * GET /notes
     */
    public function getAll(int $userId): void
    {
        $notes = $this->noteModel->getAllByUser($userId);
        
        // Formatear respuesta
        $formattedNotes = array_map(function($note) {
            return [
                'id' => $note['id'],
                'title' => $note['title'],
                'content' => $note['content'],
                'created_at' => $note['created_at'],
                'updated_at' => $note['updated_at']
            ];
        }, $notes);
        
        Response::success([
            'notes' => $formattedNotes,
            'total' => count($formattedNotes)
        ], 'Notas obtenidas exitosamente');
    }
    
    /**
     * Obtener una nota específica
     * GET /notes/{id}
     */
    public function getOne(int $userId, int $noteId): void
    {
        $note = $this->noteModel->getById($noteId);
        
        if (!$note) {
            Response::notFound('Nota no encontrada');
        }
        
        // Verificar que la nota pertenece al usuario
        if ($note['user_id'] != $userId) {
            Response::forbidden('No tienes permiso para ver esta nota');
        }
        
        Response::success([
            'id' => $note['id'],
            'title' => $note['title'],
            'content' => $note['content'],
            'created_at' => $note['created_at'],
            'updated_at' => $note['updated_at']
        ], 'Nota obtenida exitosamente');
    }
    
    /**
     * Actualizar una nota
     * PUT /notes/{id}
     */
    public function update(int $userId, int $noteId): void
    {
        // Verificar que la nota existe y pertenece al usuario
        $note = $this->noteModel->getById($noteId);
        
        if (!$note) {
            Response::notFound('Nota no encontrada');
        }
        
        if ($note['user_id'] != $userId) {
            Response::forbidden('No tienes permiso para editar esta nota');
        }
        
        $data = Utils::getRequestBody();
        
        // Validaciones
        $errors = [];
        $updateData = [];
        
        if (isset($data['title'])) {
            if (empty($data['title'])) {
                $errors['title'] = 'El título no puede estar vacío';
            } elseif (!Utils::validateLength($data['title'], 1, 255)) {
                $errors['title'] = 'El título debe tener entre 1 y 255 caracteres';
            } else {
                $updateData['title'] = Utils::sanitize($data['title']);
            }
        }
        
        if (isset($data['content'])) {
            if (empty($data['content'])) {
                $errors['content'] = 'El contenido no puede estar vacío';
            } else {
                $updateData['content'] = Utils::sanitize($data['content']);
            }
        }
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        if (empty($updateData)) {
            Response::error('No hay datos para actualizar', 400);
        }
        
        // Actualizar nota
        $success = $this->noteModel->update($noteId, $userId, $updateData);
        
        if (!$success) {
            Response::serverError('Error al actualizar la nota');
        }
        
        // Obtener nota actualizada
        $updatedNote = $this->noteModel->getById($noteId);
        
        Response::success([
            'id' => $updatedNote['id'],
            'title' => $updatedNote['title'],
            'content' => $updatedNote['content'],
            'created_at' => $updatedNote['created_at'],
            'updated_at' => $updatedNote['updated_at']
        ], 'Nota actualizada exitosamente');
    }
    
    /**
     * Eliminar una nota
     * DELETE /notes/{id}
     */
    public function delete(int $userId, int $noteId): void
    {
        // Verificar que la nota existe y pertenece al usuario
        $note = $this->noteModel->getById($noteId);
        
        if (!$note) {
            Response::notFound('Nota no encontrada');
        }
        
        if ($note['user_id'] != $userId) {
            Response::forbidden('No tienes permiso para eliminar esta nota');
        }
        
        // Eliminar nota
        $success = $this->noteModel->delete($noteId, $userId);
        
        if (!$success) {
            Response::serverError('Error al eliminar la nota');
        }
        
        Response::success(null, 'Nota eliminada exitosamente');
    }
}
