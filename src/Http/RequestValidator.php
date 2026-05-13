<?php

namespace Juancarlos\Regresoacasauabc\Http;

final class RequestValidator
{
    /**
     * @param array<string, mixed> $data
     */
    public static function hasRequiredRegistrationFields(array $data): bool
    {
        return self::hasRequiredFields($data, ['nombre', 'email', 'campus', 'evento_id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function hasRequiredEventFields(array $data): bool
    {
        return self::hasRequiredFields($data, ['nombre', 'fecha', 'hora', 'ubicacion']);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $requiredFields
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
