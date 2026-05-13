<?php

namespace Juancarlos\Regresoacasauabc\Http;

final class FaqValidator
{
    private const MAX_PREGUNTA_LENGTH  = 255;
    private const MAX_RESPUESTA_LENGTH = 1000;

    /**
     * Valida que una entrada de FAQ tenga pregunta y respuesta
     * no vacías y dentro de los límites de longitud.
     *
     * @param array<string, mixed> $data
     */
    public static function isValid(array $data): bool
    {
        $pregunta  = trim((string) ($data['pregunta']  ?? ''));
        $respuesta = trim((string) ($data['respuesta'] ?? ''));

        if ($pregunta === '' || $respuesta === '') {
            return false;
        }

        if (mb_strlen($pregunta) > self::MAX_PREGUNTA_LENGTH) {
            return false;
        }

        if (mb_strlen($respuesta) > self::MAX_RESPUESTA_LENGTH) {
            return false;
        }

        return true;
    }
}
