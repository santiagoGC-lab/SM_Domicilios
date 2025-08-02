<?php
class Response
{
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    public static function error($message, $statusCode = 400, $details = null)
    {
        $response = [
            'success' => false,
            'error' => $message
        ];

        if ($details && DEVELOPMENT_MODE) {
            $response['details'] = $details;
        }

        self::json($response, $statusCode);
    }

    public static function success($data = null, $message = 'Operación exitosa')
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response);
    }

    public static function redirect($url, $message = '', $type = 'success')
    {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $url .= $separator . $type . '=' . urlencode($message);
        header('Location: ' . $url);
        exit;
    }
    public static function validationError($errors)
    {
        self::error('Errores de validación', 422, $errors);
    }

    public static function unauthorized($message = 'Autenticación requerida')
    {
        self::error($message, 401);
    }

    public static function forbidden($message = 'Acceso denegado')
    {
        self::error($message, 403);
    }


    public static function notFound($message = 'Recurso no encontrado')
    {
        self::error($message, 404);
    }

    public static function serverError($message = 'Error interno del servidor')
    {
        self::error($message, 500);
    }

    public static function conflict($message = 'Conflicto con el estado actual del recurso')
    {
        self::error($message, 409);
    }

    public static function tooManyRequests($message = 'Demasiadas solicitudes')
    {
        self::error($message, 429);
    }

    public static function paginated($data, $page, $totalPages, $totalItems, $perPage)
    {
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
