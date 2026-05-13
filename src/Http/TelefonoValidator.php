<?php

namespace Juancarlos\Regresoacasauabc\Http;

final class TelefonoValidator
{
    /**
     * El teléfono es opcional en el sistema.
     * Si se proporciona, debe tener entre 7 y 15 dígitos (sin contar espacios,
     * guiones, paréntesis ni el prefijo internacional +).
     */
    public static function isValidOrEmpty(string $telefono): bool
    {
        $telefono = trim($telefono);

        // Campo vacío → válido (es opcional)
        if ($telefono === '') {
            return true;
        }

        // Extraer solo los dígitos para validar la longitud real
        $soloDigitos = preg_replace('/[^\d]/', '', $telefono);

        if ($soloDigitos === null) {
            return false;
        }

        $longitud = strlen($soloDigitos);

        if ($longitud < 7 || $longitud > 15) {
            return false;
        }

        // Permitir: dígitos, espacios, guiones, paréntesis y + al inicio
        return preg_match('/^\+?[\d\s\-().]+$/', $telefono) === 1;
    }
}
