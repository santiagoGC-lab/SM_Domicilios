<?php
/**
 * Clase Response - Manejo unificado de respuestas
 */

class Response {
    /**
     * Enviar respuesta JSON
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Enviar respuesta de error
     */
    public static function error($message, $statusCode = 400, $details = null) {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($details && DEVELOPMENT_MODE) {
            $response['details'] = $details;
        }
        
        self::json($response, $statusCode);
    }
    
    /**
     * Enviar respuesta de éxito
     */
    public static function success($data = null, $message = 'Operación exitosa') {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        self::json($response);
    }
    
    /**
     * Redirigir con mensaje
     */
    public static function redirect($url, $message = '', $type = 'success') {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $url .= $separator . $type . '=' . urlencode($message);
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Enviar respuesta de validación
     */
    public static function validationError($errors) {
        self::error('Errores de validación', 422, $errors);
    }
    
    /**
     * Enviar respuesta de autenticación requerida
     */
    public static function unauthorized($message = 'Autenticación requerida') {
        self::error($message, 401);
    }
    
    /**
     * Enviar respuesta de acceso denegado
     */
    public static function forbidden($message = 'Acceso denegado') {
        self::error($message, 403);
    }
    
    /**
     * Enviar respuesta de recurso no encontrado
     */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }
    
    /**
     * Enviar respuesta de error interno del servidor
     */
    public static function serverError($message = 'Error interno del servidor') {
        self::error($message, 500);
    }
    
    /**
     * Enviar respuesta de conflicto
     */
    public static function conflict($message = 'Conflicto con el estado actual del recurso') {
        self::error($message, 409);
    }
    
    /**
     * Enviar respuesta de demasiadas solicitudes
     */
    public static function tooManyRequests($message = 'Demasiadas solicitudes') {
        self::error($message, 429);
    }
    
    /**
     * Enviar respuesta con paginación
     */
    public static function paginated($data, $page, $totalPages, $totalItems, $perPage) {
        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'per_page' => $perPage,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
        
        self::json($response);
    }
}
?> 