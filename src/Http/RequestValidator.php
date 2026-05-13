<?php

namespace Juancarlos\Regresoacasauabc\Http;

final class RequestValidator
{
    /** Tipos de asistente permitidos en el sistema */
    private const TIPOS_ASISTENTE_VALIDOS = ['egresado', 'docente', 'administrativo'];

    /**
     * Valida que un arreglo de registro contiene todos los campos requeridos
     * (nombre, apellidos, email, campus, facultad, carrera, generacion,
     * tipo_asistente, evento_id) con valores no vacíos.
     *
     * @param array<string, mixed> $data
     */
    public static function hasRequiredRegistrationFields(array $data): bool
    {
        return self::hasRequiredFields($data, [
            'nombre',
            'apellidos',
            'email',
            'campus',
            'facultad',
            'carrera',
            'generacion',
            'tipo_asistente',
            'evento_id',
        ]);
    }

    /**
     * Valida que un arreglo de evento contiene todos los campos requeridos
     * (nombre, fecha, hora, ubicacion) con valores no vacíos.
     *
     * @param array<string, mixed> $data
     */
    public static function hasRequiredEventFields(array $data): bool
    {
        return self::hasRequiredFields($data, ['nombre', 'fecha', 'hora', 'ubicacion']);
    }

    /**
     * Verifica si un email tiene formato válido.
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida que la generación sea un año numérico entre 1960 y el año actual.
     */
    public static function isValidGeneracion(string $generacion): bool
    {
        if (!ctype_digit($generacion)) {
            return false;
        }

        $anio = (int) $generacion;
        return $anio >= 1960 && $anio <= (int) date('Y');
    }

    /**
     * Valida que el tipo de asistente sea uno de los valores permitidos.
     */
    public static function isValidTipoAsistente(string $tipo): bool
    {
        return in_array($tipo, self::TIPOS_ASISTENTE_VALIDOS, true);
    }

    /**
     * Valida que una fecha en formato YYYY-MM-DD sea hoy o en el futuro.
     */
    public static function isFutureOrTodayDate(string $fecha): bool
    {
        $parsed = date_create_from_format('Y-m-d', $fecha);

        if ($parsed === false) {
            return false;
        }

        // Verifica que el formato sea exactamente YYYY-MM-DD
        if (date_format($parsed, 'Y-m-d') !== $fecha) {
            return false;
        }

        $hoy = new \DateTime('today midnight');
        $parsed->setTime(0, 0, 0);

        return $parsed >= $hoy;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string>   $requiredFields
     */
    private static function hasRequiredFields(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return false;
            }
        }

        return true;
    }
}
