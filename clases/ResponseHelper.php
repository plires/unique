<?php

/**
 * Helper para estandarizar respuestas JSON
 */

class ResponseHelper
{

  /**
   * Respuesta de éxito
   */
  public static function success($data = null, $message = 'Operación exitosa', $httpCode = 200)
  {
    http_response_code($httpCode);

    $response = [
      'success' => true,
      'message' => $message,
    ];

    if ($data !== null) {
      $response['data'] = $data;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit;
  }

  /**
   * Respuesta de error
   */
  public static function error($message = 'Error en la operación', $errors = null, $httpCode = 400)
  {
    http_response_code($httpCode);

    $response = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = is_array($errors) ? $errors : [$errors];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
  }

  /**
   * Respuesta para validaciones fallidas
   */
  public static function validationError($errors, $message = 'Errores de validación')
  {
    self::error($message, $errors, 422);
  }

  /**
   * Respuesta para recursos no encontrados
   */
  public static function notFound($message = 'Recurso no encontrado')
  {
    self::error($message, null, 404);
  }

  /**
   * Respuesta para errores de servidor
   */
  public static function serverError($message = 'Error interno del servidor')
  {
    self::error($message, null, 500);
  }

  /**
   * Respuesta para acceso no autorizado
   */
  public static function unauthorized($message = 'Acceso no autorizado')
  {
    self::error($message, null, 401);
  }

  /**
   * Respuesta para operaciones prohibidas
   */
  public static function forbidden($message = 'Operación prohibida')
  {
    self::error($message, null, 403);
  }

  /**
   * Respuesta simple para compatibilidad con código existente
   */
  public static function json($data)
  {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit;
  }

  /**
   * Respuesta booleana simple (para compatibilidad)
   */
  public static function boolean($result)
  {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
  }
}
